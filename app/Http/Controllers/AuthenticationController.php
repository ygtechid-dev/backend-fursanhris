<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Employee;
use App\Models\LoginDetail;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function loginView()
    {
        return view('auth.login');
    }

    // public function login(LoginRequest $request)
    // {
    //     $request->authenticate();

    //     $user = Auth::user();
    //     if ($user->is_active == 0) {
    //         Auth::user()->tokens()->delete();
    //         return response()->json([
    //             'message'   => 'User inactive',
    //         ], 403);
    //     }

    //     if ($user->is_disable == 0) {
    //         Auth::user()->tokens()->delete();
    //         return response()->json([
    //             'message'   => 'User disabled',
    //         ], 403);
    //     }

    //     $user = Auth::user();

    //     // if ($user->type != 'company' && $user->type != 'super admin') {
    //     //     // $ip = '49.36.83.154'; // This is static ip address
    //     //     $ip = $_SERVER['REMOTE_ADDR']; // your ip address here
    //     //     $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));

    //     //     $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
    //     //     if ($whichbrowser->device->type == 'bot') {
    //     //         return;
    //     //     }
    //     //     $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;

    //     //     /* Detect extra details about the user */
    //     //     $query['browser_name'] = $whichbrowser->browser->name ?? null;
    //     //     $query['os_name'] = $whichbrowser->os->name ?? null;
    //     //     $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
    //     //     // $query['device_type'] = Utility::get_device_type($_SERVER['HTTP_USER_AGENT']);
    //     //     $query['referrer_host'] = !empty($referrer['host']);
    //     //     $query['referrer_path'] = !empty($referrer['path']);


    //     //     isset($query['timezone']) ? date_default_timezone_set($query['timezone']) : '';


    //     //     $json = json_encode($query);

    //     //     $login_detail = new LoginDetail();
    //     //     $login_detail->user_id = Auth::user()->id;
    //     //     $login_detail->ip = $ip;
    //     //     $login_detail->date = date('Y-m-d H:i:s');
    //     //     $login_detail->Details = $json;
    //     //     $login_detail->created_by = Auth::user()->creatorId();
    //     //     $login_detail->save();
    //     // }

    //     $token = $user->createToken(env('APP_NAME'))->plainTextToken;
    //     // $user->last_login = date('Y-m-d H:i:s');
    //     // $user->save();
    //     return response()->json([
    //         'message'   => 'Succcessfully Login',
    //         'data'      => Auth::user(),
    //         'token'     => $token
    //     ]);
    // }
    // {
    //     $credentials = $request->validate([
    //         'username' => 'required',
    //         'password' => 'required',
    //     ]);

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();

    //         if (!empty(Auth::user())) {
    //             return redirect()->route('dashboard.index');
    //         }
    //     }

    //     return back()->with('loginError', 'Login Failed!');
    // }

    // Revisi 2
    // public function login(LoginRequest $request)
    // {
    //     try {
    //         // Validate credentials
    //         if (!Auth::attempt($request->only('email', 'password'))) {
    //             throw ValidationException::withMessages([
    //                 'email' => ['The provided credentials are incorrect.'],
    //             ]);
    //         }

    //         $user = Auth::user();

    //         // Check user status
    //         if (!$user->is_active) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Your account is inactive',
    //             ], 403);
    //         }

    //         if ($user->is_disable == 0) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Your account has been disabled',
    //             ], 403);
    //         }

    //         // Validate platform
    //         $platform = $request->platform;
    //         if (!in_array($platform, ['web', 'mobile'])) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invalid platform specified',
    //             ], 400);
    //         }

    //         // Revoke existing tokens for the same platform
    //         $user->tokens()->where('name', $platform)->delete();

    //         // Create new token with platform-specific name
    //         $token = $user->createToken($platform, [
    //             $platform === 'web' ? 'web-access' : 'mobile-access'
    //         ])->plainTextToken;

    //         // Store login details for analytics
    //         // $this->storeLoginDetails($request, $user, $platform);

    //         // Update last login
    //         $user->update([
    //             'last_login' => now(),
    //             'last_login_platform' => $platform
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Login successful',
    //             'data' => [
    //                 'user' => $user,
    //                 'token' => $token,
    //                 'token_type' => 'Bearer',
    //                 'platform' => $platform
    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function login(LoginRequest $request)
    {
        try {
            // Validate login type
            $validLoginTypes = ['email', 'employee_id', 'phone'];
            if (!in_array($request->login_type, $validLoginTypes)) {
                throw ValidationException::withMessages([
                    'login_type' => ['Invalid login type. Must be email, employee_id, or phone.']
                ]);
            }

            // Prepare credentials array based on explicitly specified login type
            $credentials = [
                $request->login_type => $request->login,
                'password' => $request->password
            ];

            // Find user based on the specified login type
            $user = null;
            switch ($request->login_type) {
                case 'email':
                    $user = User::where('email', $request->login)->first();
                    break;
                case 'phone':
                    $user = User::whereHas('employee', function ($query) use ($request) {
                        $query->where('phone', $request->login);
                    })->first();
                    break;
                case 'employee_id':
                    $user = User::whereHas('employee', function ($query) use ($request) {
                        $query->where('employee_id', $request->login);
                    })->first();
                    break;
            }

            // Validate user exists
            if (!$user) {
                throw ValidationException::withMessages([
                    'login' => ['No account found with the provided identifier.']
                ]);
            }

            // Additional validation steps
            // Attempt authentication using password verification
            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'login' => ['The provided credentials are incorrect.']
                ]);
            }

            // Check user account status
            if (!$user->is_active) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }

            if ($user->is_disable == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been disabled'
                ], 403);
            }

            // Validate platform
            $platform = $request->platform;
            if (!in_array($platform, ['web', 'mobile'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid platform specified'
                ], 400);
            }

            // Revoke existing tokens for the same platform
            $user->tokens()->where('name', $platform)->delete();

            // Create new token with platform-specific name
            $token = $user->createToken($platform, [
                $platform === 'web' ? 'web-access' : 'mobile-access'
            ])->plainTextToken;

            // Update last login information
            $user->update([
                'last_login' => now(),
                'last_login_platform' => $platform
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'platform' => $platform
                ]
            ], 200);
        } catch (\Exception $e) {
            // Log the error for internal tracking
            Log::error('Login Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registerMobile(Request $request)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:employees',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company_id' => 'required|string',
            'password' => 'required|confirmed',
        ]);
        $platform = 'mobile';

        /** Buat pengecekan Company Id ada atau tidaknya */
        $companyUser = User::where('company_id', $validatedData['company_id'])->first();

        if (empty($companyUser)) {
            return response()->json([
                'status' => false,
                'message' => 'Company ID not found'
            ], 404);
        }

        // Create a new User instance
        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'type' => 'employee',
            'avatar' => '',
            'lang' => 'en',
            'company_id' => $validatedData['company_id'],
            'password' => Hash::make($validatedData['password']),
            'created_by' => $companyUser->id,
        ]);
        $user->assignRole('employee');

        Employee::create(
            [
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $validatedData['phone'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'employee_id' => (new EmployeeController)->employeeNumber($companyUser->id),
                'created_by' => $companyUser->id,
            ]
        );

        // Create new token with platform-specific name
        $token = $user->createToken($platform, [
            $platform === 'web' ? 'web-access' : 'mobile-access'
        ])->plainTextToken;

        // Update last login information
        $user->update([
            'last_login' => now(),
            // 'last_login_platform' => $platform
        ]);

        // Log the user in or redirect them to the login page
        /** Format response bukan redirect tapi ganti menjadi json */
        return response()->json([
            'status' => true,
            'message' => 'Sign up successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'platform' => $platform
            ]
        ], 200);
    }

    public function updateAccountProfile(Request $request)
    {
        // dd($request->all());
        $user = User::with('employee')->find(Auth::user()->id);

        $user->update([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' => $request['phone'],
        ]);

        Employee::where('user_id', $user->id)->update([
            'name' => $user->first_name . ' ' . $user->last_name,
            'phone' => $request['phone'],
            'dob' => $request['dob'],
            'branch_id' => 1, // buat logic pengecekan departement dan branch id dari designation
            'department_id' => 1,
            'designation_id' => $request['designation_id'],
            'address' => $request['address'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Update account profile successful',
            'data' => [
                'user' => $user,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            // Get current token's platform
            $platform = $request->user()->currentAccessToken()->name;

            // Only revoke current platform's token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => "Successfully logged out from $platform",
                'platform' => $platform
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Generate reset token
        // $token = Str::random(60);
        $token = 'token';

        // Store reset token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        //Delete Existing tokens
        $user = User::where('email', $request->email)->first();
        $user->tokens()->where('name', 'mobile')->delete();



        // Send reset email
        // Mail::to($request->email)->send(new PasswordResetMail($token));

        return response()->json([
            'status' => true,
            'message' => 'Password reset link sent to your email, for testing only password reset is "token"'
        ], 200);
    }

    public function checkResetPasswordToken(Request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Reset token is correct'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed'
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ], 200);
    }
}
