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
            } else {
                $users = User::where('created_by', '=', $user->creatorId())
                    ->where('type', '!=', 'employee')
                    ->get();
            }

            return response()->json([
                'status'    => true,
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

    public function getUser()
    {
        if (Auth::user()->can('Manage User')) {
            $companyId = Auth::user()->id;

            // Dapatkan company user
            $company = User::where('id', $companyId)
                ->when(Auth::user()->type != 'super admin', function ($q) {
                    $q->where('type', 'company');
                })
                ->first();

            // Dapatkan semua users yang dibuat oleh company tersebut
            $employeeUsers = User::when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
                ->where('id', '!=', $companyId)
                ->get();

            // Gabungkan company dengan employee users
            $allUsers = collect([$company])->merge($employeeUsers)
                ->filter() // Remove null values
                ->map(function ($user) {
                    return [
                        'id' => $user?->id,
                        'name' => $user?->employee_name(),
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'type' => $user->type,
                        'subscription' => $user->subscription,
                        'created_by' => $user->created_by,
                        'employee' => $user->type == 'employee' ? $user->employee : null,
                    ];
                });

            return response()->json([
                'message' => 'Successfully retrieved data',
                'data' => $allUsers
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('Permission denied.')
            ], 403);
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
            return response()->json([
                'status'    => false,
                'message' => __('Permission denied.')
            ], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create User')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|unique:users',
                    'subscription' => 'nullable|string|max:255',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status'   => false,
                    'message'   => $messages->first()
                ], 400);
            }

            if (!empty($request->password_switch) && $request->password_switch == 'on') {
                $validator = Validator::make(
                    $request->all(),
                    ['password' => 'required|min:6']
                );

                if ($validator->fails()) {
                    return response()->json([
                        'status'    => false,
                        'message'   => $validator->errors()->first()
                    ], 400);
                }
            }

            if (Auth::user()->type == 'super admin') {
                $date = date("Y-m-d H:i:s");
                $userpassword = $request->input('password');
                
                $user = User::create(
                    [
                        'first_name' => $request['first_name'],
                        'last_name' => $request['last_name'],
                        'email' => $request['email'],
                        'subscription' => $request['subscription'] ?? 'Basic', // Default subscription
                        'is_login_enable' => 1,
                        'password' => !empty($userpassword) ? Hash::make($userpassword) : Hash::make('password123'), // Default password jika kosong
                        'type' => 'company',
                        'avatar' => '',
                        'company_id' => Utility::generateCompanyId(),
                        'lang' => 'en',
                        'plan' => 0, // Default plan Basic
                        'is_active' => 1,
                        'dark_mode' => 0,
                        'messenger_color' => '#2180f3',
                        'is_disable' => 1,
                        'created_by' => Auth::user()->id,
                        'email_verified_at' => $date,
                    ]
                );

                $user->assignRole('Company');
                $role_r = Role::findById(2);
                Utility::MakeRole($user->id);
            } else {
                $objUser = Auth::user()->creatorId();
                $objUser = User::find($objUser);
                $userpassword = $request->input('password');

                $role_r = Role::findById($request->type, 'web');
                $date = date("Y-m-d H:i:s");
                
                $user = User::create(
                    [
                        'first_name' => $request['first_name'],
                        'last_name' => $request['last_name'],
                        'email' => $request['email'],
                        'subscription' => $request['subscription'] ?? 'Basic', // Default subscription
                        'is_login_enable' => 1,
                        'password' => !empty($userpassword) ? Hash::make($userpassword) : Hash::make('password123'), // Default password jika kosong
                        'type' => $role_r->name,
                        'avatar' => '',
                        'lang' => 'en',
                        'plan' => 0, // Default plan Basic
                        'is_active' => 1,
                        'dark_mode' => 0,
                        'messenger_color' => '#2180f3',
                        'is_disable' => 1,
                        'created_by' => Auth::user()->creatorId(),
                        'email_verified_at' => $date,
                    ]
                );
                $user->assignRole($role_r);
            }

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
            'subscription' => 'nullable|string|max:255',
        ];

        // Add password validation only if password field is present
        if ($request->has('password') && !empty($request->password)) {
            $validationRules['password'] = 'min:8';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status'   => false,
                'message'   => $messages->first()
            ], 400);
        }

        $user = User::findOrFail($id);
        $input = $request->all();

        // Handle password update if provided
        if ($request->has('password') && !empty($request->password)) {
            // Pastikan password selalu di-hash dengan bcrypt
            $input['password'] = Hash::make($request->password);
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

    /**
     * Method untuk migrate password lama ke bcrypt
     * Panggil ini jika ada user dengan password plain text
     */
    public function migratePasswords()
    {
        if (Auth::user()->type == 'super admin') {
            $users = User::all();
            $migrated = 0;

            foreach ($users as $user) {
                // Cek apakah password sudah bcrypt (biasanya panjang 60 karakter)
                if (strlen($user->password) < 60) {
                    // Anggap ini plain text, hash dengan bcrypt
                    $user->password = Hash::make($user->password);
                    $user->save();
                    $migrated++;
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Successfully migrated {$migrated} passwords to bcrypt."
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.'
            ], 403);
        }
    }
}