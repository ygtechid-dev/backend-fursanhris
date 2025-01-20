<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\LoginDetail;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function login(LoginRequest $request)
    {
        try {
            // Validate credentials
            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();

            // Check user status
            if (!$user->is_active) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive',
                ], 403);
            }

            if ($user->is_disable == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been disabled',
                ], 403);
            }

            // Validate platform
            $platform = $request->platform;
            if (!in_array($platform, ['web', 'mobile'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid platform specified',
                ], 400);
            }

            // Revoke existing tokens for the same platform
            $user->tokens()->where('name', $platform)->delete();

            // Create new token with platform-specific name
            $token = $user->createToken($platform, [
                $platform === 'web' ? 'web-access' : 'mobile-access'
            ])->plainTextToken;

            // Store login details for analytics
            // $this->storeLoginDetails($request, $user, $platform);

            // Update last login
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
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
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
}
