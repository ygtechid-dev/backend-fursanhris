<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage User')) {
            $user = Auth::user();
            if (Auth::user()->type == 'super admin') {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();
                // $CountUser = User::where('created_by')->get();
            } else {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'employee')->get();
            }

            return response()->json([
                'message'   => 'Successfully retrieved data',
                'data'      => $users
            ]);
        } else {
            return response()->json(
                [
                    'status'    => false,
                    'message' => __('Permission denied.')
                ],
                403
            );
        }
    }

    public function getCompanies()
    {
        if (Auth::user()->can('Manage User')) {
            $user = Auth::user();
            $companies = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();

            return response()->json([
                'message'   => 'Successfully retrieved data',
                'data'      => $companies
            ]);
        } else {
            return response()->json(
                [
                    'status'    => false,
                    'message' => __('Permission denied.')
                ],
                403
            );
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create User')) {
            // $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', \Auth::user()->creatorId())->first();

            // new company default language
            // if ($default_language == null) {
            //     $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            // }

            $validator        = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|unique:users',
                    // 'password' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'    => false,
                    'message'   => $messages->first()
                ], 400);
            }

            if (!empty($request->password_switch) && $request->password_switch == 'on') {
                $validator = Validator::make(
                    $request->all(),
                    ['password' => 'required|min:6']
                );

                if ($validator->fails()) {
                    // return redirect()->back()->with('error', $validator->errors()->first());
                    return response()->json([
                        'status'    => false,
                        'message'   => $validator->errors()->first()
                    ], 400);
                }
            }

            // do {
            //     $code = rand(100000, 999999);
            // } while (User::where('referral_code', $code)->exists());

            if (Auth::user()->type == 'super admin') {
                $date = date("Y-m-d H:i:s");
                $userpassword = $request->input('password');
                $user = User::create(
                    [
                        'first_name' => $request['first_name'],
                        'last_name' => $request['last_name'],
                        'email' => $request['email'],
                        // 'is_login_enable' => !empty($request->password_switch) && $request->password_switch == 'on' ? 1 : 0,
                        'is_login_enable' => 1,
                        'password' => !empty($userpassword) ? Hash::make($userpassword) : null,
                        'type' => 'company',
                        'avatar' => '',
                        'company_id' => Utility::generateCompanyId(),
                        // 'plan' => $plan = Plan::where('price', '<=', 0)->first()->id,
                        // 'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'lang' => 'en',
                        // 'referral_code' => $code,
                        'created_by' => Auth::user()->id,
                        'email_verified_at' => $date,
                    ]
                );

                $user->assignRole('Company');
                // $user->userDefaultData();
                // $user->userDefaultDataRegister($user->id);
                // GenerateOfferLetter::defaultOfferLetterRegister($user->id);
                // ExperienceCertificate::defaultExpCertificatRegister($user->id);
                // JoiningLetter::defaultJoiningLetterRegister($user->id);
                // NOC::defaultNocCertificateRegister($user->id);
                // Utility::jobStage($user->id);
                $role_r = Role::findById(2);

                //create company default roles
                Utility::MakeRole($user->id);
            } else {
                $objUser    = Auth::user()->creatorId();
                $objUser = User::find($objUser);
                // $total_user = $objUser->countUsers();
                // $plan       = Plan::find($objUser->plan);
                $userpassword = $request->input('password');

                // if ($total_user < $plan->max_users || $plan->max_users == -1) {

                $role_r = Role::findById($request->type, 'web');
                $date = date("Y-m-d H:i:s");
                $user   = User::create(
                    [
                        'first_name' => $request['first_name'],
                        'last_name' => $request['last_name'],
                        'email' => $request['email'],
                        // 'is_login_enable' => !empty($request->password_switch) && $request->password_switch == 'on' ? 1 : 0,
                        'is_login_enable' => 1,
                        'password' => !empty($userpassword) ? Hash::make($userpassword) : null,
                        'type' => $role_r->name,
                        'avatar' => '',
                        // 'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'lang' => 'en',
                        'created_by' => Auth::user()->creatorId(),
                        'email_verified_at' => $date,
                    ]
                );
                $user->assignRole($role_r);

                // } else {
                //     return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                // }
            }

            // $setings = Utility::settings();
            // if ($setings['new_user'] == 1) {

            //     $uArr = [
            //         'email' => $user->email,
            //         'password' => $request->password,
            //     ];

            //     $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);

            //     return redirect()->route('user.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            // }

            return response()->json([
                'status'    => true,
                'message'   => 'User successfully created.'
            ], 201);
        } else {
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }

    public function show(User $user)
    {
        $userDetail = Auth::user();

        return response()->json([
            'status'    => true,
            'message'   => 'Successfully retrieved user',
            'data'      => $userDetail
        ]);
    }

    public function update(Request $request, $id)
    {
        // Define base validation rules
        $validationRules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'unique:users,email,' . $id,
        ];

        // Add password validation only if password field is present
        if ($request->has('password') && !empty($request->password)) {
            $validationRules['password'] = 'min:8';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        $user = User::findOrFail($id);
        $input = $request->all();

        // Handle password update if provided
        if ($request->has('password') && !empty($request->password)) {
            $input['password'] = bcrypt($request->password);
        } else {
            // Remove password from input if not provided
            unset($input['password']);
        }

        if (Auth::user()->type == 'super admin') {
            $user->fill($input)->save();
        } else {
            $role = Role::findById($request->type, 'web');
            $input['type'] = $role->name;
            $user->fill($input)->save();
            $user->assignRole($role);
        }

        return response()->json([
            'status' => true,
            'message' => 'User successfully updated.'
        ], 200);
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete User')) {
            $user = User::findOrFail($id);

            $sub_employee = Employee::where('created_by', $user->id)->delete();

            $sub_user = User::where('created_by', $user->id)->delete();
            $user->delete();

            return response()->json([
                'status'    => true,
                'message'   => 'User successfully deleted.'
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }
}
