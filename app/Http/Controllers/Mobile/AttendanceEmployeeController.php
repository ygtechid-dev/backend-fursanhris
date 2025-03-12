<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AttendanceEmployeeController extends Controller
{
    protected $attendance;

    public function __construct(AttendanceEmployee $attendance)
    {
        $this->attendance = $attendance;
    }

    /**
     * Get attendance history for a specific employee
     */
    public function getEmployeeHistory(Request $request, $employee_id)
    {
        try {
            $start_date = $request->get('start_date') ?? null;
            $end_date = $request->get('end_date') ?? null;

            // Get company timezone
            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            $history = $this->attendance->getEmployeeAttendanceHistory(
                $employee_id,
                $start_date,
                $end_date,
                $companyTz
            );

            return response()->json([
                'status' => true,
                'message' => 'Employee attendance history retrieved successfully',
                'data' => [
                    'employee_id' => $employee_id,
                    'company_timezone' => $companyTz,
                    'attendance_records' => $history
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve attendance history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadAttendanceDetail($id)
    {
        try {
            $attendance = AttendanceEmployee::with('employee')
                ->findOrFail($id);

            // Get company timezone
            $companyTz = $attendance->timezone ?? 'UTC';

            // Convert UTC times to company timezone
            $clockIn = $attendance->clock_in !== '00:00:00'
                ? Carbon::createFromFormat('H:i:s', $attendance->clock_in, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $clockOut = $attendance->clock_out !== '00:00:00'
                ? Carbon::createFromFormat('H:i:s', $attendance->clock_out, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            // Get company schedule
            $companySchedule = Utility::getCompanySchedule($attendance?->employee?->user?->creatorId());

            // Generate PDF filename
            $filename = 'attendance_' . $attendance->employee->id . '_' . $attendance->date . '_' . Str::random(8) . '.pdf';

            // Generate PDF
            $pdf = Pdf::loadView('pdf.attendance-detail', [
                'attendance' => $attendance,
                'clockIn' => $clockIn,
                'clockOut' => $clockOut,
                'companySchedule' => $companySchedule,
                'companyTz' => $companyTz,
            ]);

            // Store PDF in storage
            $pdfPath = 'attendance_pdfs/' . $filename;
            Storage::disk('public')->put($pdfPath, $pdf->output());

            // Generate download URL
            $downloadUrl = asset('storage/' . $pdfPath);

            return response()->json([
                'status' => true,
                'message' => 'PDF generated successfully',
                'data' => [
                    'download_url' => $downloadUrl,
                    'filename' => $filename
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed attendance information for a specific attendance record
     */
    public function getAttendanceDetail($id)
    {
        try {
            $attendance = AttendanceEmployee::with('employee')
                ->findOrFail($id);

            // Get company timezone
            $companyTz = $attendance->timezone ?? 'UTC';

            // Convert UTC times to company timezone
            $clockIn = $attendance->clock_in !== '00:00:00'
                ? Carbon::createFromFormat('H:i:s', $attendance->clock_in, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            $clockOut = $attendance->clock_out !== '00:00:00'
                ? Carbon::createFromFormat('H:i:s', $attendance->clock_out, 'UTC')
                ->setDate(
                    Carbon::parse($attendance->date)->year,
                    Carbon::parse($attendance->date)->month,
                    Carbon::parse($attendance->date)->day
                )
                ->setTimezone($companyTz)
                : null;

            // Get company schedule
            $companySchedule = Utility::getCompanySchedule($attendance->created_by);

            // Format photo URLs
            $clockInPhotoUrl = $attendance->clock_in_photo
                ? $attendance->clock_in_photo
                : null;

            $clockOutPhotoUrl = $attendance->clock_out_photo
                ? $attendance->clock_out_photo
                : null;

            $attendanceDetail = [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'employee' => [
                    'id' => $attendance->employee->id,
                    'name' => $attendance->employee->name
                ],
                'clock_in' => [
                    'time' => $clockIn ? $clockIn->format('H:i:s') : null,
                    'photo' => $clockInPhotoUrl,
                    'location' => $attendance->clock_in_location,
                    'latitude' => $attendance->clock_in_latitude,
                    'longitude' => $attendance->clock_in_longitude,
                    'notes' => $attendance->clock_in_notes
                ],
                'clock_out' => [
                    'time' => $clockOut ? $clockOut->format('H:i:s') : null,
                    'photo' => $clockOutPhotoUrl,
                    'location' => $attendance->clock_out_location,
                    'latitude' => $attendance->clock_out_latitude,
                    'longitude' => $attendance->clock_out_longitude,
                    'notes' => $attendance->clock_out_notes
                ],
                'schedule' => [
                    'start_time' => $companySchedule['company_start_time'],
                    'end_time' => $companySchedule['company_end_time']
                ],
                'status' => $attendance->status,
                'metrics' => [
                    'late' => $attendance->late,
                    'early_leaving' => $attendance->early_leaving,
                    'overtime' => $attendance->overtime,
                    'total_rest' => $attendance->total_rest
                ],
                'timezone' => $companyTz
            ];

            return response()->json([
                'status' => true,
                'message' => 'Attendance detail retrieved successfully',
                'data' => $attendanceDetail
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Attendance record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve attendance detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function attendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:4096',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->getMessageBag()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee = Auth::user()->employee;
            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee record not found.',
                ], 404);
            }

            // Get company schedule and timezone
            $companySchedule = Utility::getCompanySchedule(Auth::user()->creatorId());
            $companyTz = $companySchedule['company_timezone'];

            // Get current time in company timezone
            $currentDateTime = now()->setTimezone($companyTz);

            // Get date and time components in company timezone
            $date = $currentDateTime->format('Y-m-d');
            $timeInCompanyTz = $currentDateTime->format('H:i:s');

            // Convert time to UTC for storage
            $utcDateTime = $currentDateTime->setTimezone('UTC');
            $timeInUTC = $utcDateTime->format('H:i:s');

            // Check for existing attendance
            $existingAttendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                // ->where('clock_out', '00:00:00')
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Already clocked in today.',
                    'data' => [
                        'attendance' => $this->formatAttendanceResponse($existingAttendance, $companyTz)
                    ]
                ], 400);
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendance_photos', 'public');
            }

            // Calculate late time based on company schedule
            $late = $this->calculateLateTime(
                $date,
                $timeInCompanyTz,
                $companySchedule['company_start_time'],
                $employee->id,
                $companyTz
            );

            // Create attendance record (stored in UTC)
            $attendanceData = [
                'employee_id' => $employee->id,
                'date' => $date, // Store date in company timezone
                'status' => 'Present',
                'clock_in' => $timeInUTC, // Store time in UTC
                'clock_out' => '00:00:00',
                'late' => $late,
                'early_leaving' => '00:00:00',
                'overtime' => '00:00:00',
                'total_rest' => '00:00:00',
                'created_by' => Auth::user()->id,
                'timezone' => $companyTz,
                'clock_in_latitude' => $request->latitude,
                'clock_in_longitude' => $request->longitude,
                'clock_in_location' => $request->location,
                'clock_in_photo' => asset('storage/' . $photoPath),
                'clock_in_notes' => $request->notes
            ];

            $attendance = AttendanceEmployee::create($attendanceData);

            return response()->json([
                'status' => true,
                'message' => 'Employee successfully clocked in.',
                'data' => [
                    'attendance' => $this->formatAttendanceResponse($attendance, $companyTz),
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name
                    ],
                    'clock_in_time' => $timeInCompanyTz,
                    'late_duration' => $late,
                    'datetime_utc' => $currentDateTime->format('Y-m-d H:i:s')
                ]
            ], 201);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:4096',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->getMessageBag()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee = Auth::user()->employee;
            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get company schedule and timezone
            $companySchedule = Utility::getCompanySchedule(Auth::user()->creatorId());
            $companyTz = $companySchedule['company_timezone'];

            // Get current time in company timezone
            $currentDateTime = now()->setTimezone($companyTz);
            $date = $currentDateTime->format('Y-m-d');
            $timeInCompanyTz = $currentDateTime->format('H:i:s');

            // Convert to UTC for storage
            $utcDateTime = $currentDateTime->setTimezone('UTC');
            $timeInUTC = $utcDateTime->format('H:i:s');

            // Find existing attendance record
            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clock-in record found for this date.',
                ], 404);
            }

            if ($attendance->clock_out !== '00:00:00') {
                return response()->json([
                    'status' => false,
                    'message' => 'Already clocked out for today.',
                    'data' => [
                        'attendance' => $this->formatAttendanceResponse($attendance, $companyTz)
                    ]
                ], 400);
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendance_photos', 'public');
            }

            // Calculate early leaving and overtime in company timezone
            $earlyLeaving = $this->calculateEarlyLeaving($timeInCompanyTz, $companySchedule['company_end_time']);
            $overtime = $this->calculateOvertime($timeInCompanyTz, $companySchedule['company_end_time']);

            $attendance->update([
                'clock_out' => $timeInUTC, // Store in UTC
                'early_leaving' => $earlyLeaving,
                'overtime' => $overtime,
                'total_rest' => '00:00:00',
                'clock_out_latitude' => $request->latitude,
                'clock_out_longitude' => $request->longitude,
                'clock_out_location' => $request->location,
                'clock_out_photo' => asset('storage/' . $photoPath),
                'clock_out_notes' => $request->notes,
                'timezone' => $companyTz
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Clock out recorded successfully.',
                'data' => [
                    'attendance' => $this->formatAttendanceResponse($attendance->fresh(), $companyTz),
                    'early_leaving' => $earlyLeaving,
                    'overtime' => $overtime,
                    'datetime_utc' => $currentDateTime->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while recording clock out.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateLateTime($date, $currentTime, $startTime, $employeeId, $timezone)
    {
        // Convert times to Carbon instances in the company timezone
        $currentDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "$date $currentTime", $timezone);
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "$date $startTime", $timezone);


        $totalLateSeconds = max(0, $startDateTime->diffInSeconds($currentDateTime));

        return $this->formatDuration($totalLateSeconds);
    }

    private function calculateEarlyLeaving($clockOutTime, $endTime)
    {
        $totalSeconds = strtotime($endTime) - strtotime($clockOutTime);
        return $totalSeconds > 0 ? $this->formatDuration($totalSeconds) : '00:00:00';
    }

    private function calculateOvertime($clockOutTime, $endTime)
    {
        $totalSeconds = strtotime($clockOutTime) - strtotime($endTime);
        return $totalSeconds > 0 ? $this->formatDuration($totalSeconds) : '00:00:00';
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds / 60) % 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    private function formatAttendanceResponse($attendance, $companyTz)
    {
        // Convert UTC times to company timezone for response
        $clockIn = $attendance->clock_in !== '00:00:00'
            ? Carbon::createFromFormat('H:i:s', $attendance->clock_in, 'UTC')->setTimezone($companyTz)->format('H:i:s')
            : '00:00:00';

        $clockOut = $attendance->clock_out !== '00:00:00'
            ? Carbon::createFromFormat('H:i:s', $attendance->clock_out, 'UTC')->setTimezone($companyTz)->format('H:i:s')
            : '00:00:00';

        return [
            'id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'date' => $attendance->date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'late' => $attendance->late,
            'early_leaving' => $attendance->early_leaving,
            'overtime' => $attendance->overtime,
            'total_rest' => $attendance->total_rest,
            'status' => $attendance->status,
            'timezone' => $companyTz,
            // Include other fields as needed
        ];
    }
}
