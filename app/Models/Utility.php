<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class Utility extends Model
{
    private static $getsettings = null;
    private static $languages = null;
    private static $settings = null;
    private static $payments = null;
    private static $cookies = null;

    public static function settings()
    {
        if (self::$settings === null) {
            self::$settings = self::fetchSettings();
        }
        return self::$settings;
    }

    public static function fetchSettings($user_id = null)
    {
        $data = DB::table('settings');
        if ($user_id != null) {
            $user = User::where('id', $user_id)->first();
            if ($user) {
                $data = $data->where('created_by', '=', $user_id)->get();
            } else {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        }
        if (Auth::check()) {

            $data = $data->where('created_by', '=', Auth::user()->creatorId())->get();
            if (count($data) == 0) {
                $data = DB::table('settings')->where('created_by', '=', 1)->get();
            }
        } else {

            $data->where('created_by', '=', 1);
            $data = $data->get();
        }

        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "company_start_time" => "09:00:00",
            "company_end_time" => "18:00:00",
            "company_logo" => 'logo-dark.png',
            "company_logo_light" => 'logo-light.png',
            'company_timezone' => 'UTC',
            "employee_prefix" => "#EMP00",
            "footer_title" => "",
            "footer_notes" => "",
            'new_user' => '1',
            'new_employee' => '1',
            'new_payroll' => '1',
            'new_ticket' => '1',
            'new_award' => '1',
            'employee_transfer' => '1',
            'employee_resignation' => '1',
            'employee_trip' => '1',
            'employee_promotion' => '1',
            'employee_complaints' => '1',
            'employee_warning' => '1',
            'employee_termination' => '1',
            'leave_status' => '1',
            'contract' => '1',
            "default_language" => "en",
            "display_landing_page" => "on",
            "ip_restrict" => "on",
            "title_text" => "",
            "footer_text" => "",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "metakeyword" => "",
            "metadesc" => "",
            "zoom_account_id" => "",
            "zoom_client_id" => "",
            "zoom_client_secret" => "",
            'disable_signup_button' => "on",
            "theme_color" => "theme-3",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "SITE_RTL" => "off",
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "contract_prefix" => "#CON",
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            "google_clender_id" => "",
            "google_calender_json_file" => "",
            "is_enabled" => "",
            "email_verification" => "",
            // "seo_is_enabled" => "",
            "meta_title" => "",
            "meta_image" => "",
            "meta_description" => "",
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            'contactus_url' => '#',
            'chatgpt_key' => '',
            'chatgpt_model' => '',
            'enable_chatgpt' => '',
            'mail_driver' => '',
            'mail_host' => '',
            'mail_port' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'timezone' => '',
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster' => '',
            'recaptcha_module' => '',
            'google_recaptcha_key' => '',
            'google_recaptcha_secret' => '',
            'google_recaptcha_version' => '',
            'color_flag' => 'false',
            'zkteco_api_url' => '',
            'username' => '',
            'user_password' => '',
            'auth_token' => '',
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getValByName($key)
    {
        $setting = Utility::settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }
        return $setting[$key];
    }

    public static function getStorageSetting()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data     = $data->get();
        $settings = [

            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    // get date format
    public static function getDateFormated($date, $time = false)
    {
        if (!empty($date) && $date != '0000-00-00') {
            if ($time == true) {
                return date("d M Y H:i A", strtotime($date));
            } else {
                return date("d M Y", strtotime($date));
            }
        } else {
            return '';
        }
    }

    public static function MakeRole($company_id)
    {
        $data = [];
        $hr_role_permission = [
            "Manage Language",
            "Manage User",
            "Create User",
            "Edit User",
            "Delete User",
            "Manage Award",
            "Create Award",
            "Edit Award",
            "Delete Award",
            "Manage Transfer",
            "Create Transfer",
            "Edit Transfer",
            "Delete Transfer",
            "Manage Resignation",
            "Create Resignation",
            "Edit Resignation",
            "Delete Resignation",
            "Manage Travel",
            "Create Travel",
            "Edit Travel",
            "Delete Travel",
            "Manage Promotion",
            "Create Promotion",
            "Edit Promotion",
            "Delete Promotion",
            "Manage Complaint",
            "Create Complaint",
            "Edit Complaint",
            "Delete Complaint",
            "Manage Warning",
            "Create Warning",
            "Edit Warning",
            "Delete Warning",
            "Manage Termination",
            "Create Termination",
            "Edit Termination",
            "Delete Termination",
            "Manage Department",
            "Create Department",
            "Edit Department",
            "Delete Department",
            "Manage Designation",
            "Create Designation",
            "Edit Designation",
            "Delete Designation",
            "Manage Document Type",
            "Create Document Type",
            "Edit Document Type",
            "Delete Document Type",
            "Manage Branch",
            "Create Branch",
            "Edit Branch",
            "Delete Branch",
            "Manage Award Type",
            "Create Award Type",
            "Edit Award Type",
            "Delete Award Type",
            "Manage Termination Type",
            "Create Termination Type",
            "Edit Termination Type",
            "Delete Termination Type",
            "Manage Employee",
            "Create Employee",
            "Edit Employee",
            "Delete Employee",
            "Show Employee",
            "Manage Payslip Type",
            "Create Payslip Type",
            "Edit Payslip Type",
            "Delete Payslip Type",
            "Manage Allowance Option",
            "Create Allowance Option",
            "Edit Allowance Option",
            "Delete Allowance Option",
            "Manage Loan Option",
            "Create Loan Option",
            "Edit Loan Option",
            "Delete Loan Option",
            "Manage Deduction Option",
            "Create Deduction Option",
            "Edit Deduction Option",
            "Delete Deduction Option",
            "Manage Set Salary",
            "Create Set Salary",
            "Edit Set Salary",
            "Delete Set Salary",
            "Manage Allowance",
            "Create Allowance",
            "Edit Allowance",
            "Delete Allowance",
            "Create Commission",
            "Create Loan",
            "Create Saturation Deduction",
            "Create Other Payment",
            "Create Overtime",
            "Edit Commission",
            "Delete Commission",
            "Edit Loan",
            "Delete Loan",
            "Edit Saturation Deduction",
            "Delete Saturation Deduction",
            "Edit Other Payment",
            "Delete Other Payment",
            "Edit Overtime",
            "Delete Overtime",
            "Manage Pay Slip",
            "Create Pay Slip",
            "Edit Pay Slip",
            "Delete Pay Slip",
            "Manage Event",
            "Create Event",
            "Edit Event",
            "Delete Event",
            "Manage Announcement",
            "Create Announcement",
            "Edit Announcement",
            "Delete Announcement",
            "Manage Leave Type",
            "Create Leave Type",
            "Edit Leave Type",
            "Delete Leave Type",
            "Manage Leave",
            "Create Leave",
            "Edit Leave",
            "Delete Leave",
            "Manage Meeting",
            "Create Meeting",
            "Edit Meeting",
            "Delete Meeting",
            "Manage Ticket",
            "Create Ticket",
            "Edit Ticket",
            "Delete Ticket",
            "Manage Attendance",
            "Create Attendance",
            "Edit Attendance",
            "Delete Attendance",
            "Manage TimeSheet",
            "Create TimeSheet",
            "Edit TimeSheet",
            "Delete TimeSheet",
            'Manage Assets',
            'Create Assets',
            'Edit Assets',
            'Delete Assets',
            'Manage Document',
            'Manage Employee Profile',
            'Show Employee Profile',
            'Manage Employee Last Login',
            'Manage Indicator',
            'Create Indicator',
            'Edit Indicator',
            'Delete Indicator',
            'Show Indicator',
            'Manage Appraisal',
            'Create Appraisal',
            'Edit Appraisal',
            'Delete Appraisal',
            'Show Appraisal',
            "Manage Goal Type",
            "Create Goal Type",
            "Edit Goal Type",
            "Delete Goal Type",
            "Manage Goal Tracking",
            "Create Goal Tracking",
            "Edit Goal Tracking",
            "Delete Goal Tracking",
            "Manage Company Policy",
            "Create Company Policy",
            "Edit Company Policy",
            "Delete Company Policy",
            "Manage Trainer",
            "Create Trainer",
            "Edit Trainer",
            "Delete Trainer",
            "Show Trainer",
            "Manage Training",
            "Create Training",
            "Edit Training",
            "Delete Training",
            "Show Training",
            "Manage Training Type",
            "Create Training Type",
            "Edit Training Type",
            "Delete Training Type",
            "Manage Holiday",
            "Create Holiday",
            "Edit Holiday",
            "Delete Holiday",
            "Manage Job Category",
            "Create Job Category",
            "Edit Job Category",
            "Delete Job Category",
            "Manage Job Stage",
            "Create Job Stage",
            "Edit Job Stage",
            "Delete Job Stage",
            "Manage Job",
            "Create Job",
            "Edit Job",
            "Delete Job",
            "Show Job",
            "Manage Job Application",
            "Create Job Application",
            "Edit Job Application",
            "Delete Job Application",
            "Show Job Application",
            "Move Job Application",
            "Add Job Application Note",
            "Delete Job Application Note",
            "Add Job Application Skill",
            "Manage Job OnBoard",
            "Manage Custom Question",
            "Create Custom Question",
            "Edit Custom Question",
            "Delete Custom Question",
            "Manage Interview Schedule",
            "Create Interview Schedule",
            "Edit Interview Schedule",
            "Delete Interview Schedule",
            "Manage Career",
            "Manage Performance Type",
            "Create Performance Type",
            "Edit Performance Type",
            "Delete Performance Type",
            "Manage Contract",
            "Create Contract",
            "Edit Contract",
            "Delete Contract",
            "Store Note",
            "Delete Note",
            "Store Comment",
            "Delete Comment",
            "Delete Attachment",
            "Manage Contract Type",
            "Create Contract Type",
            "Edit Contract Type",
            "Delete Contract Type",
        ];

        $hr_permission = Role::where('name', 'hr')->where('created_by', $company_id)->where('guard_name', 'web')->first();

        if (empty($hr_permission)) {
            $hr_permission                   = new Role();
            $hr_permission->name             = 'hr';
            $hr_permission->guard_name       = 'web';
            $hr_permission->created_by       = $company_id;
            $hr_permission->save();
            foreach ($hr_role_permission as $permission_s) {
                $permission = Permission::where('name', $permission_s)->first();
                $hr_permission->givePermissionTo($permission);
            }
        }

        $employee_role_permission = [
            "Manage Award",
            "Manage Transfer",
            "Manage Resignation",
            "Create Resignation",
            "Edit Resignation",
            "Delete Resignation",
            "Manage Travel",
            "Manage Promotion",
            "Manage Complaint",
            "Create Complaint",
            "Edit Complaint",
            "Delete Complaint",
            "Manage Warning",
            "Create Warning",
            "Edit Warning",
            "Delete Warning",
            "Manage Termination",
            "Manage Employee",
            "Edit Employee",
            "Show Employee",
            "Manage Allowance",
            "Manage Event",
            "Manage Announcement",
            "Manage Leave",
            "Create Leave",
            "Edit Leave",
            "Delete Leave",
            "Manage Meeting",
            "Manage Ticket",
            "Create Ticket",
            "Edit Ticket",
            "Delete Ticket",
            "Manage Language",
            "Manage TimeSheet",
            "Create TimeSheet",
            "Edit TimeSheet",
            "Delete TimeSheet",
            "Manage Attendance",
            'Manage Document',
            "Manage Holiday",
            "Manage Career",
            "Manage Contract",
            "Store Note",
            "Delete Note",
            "Store Comment",
            "Delete Comment",
            "Delete Attachment",
        ];

        $employee_permission = Role::where('name', 'employee')->where('created_by', $company_id)->where('guard_name', 'web')->first();

        if (empty($employee_permission)) {
            $employee_permission                   = new Role();
            $employee_permission->name             = 'employee';
            $employee_permission->guard_name       = 'web';
            $employee_permission->created_by       = $company_id;
            $employee_permission->save();
            foreach ($employee_role_permission as $permission_s) {
                $permission = Permission::where('name', $permission_s)->first();
                $employee_permission->givePermissionTo($permission);
            }
        }

        $data['employee_permission'] = $employee_permission;

        return $data;
    }

    public static function generateCompanyId()
    {
        // Combines current timestamp with a short random string
        return 'CMP-' . time() . Str::random(5);
        // Example output: COMP-1705988234x9k2
    }

    public static function AnnualLeaveCycle()
    {
        $start_date = '' . date('Y') . '-01-01';
        $end_date = '' . date('Y') . '-12-31';
        $start_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

        $date['start_date'] = $start_date;
        $date['end_date']   = $end_date;

        return $date;
    }

    public static function checkLeaveRemainingbyType($employee_id, $leave_type_id)
    {
        // Find leave type
        $leave_type = LeaveType::find($leave_type_id);
        if (!$leave_type) {
            return [
                'status' => false,
                'message' => 'Leave type not found.',
                'data' => null
            ];
        }

        // Get annual leave cycle dates
        $date = Utility::AnnualLeaveCycle();

        // Calculate approved leaves
        $leaves_used = Leave::where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Calculate pending leaves
        $leaves_pending = Leave::where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Calculate remaining leaves
        $remaining_leaves = $leave_type->days - $leaves_used;

        return [
            'status' => true,
            'data' => [
                'leave_type' => $leave_type->title,
                'total_allowed_days' => $leave_type->days,
                'leaves_used' => intval($leaves_used),
                'leaves_pending' => intval($leaves_pending),
                'remaining_leaves' => intval($remaining_leaves)
            ]
        ];
    }

    public static function getCompanySchedule($companyId, $countryCode = null)
    {
        $settings = self::settings();

        return $settings;
    }

    public static function convertToCompanyTime($time, $companyId, $countryCode = null)
    {
        $schedule = self::getCompanySchedule($companyId, $countryCode);
        $datetime = new \DateTime($time, new \DateTimeZone('UTC'));
        $datetime->setTimezone(new \DateTimeZone($schedule['company_timezone']));
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function fetchCompanySettings()
    {
        if (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
            try {
                // Daftar field yang diharapkan
                $defaultSettings = [
                    'company_name' => '',
                    'company_address' => '',
                    'company_city' => '',
                    'company_state' => '',
                    'company_zipcode' => '',
                    'company_country' => '',
                    'company_telephone' => '',
                    'company_start_time' => '',
                    'company_end_time' => '',
                    'company_timezone' => 'UTC', // Default timezone
                    'ip_restrict' => ''
                ];

                // Ambil data dari database
                $settings = DB::table('settings')
                    ->where('created_by', Auth::user()->creatorId())
                    ->whereIn('name', array_keys($defaultSettings))
                    ->get()
                    ->pluck('value', 'name')
                    ->toArray();

                // Gabungkan dengan default settings
                $settings = array_merge($defaultSettings, $settings);

                return response()->json([
                    'status' => true,
                    'data' => $settings,
                    'message' => 'Company settings retrieved successfully.'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while fetching company settings: ' . $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }
    }


    public static function saveCompanySettings(Request $request)
    {
        if (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {

            $user = Auth::user();
            $request->validate(
                [
                    'company_name' => 'nullable|string|max:255',
                    'company_address' => 'nullable',
                    'company_city' => 'nullable',
                    'company_state' => 'nullable',
                    'company_zipcode' => 'nullable',
                    'company_country' => 'nullable',
                    'company_telephone' => 'nullable',
                    'company_start_time' => 'nullable',
                    'company_end_time' => 'nullable',
                    'company_timezone' => 'nullable',
                    // 'company_email' => 'nullable',
                    // 'company_email_from_name' => 'nullable|string',
                ]
            );
            $post = $request->all();
            if (!isset($request->ip_restrict)) {
                $post['ip_restrict'] = 'off';
            }
            unset($post['_token']);

            $settings = Utility::settings();
            foreach ($post as $key => $data) {
                if ((in_array($key, array_keys($settings)) && $data !== null)) {

                    DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            // $settings,
                            $data,
                            $key,
                            Auth::user()->creatorId(),
                        ]
                    );
                }
            }

            // return redirect()->back()->with('success', __('Setting successfully updated.'));
            return response()->json([
                'status' => true,
                'message' => 'Setting successfully updated.',
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing update.',
            ], 500);
        }
    }

    public static function formatDateTimeToCompanyTz($dateTime, $companyTz)
    {
        if (!$dateTime || $dateTime === '0000-00-00 00:00:00') {
            return null;
        }
        return Carbon::parse($dateTime)->setTimezone($companyTz);
    }
}
