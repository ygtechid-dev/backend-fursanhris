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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function loginView()
    {
        return view('auth.login');
    }

    /**
     * Custom password verification dengan fallback untuk berbagai hash algorithm
     */
    private function verifyPassword($plainPassword, $hashedPassword, $user)
    {
        // Debug logging
        Log::info("Attempting password verification for user: {$user->email}");
        Log::info("Hash algorithm detected: " . substr($hashedPassword, 0, 4));
        
        // 1. Coba bcrypt check dulu (Laravel default)
        try {
            $bcryptResult = Hash::check($plainPassword, $hashedPassword);
            Log::info("Bcrypt check result: " . ($bcryptResult ? 'true' : 'false'));
            
            if ($bcryptResult) {
                Log::info("Password verified with bcrypt for user: {$user->email}");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Bcrypt check failed for user {$user->email}: " . $e->getMessage());
        }
        
        // 2. Cek apakah menggunakan Blowfish ($2a$, $2x$, $2y$)
        if (preg_match('/^\$2[axy]\$/', $hashedPassword)) {
            Log::info("Detected Blowfish hash for user: {$user->email}");
            
            // Gunakan password_verify PHP native untuk Blowfish
            if (password_verify($plainPassword, $hashedPassword)) {
                Log::info("Password verified with Blowfish for user: {$user->email}");
                
                // Migrate ke Laravel bcrypt ($2y$)
                $newHashedPassword = Hash::make($plainPassword);
                $user->password = $newHashedPassword;
                $user->save();
                Log::info("Password migrated from Blowfish to Laravel bcrypt for user: {$user->email}");
                return true;
            }
        }
        
        // 3. Cek apakah password plain text (exact match)
        if ($hashedPassword === $plainPassword) {
            Log::info("Password matched as plain text for user: {$user->email}");
            // Update password ke bcrypt untuk security
            $newHashedPassword = Hash::make($plainPassword);
            $user->password = $newHashedPassword;
            $user->save();
            Log::info("Password migrated from plain text to bcrypt for user: {$user->email}");
            return true;
        }
        
        // 4. Cek apakah password di-hash dengan algorithm lain (MD5, SHA1, etc)
        $algorithms = [
            'md5' => md5($plainPassword),
            'sha1' => sha1($plainPassword),
            'sha256' => hash('sha256', $plainPassword),
        ];
        
        foreach ($algorithms as $algo => $hashedPlain) {
            if ($hashedPassword === $hashedPlain) {
                Log::info("Password matched with {$algo} for user: {$user->email}");
                // Update password ke bcrypt
                $newHashedPassword = Hash::make($plainPassword);
                $user->password = $newHashedPassword;
                $user->save();
                Log::info("Password migrated from {$algo} to bcrypt for user: {$user->email}");
                return true;
            }
        }
        
        // 5. Cek case insensitive untuk plain text
        if (strtolower($hashedPassword) === strtolower($plainPassword)) {
            Log::info("Password matched case-insensitive for user: {$user->email}");
            $newHashedPassword = Hash::make($plainPassword);
            $user->password = $newHashedPassword;
            $user->save();
            Log::info("Password migrated (case-insensitive) to bcrypt for user: {$user->email}");
            return true;
        }
        
        Log::warning("All password verification methods failed for user: {$user->email}");
        return false;
    }

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

            // Find user based on the specified login type
            $user = null;
            switch ($request->login_type) {
                case 'email':
                    $user = User::where('email', $request->login)->first();
                    Log::info("Searching user by email: {$request->login}");
                    break;
                case 'phone':
                    $user = User::whereHas('employee', function ($query) use ($request) {
                        $query->where('phone', $request->login);
                    })->first();
                    Log::info("Searching user by phone: {$request->login}");
                    break;
                case 'employee_id':
                    $user = User::whereHas('employee', function ($query) use ($request) {
                        $query->where('employee_id', $request->login);
                    })->first();
                    Log::info("Searching user by employee_id: {$request->login}");
                    break;
            }

            // Log user search result
            if ($user) {
                Log::info("User found: ID={$user->id}, Email={$user->email}, Type={$user->type}");
            } else {
                Log::warning("No user found for login_type={$request->login_type}, login={$request->login}");
            }

            // Validate user exists
            if (!$user) {
                throw ValidationException::withMessages([
                    'login' => ['No account found with the provided identifier.']
                ]);
            }

            // Additional validation steps
            // Attempt authentication using custom password verification
            if (!$this->verifyPassword($request->password, $user->password, $user)) {
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

            // *** CHECK TERMINATION STATUS ***
            if (method_exists($user, 'isTerminated') && $user->isTerminated()) {
                // Get active termination data
                $termination = $user->activeTermination()->first();

                // If the user is terminated and trying to access via web
                if ($platform === 'web' && $termination->is_mobile_access_allowed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Terminated employees can only access via mobile application'
                    ], 403);
                }

                // If the user is terminated and not allowed to access via any platform
                if (!$termination->is_mobile_access_allowed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account has been terminated and access is no longer available'
                    ], 403);
                }
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

            // Include termination data in response if user is terminated
            $responseData = [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'platform' => $platform
            ];

            if (method_exists($user, 'isTerminated') && $user->isTerminated()) {
                $responseData['termination'] = [
                    'status' => 'terminated',
                    'termination_date' => $user->activeTermination()->first()->termination_date
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => $responseData
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
            'subscription' => 'Basic', // Default subscription
            'plan' => 0, // Default plan Basic
            'is_active' => 1,
            'is_login_enable' => 1,
            'dark_mode' => 0,
            'messenger_color' => '#2180f3',
            'is_disable' => 1,
            'company_id' => $validatedData['company_id'],
            'password' => Hash::make($validatedData['password']),
            'created_by' => $companyUser->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('employee');

        Employee::create([
            'user_id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'employee_id' => (new EmployeeController)->employeeNumber($companyUser->id),
            'created_by' => $companyUser->id,
        ]);

        // Create new token with platform-specific name
        $token = $user->createToken($platform, [
            $platform === 'web' ? 'web-access' : 'mobile-access'
        ])->plainTextToken;

        // Update last login information
        $user->update([
            'last_login' => now(),
        ]);

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
            'branch_id' => 1,
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

    public function updateAccountPhotoProfile(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = User::with('employee')->find(Auth::user()->id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $updateData = [];

            // Handle upload avatar menggunakan Laravel Storage
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');

                if ($avatar->isValid()) {
                    // Generate nama file unik
                    $fileName = time() . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();

                    // Upload file ke storage/app/public/avatars
                    $path = $avatar->storeAs('avatars', $fileName, 'public');

                    if ($path) {
                        // Simpan full URL ke database agar bisa diakses langsung dari mobile
                        $fullUrl = url(Storage::url('avatars/' . $fileName));
                        $updateData['avatar'] = $fullUrl;
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Failed to upload avatar',
                        ], 500);
                    }
                }
            }

            // Update user data
            if (!empty($updateData)) {
                $user->update($updateData);
                $user->refresh();
            }

            return response()->json([
                'status' => true,
                'message' => 'Update account profile successful',
                'data' => [
                    'user' => $user,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating profile',
                'error' => $e->getMessage(),
            ], 500);
        }
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

        // Update password dengan hash yang benar
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

    public function checkAuth()
    {
        return response()->json([
            'status' => true,
            'message' => 'Profile retrived successfully',
            'data' => [
                'user' => User::with('employee.designation')->find(Auth::id()),
            ]
        ], 200);
    }

    /**
     * Method untuk debug password - HAPUS DI PRODUCTION!
     */
    public function debugPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $debugInfo = [
            'user_id' => $user->id,
            'email' => $user->email,
            'stored_password' => $user->password,
            'stored_password_length' => strlen($user->password),
            'input_password' => $request->password,
            'input_password_length' => strlen($request->password),
            'hash_algorithm' => $this->detectHashAlgorithm($user->password),
            'bcrypt_check' => false,
            'blowfish_check' => false,
            'plain_text_match' => $user->password === $request->password,
            'md5_match' => $user->password === md5($request->password),
            'sha1_match' => $user->password === sha1($request->password),
            'sha256_match' => $user->password === hash('sha256', $request->password),
        ];

        try {
            $debugInfo['bcrypt_check'] = Hash::check($request->password, $user->password);
        } catch (\Exception $e) {
            $debugInfo['bcrypt_error'] = $e->getMessage();
        }

        // Test Blowfish dengan password_verify
        if (preg_match('/^\$2[axy]\$/', $user->password)) {
            $debugInfo['blowfish_check'] = password_verify($request->password, $user->password);
        }

        return response()->json([
            'status' => true,
            'message' => 'Debug info retrieved',
            'debug' => $debugInfo
        ], 200);
    }

    /**
     * Detect hash algorithm dari password
     */
    private function detectHashAlgorithm($password)
    {
        if (preg_match('/^\$2y\$/', $password)) {
            return 'bcrypt (Laravel)';
        } elseif (preg_match('/^\$2a\$/', $password)) {
            return 'blowfish ($2a$)';
        } elseif (preg_match('/^\$2x\$/', $password)) {
            return 'blowfish ($2x$)';
        } elseif (strlen($password) === 32 && ctype_xdigit($password)) {
            return 'md5';
        } elseif (strlen($password) === 40 && ctype_xdigit($password)) {
            return 'sha1';
        } elseif (strlen($password) === 64 && ctype_xdigit($password)) {
            return 'sha256';
        } else {
            return 'unknown/plain_text';
        }
    }

    /**
     * Method untuk reset password user tertentu - HAPUS DI PRODUCTION!
     */
    public function forceResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully',
            'data' => [
                'email' => $user->email,
                'new_password_hash' => $user->password
            ]
        ], 200);
    }
}