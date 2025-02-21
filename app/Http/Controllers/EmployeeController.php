<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
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
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', Auth::user()->id)->get();
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
                // dd(Carbon::parse($request->dob), $request->all());
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

                // if ($request->hasFile('document')) {
                //     foreach ($request->document as $key => $document) {

                //         $image_size = $request->file('document')[$key]->getSize();
                //         $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

                //         if ($result == 1) {
                //             $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                //             $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                //             $extension       = $request->file('document')[$key]->getClientOriginalExtension();
                //             $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                //             $dir             = 'uploads/document/';

                //             $image_path      = $dir . $fileNameToStore;

                //             $path = \App\Models\Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);

                //             if ($path['flag'] == 1) {
                //                 $url = $path['url'];
                //             } else {
                //                 return redirect()->back()->with('error', __($path['msg']));
                //             }
                //         }
                //     }
                // }

                // if ($total_employee < $plan->max_employees || $plan->max_employees == -1) {

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
                        'created_by' => Auth::user()->creatorId(),
                        'email_verified_at' => $date,
                    ]
                );
                $user->save();
                $user->assignRole('employee');

                // } else {
                //     return redirect()->back()->with('error', __('Your employee limit is over, Please upgrade plan.'));
                // }


                // if (!empty($request->document) && !is_null($request->document)) {
                //     $document_implode = implode(',', array_keys($request->document));
                // } else {
                //     $document_implode = null;
                // }


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
                        'employee_id' => $this->employeeNumber(),
                        // 'biometric_emp_id' => !empty($request['biometric_emp_id']) ? $request['biometric_emp_id'] : '',
                        'branch_id' => $request['branch_id'],
                        'department_id' => $request['department_id'],
                        'designation_id' => $request['designation_id'],
                        'company_doj' => $request['company_doj'],
                        // 'documents' => $document_implode,
                        'account_holder_name' => $request['account_holder_name'] ?? null,
                        'account_number' => $request['account_number'] ?? null,
                        'bank_name' => $request['bank_name'] ?? null,
                        'bank_identifier_code' => $request['bank_identifier_code'] ?? null,
                        'branch_location' => $request['branch_location'] ?? null,
                        'tax_payer_id' => $request['tax_payer_id'] ?? null,
                        'created_by' => Auth::user()->creatorId(),
                    ]
                );

                // if ($request->hasFile('document')) {
                //     foreach ($request->document as $key => $document) {

                //         $image_size = $request->file('document')[$key]->getSize();
                //         $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

                //         if ($result == 1) {
                //             $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                //             $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                //             $extension       = $request->file('document')[$key]->getClientOriginalExtension();
                //             $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                //             $dir             = 'uploads/document/';

                //             $image_path      = $dir . $fileNameToStore;

                //             $path = \App\Models\Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);

                //             if ($path['flag'] == 1) {
                //                 $url = $path['url'];
                //             } else {
                //                 return redirect()->back()->with('error', __($path['msg']));
                //             }
                //             $employee_document = EmployeeDocument::create(
                //                 [
                //                     'employee_id' => $employee['employee_id'],
                //                     'document_id' => $key,
                //                     'document_value' => $path['url'],
                //                     'created_by' => \Auth::user()->creatorId(),
                //                 ]
                //             );
                //             $employee_document->save();
                //         }
                //     }
                // }
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
            $employee = Employee::where('id', '=', $empId)->orWhere('user_id', '=', $empId)->where('created_by', Auth::user()->creatorId())->first();
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


            // if ($request->document) {

            //     foreach ($request->document as $key => $document) {
            //         $employee_document = EmployeeDocument::where('employee_id', $employee->employee_id)->where('document_id', $key)->first();
            //         if (!empty($document)) {

            //             //storage limit
            //             $dir = 'uploads/document/';
            //             if (!empty($employee_document)) {
            //                 $file_path = $dir . $employee_document->document_value;
            //             }
            //             $image_size = $request->file('document')[$key]->getSize();
            //             $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

            //             if ($result == 1) {
            //                 if (!empty($$file_path)) {
            //                     Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            //                 }

            //                 $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
            //                 $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //                 $extension       = $request->file('document')[$key]->getClientOriginalExtension();
            //                 $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            //                 $dir             = 'uploads/document/';

            //                 $image_path      = $dir . $fileNameToStore;

            //                 $path = \App\Models\Utility::upload_coustom_file($request, 'document', $fileNameToStore, $dir, $key, []);
            //                 if (!empty($employee_document)) {
            //                     if ($employee_document->document_value) {
            //                         \File::delete(storage_path('uploads/document/' . $employee_document->document_value));
            //                     }
            //                     $employee_document->document_value = $fileNameToStore;
            //                     $employee_document->save();
            //                 } else {
            //                     $employee_document                 = new EmployeeDocument();
            //                     $employee_document->employee_id    = $employee->employee_id;
            //                     $employee_document->document_id    = $key;
            //                     $employee_document->document_value = $fileNameToStore;
            //                     $employee_document->save();
            //                 }

            //                 if ($path['flag'] == 1) {
            //                     $url = $path['url'];
            //                 } else {
            //                     return redirect()->back()->with('error', __($path['msg']));
            //                 }
            //             }
            //         }
            //     }
            // }

            // if (!empty($request->document) && !is_null($request->document)) {
            //     $document_implode = implode(',', array_keys($request->document));
            // } else {
            //     $document_implode = null;
            // }

            $employee = Employee::findOrFail($id);
            $input    = $request->all();
            // $input['documents'] = $document_implode;
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
                // return redirect()->route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))->with('success', __('Employee successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));


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
}
