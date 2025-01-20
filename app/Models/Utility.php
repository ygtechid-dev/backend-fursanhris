<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            "employee_prefix" => "#EMP00",
            "footer_title" => "",
            "footer_notes" => "",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
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
            "company_logo" => 'logo-dark.png',
            "company_logo_light" => 'logo-light.png',
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

        $hr_permission = Role::where('name', 'hr')->where('created_by', $company_id)->where('guard_name', 'sanctum')->first();

        if (empty($hr_permission)) {
            $hr_permission                   = new Role();
            $hr_permission->name             = 'hr';
            $hr_permission->guard_name       = 'sanctum';
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

        $employee_permission = Role::where('name', 'employee')->where('created_by', $company_id)->where('guard_name', 'sanctum')->first();

        if (empty($employee_permission)) {
            $employee_permission                   = new Role();
            $employee_permission->name             = 'employee';
            $employee_permission->guard_name       = 'sanctum';
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
}
