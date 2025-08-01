<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Branch;
use App\Models\Deduction;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->can('Manage Employee')) {
            if (Auth::user()->type == 'super admin') {
                $employees = Employee::with(['branch', 'department', 'designation', 'user', 'company'])->get();
            } else {
                $employees = Employee::where('created_by', Auth::user()->creatorId())->with(['branch', 'department', 'designation', 'user'])->get();
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Successfully retrieved data',
                'data'      => $employees
            ]);
        } else {
            return response()->json(['message' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Employee')) {
            $rules = [
                'name' => 'required|max:150',
                'dob' => 'before:' . date('Y-m-d'),
                'gender' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'email' => 'required|unique:users|email|max:100',
                'password' => 'required',
                'branch_id' => 'required',
                'department_id' => 'required',
                'designation_id' => 'required',
                'document.*' => 'nullable',
                'family_name' => 'string',
                'family_address' => 'string',
                'family_phone' => 'string',
            ];

            // $rules['biometric_emp_id'] = [
            //     'required',
            //     Rule::unique('employees')->where(function ($query) {
            //         return $query->where('created_by', Auth::user()->creatorId());
            //     })
            // ];

            $validator = Validator::make(
                $request->all(),
                $rules
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            try {
                DB::beginTransaction();
                $objUser        = User::find(Auth::user()->creatorId());
                // $total_employee = $objUser->countEmployees();
                // $plan           = Plan::find($objUser->plan);
                $date = date("Y-m-d H:i:s");
                // $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', \Auth::user()->creatorId())->first();

                // new company default language
                // if ($default_language == null) {
                //     $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
                // }

                $name_parts = explode(' ', $request['name'], 2); // Batasi menjadi 2 bagian
                $first_name = $name_parts[0];
                $last_name = isset($name_parts[1]) ? $name_parts[1] : ''; // Jika tidak ada last name, set empty string
                $user = User::create(
                    [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $request['email'],
                        'password' => Hash::make($request['password']),
                        'type' => 'employee',
                        // 'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'avatar' => '',
                        'lang' => 'en',
                        'created_by' => Auth::user()->type == 'super admin' ? $request['created_by'] :  Auth::user()->creatorId(),
                        'email_verified_at' => $date,
                    ]
                );
                $user->save();
                $user->assignRole('employee');

                $documents = $this->handleDocumentUploads($request);
                $createdBy = Auth::user()->type == 'super admin' ? $request['created_by'] :  Auth::user()->creatorId();
                $employee = Employee::create(
                    [
                        'user_id' => $user->id,
                        'name' => $request['name'],
                        'dob' => $request['dob'],
                        'gender' => $request['gender'],
                        'phone' => $request['phone'],
                        'address' => $request['address'],
                        'email' => $request['email'],
                        'password' => Hash::make($request['password']),
                        'employee_id' => $this->employeeNumber($createdBy),
                        // 'biometric_emp_id' => !empty($request['biometric_emp_id']) ? $request['biometric_emp_id'] : '',
                        'branch_id' => $request['branch_id'],
                        'department_id' => $request['department_id'],
                        'designation_id' => $request['designation_id'],
                        'company_doj' => $request['company_doj'],
                        'documents' => !empty($documents) ? $documents : null,
                        'account_holder_name' => $request['account_holder_name'] ?? null,
                        'account_number' => $request['account_number'] ?? null,
                        'bank_name' => $request['bank_name'] ?? null,
                        'bank_identifier_code' => $request['bank_identifier_code'] ?? null,
                        'branch_location' => $request['branch_location'] ?? null,
                        'tax_payer_id' => $request['tax_payer_id'] ?? null,
                        'family_name' => $request['family_name'] ?? null,
                        'family_phone' => $request['family_phone'] ?? null,
                        'family_address' => $request['family_address'] ?? null,
                        'created_by' => $createdBy
                    ]
                );



                // $setings = \App\Models\Utility::settings();
                // if ($setings['new_employee'] == 1) {
                //     $department = Department::find($request['department_id']);
                //     $branch = Branch::find($request['branch_id']);
                //     $designation = Designation::find($request['designation_id']);
                //     $uArr = [
                //         'employee_email' => $user->email,
                //         'employee_password' => $request->password,
                //         'employee_name' => $request['name'],
                //         'employee_branch' => !empty($branch->name) ? $branch->name : '',
                //         'employee_department' => !empty($department->name) ? $department->name : '',
                //         'employee_designation' => !empty($designation->name) ? $designation->name : '',
                //     ];
                //     $resp = \App\Models\Utility::sendEmailTemplate('new_employee', [$user->id => $user->email], $uArr);

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => __('Employee successfully created.'),
                ], 201);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('Show Employee')) {
            try {
                $empId = $id;
                // $empId        = \Illuminate\Support\Facades\Crypt::decrypt($id);
            } catch (\RuntimeException $e) {
                return response()->json([
                    'status'    => false,
                    'message' => __('Employee not avaliable.')
                ], 404);
            }
            // $documents    = Document::where('created_by', \Auth::user()->creatorId())->get();
            $branches     = Branch::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments  = Department::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee = Employee::where('id', '=', $empId)
                // ->orWhere('user_id', '=', $empId)
                ->where('created_by', Auth::user()->creatorId())->first();
            // $employee     = Employee::find($empId);
            $employeeId  = Auth::user()->employeeIdFormat($employee->employee_id);
            // $empId        = Crypt::decrypt($id);

            //     $employee     = Employee::find($empId);
            // $branch= Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            // return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));

            return response()->json([
                'status'    => true,
                'message'   => 'Successfully retrieved data',
                'data'      => [
                    'employee'  => $employee,
                    'employeeId'  => $employeeId,
                    'branches'  => $branches,
                    'departments'  => $departments,
                    'designations'  => $designations,
                ]
            ]);
        } else {
            return response()->json(['message' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit Employee')) {

            $employee = Employee::findOrFail($id);

            $rules = [
                'name' => 'required',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required',
                'address' => 'required',
            ];

            // if ($request->has('biometric_emp_id') && $employee->biometric_emp_id != $request->biometric_emp_id) {
            //     $rules['biometric_emp_id'] = [
            //         'required',
            //         Rule::unique('employees')->where(function ($query) {
            //             return $query->where('created_by', Auth::user()->creatorId());
            //         })
            //     ];
            // }

            $validator = Validator::make(
                $request->all(),
                $rules
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            $documents = $this->handleDocumentUploads($request);
            $employee = Employee::findOrFail($id);
            $input    = $request->all();
            $input['documents'] = !empty($documents) ? $documents : $request->documents;
            $employee->fill($input)->save();
            if ($request->salary) {
                return response()->json([
                    'message'   => 'Employee successfully updated.'
                ], 200);
            }

            if (Auth::user()->type != 'employee') {
                // return redirect()->route('employee.index')->with('success', 'Employee successfully updated.');

                return response()->json([
                    'status'    => true,
                    'message'   => __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '')
                ], 200);
            } else {
                return response()->json([
                    'status'    => true,
                    'message'   => __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '')
                ], 200);
            }
        } else {
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }

    public function updateEmployeeSalary(Request $request, $id)
    {
        if (Auth::user()->can('Edit Employee')) {

            $employee = Employee::findOrFail($id);

            $input    = $request->all();
            $employee->fill($input)->save();

            return response()->json([
                'status'    => true,
                'message'   => __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '')
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }



    public function destroy($id)
    {
        if (Auth::user()->can('Delete Employee')) {
            $employee      = Employee::findOrFail($id);
            $user          = User::where('id', '=', $employee->user_id)->first();
            // $emp_documents = EmployeeDocument::where('employee_id', $employee->employee_id)->get();
            // $ContractEmployee = Contract::where('employee_name', '=', $employee->user_id)->get();
            // $payslips = PaySlip::where('employee_id', $id)->get();
            $employee->delete();
            $user->delete();

            // foreach ($ContractEmployee as $contractdelete) {
            //     $contractdelete->delete();
            // }

            // foreach ($payslips as $payslip) {
            //     $payslip->delete();
            // }

            // $dir = storage_path('uploads/document/');
            // foreach ($emp_documents as $emp_document) {

            //     $emp_document->delete();
            //     // \File::delete(storage_path('uploads/document/' . $emp_document->document_value));
            //     if (!empty($emp_document->document_value)) {

            //         $file_path = 'uploads/document/' . $emp_document->document_value;
            //         $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

            //         // unlink($dir . $emp_document->document_value);
            //     }
            // }

            return response()->json([
                'status'    => true,
                'message'   => __('Employee successfully deleted.')
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }

    function employeeNumber($creatorId = null)
    {
        if ($creatorId == null) $creatorId =  Auth::user()->creatorId();
        $latest = Employee::where('created_by', '=', $creatorId)->latest('id')->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }

    private function handleDocumentUploads(Request $request)
    {
        $documents = [];
        $baseUrl = url('/');

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $key => $file) {
                if ($file && $file->isValid()) {
                    $fileName = $key . '_' . $this->employeeNumber() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/employee_documents/'), $fileName);
                    // Store as key-value pair in array
                    $documents[$key] = $baseUrl . '/uploads/employee_documents/' . $fileName;
                }
            }
        }

        // Return documents array directly instead of imploded string
        return $documents;
    }
}
