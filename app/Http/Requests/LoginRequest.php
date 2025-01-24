<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'email' => ['required', 'string', 'email'],
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'platform' => 'required|in:web,mobile'
        ];
    }

    public function authenticate()
    {
        // custom login
        $users = User::where('email', $this->email)->get();
        $id = 0;

        if (count($users) > 0) {
            foreach ($users as $key => $user) {
                if (password_verify($this->password, $user->password)) {
                    if ($user->is_active != 1 || $user->is_disable != 1 && $user->type != "super admin") {
                        throw ValidationException::withMessages([
                            'email' => __("Your Account is disable, please contact your Administrate."),
                        ]);
                    } elseif ($user->is_login_enable != 1) {
                        throw ValidationException::withMessages([
                            'email' => __("Your account is disabled from company."),
                        ]);
                    }
                    $id = $user->id;

                    break;
                }
            }
        } else {
            throw ValidationException::withMessages([
                'email' => __("this email doesn't match"),
            ]);
        }


        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password, 'id' => $id], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }
}
