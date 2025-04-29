<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReimbursementController extends Controller
{
    private function formatReimbursementResponse($reimbursement, $companyTz)
    {
        return [
            'id' => $reimbursement->id,
            'employee_id' => $reimbursement->employee_id,
            'request_number' => $reimbursement->request_number,
            'title' => $reimbursement->title,
            'description' => $reimbursement->description,
            'amount' => ($reimbursement->amount == (int)$reimbursement->amount)
                ? (int)$reimbursement->amount
                : $reimbursement->amount,
            'receipt_path' => $reimbursement->receipt_path,
            'status' => $reimbursement->status,
            'transaction_date' => $reimbursement->transaction_date,
            'requested_at' => Utility::formatDateTimeToCompanyTz($reimbursement->requested_at, $companyTz)?->format('Y-m-d H:i:s'),
            'approved_by' => $reimbursement->approved_by,
            'approved_at' => Utility::formatDateTimeToCompanyTz($reimbursement->approved_at, $companyTz)?->format('Y-m-d H:i:s'),
            'rejected_by' => $reimbursement->rejected_by,
            'rejected_at' => Utility::formatDateTimeToCompanyTz($reimbursement->rejected_at, $companyTz)?->format('Y-m-d H:i:s'),
            'paid_by' => $reimbursement->paid_by,
            'paid_at' => Utility::formatDateTimeToCompanyTz($reimbursement->paid_at, $companyTz)?->format('Y-m-d H:i:s'),
            'payment_method' => $reimbursement->payment_method,
            'notes' => $reimbursement->notes,
            'category_id' => $reimbursement->category_id,
            'created_at' => $reimbursement->created_at,
            'created_by' => $reimbursement->created_by,
            'company' => $reimbursement->company,
            'updated_at' => $reimbursement->updated_at,
            'category' => $reimbursement->category,
            'employee' => $reimbursement->employee,
            'approver' => $reimbursement->approver,
            'rejecter' => $reimbursement->rejecter,
            'payer' => $reimbursement->payer,
        ];
    }

    public function index()
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $reimbursements = Reimbursement::with(['category', 'employee', 'approver', 'rejecter', 'payer', 'company'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reimbursement) use ($companyTz) {
                return $this->formatReimbursementResponse($reimbursement, $companyTz);
            });

        return response()->json([
            'status' => true,
            'message' => 'Reimbursements retrieved successfully',
            'data' => $reimbursements
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => 'required|exists:employees,id',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:reimbursement_categories,id',
                'amount' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
                'receipt_path' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        $employee = Employee::with('user')->find($request->employee_id);
        if (empty($employee)) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Generate request number
        $requestNumber = 'REIM-' . date('Ymd') . '-' . Str::random(5);

        // Handle file upload if present
        $receiptPath = null;
        if ($request->hasFile('receipt_path')) {
            $receipt = $request->file('receipt_path');
            $fileName = time() . '-' . $receipt->getClientOriginalName();
            $filePath = $receipt->storeAs('reimbursement_receipts', $fileName, 'public');
            $receiptPath = url('/storage/' . $filePath);
        }

        try {
            $reimbursement = Reimbursement::create([
                'employee_id' => $request->employee_id,
                'request_number' => $requestNumber,
                'title' => $request?->title ?? null,
                'description' => $request->description,
                'amount' => $request->amount,
                'receipt_path' => $receiptPath,
                'status' => 'approved',
                'transaction_date' => $request->transaction_date,
                'requested_at' => now(),
                'category_id' => $request->category_id,
                'created_by' => Auth::user()->type == 'super admin' ? $request->created_by : $employee?->user->creatorId(),
                'approved_by'   => Auth::id(),
                'approved_at'   => now(),
            ]);

            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Reimbursement request created successfully',
                'data' => $this->formatReimbursementResponse($reimbursement->load(['category', 'employee']), $companyTz)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create reimbursement request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reimbursement = Reimbursement::with(['category', 'employee', 'approver', 'rejecter', 'payer'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->find($id);

        if (!$reimbursement) {
            return response()->json([
                'status' => false,
                'message' => 'Reimbursement not found.',
            ], 404);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement retrieved successfully',
            'data' => $this->formatReimbursementResponse($reimbursement, $companyTz)
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reimbursement = Reimbursement::when(Auth::user()->type != 'super admin', function ($q) {
            $q->where('created_by', Auth::user()->creatorId());
        })
            ->find($id);

        if (!$reimbursement) {
            return response()->json([
                'status' => false,
                'message' => 'Reimbursement not found.',
            ], 404);
        }

        // Don't allow editing if already approved, rejected or paid
        if (in_array($reimbursement->status, ['approved', 'rejected', 'paid'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot edit reimbursement that has already been ' . $reimbursement->status . '.',
            ], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'employee_id' => 'required|exists:employees,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:reimbursement_categories,id',
                'amount' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
                'receipt_path' => 'nullable',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        $employee = Employee::with('user')->find($request->employee_id);
        if (empty($employee)) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Handle file upload if present
        $receiptPath = $reimbursement->receipt_path;
        if ($request->hasFile('receipt_path')) {
            $receipt = $request->file('receipt_path');
            $fileName = time() . '-' . $receipt->getClientOriginalName();
            $filePath = $receipt->storeAs('reimbursement_receipts', $fileName, 'public');
            $receiptPath = url('/storage/' . $filePath);
        }

        try {
            $reimbursement->update([
                'employee_id' => $request->employee_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'receipt_path' => $receiptPath,
                'transaction_date' => $request->transaction_date,
                'category_id' => $request->category_id,
                'status' => 'pending', // Reset to pending since details changed
                'created_by' => Auth::user()->type == 'super admin' ? $request->created_by : $employee?->user->creatorId(),
            ]);

            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

            return response()->json([
                'status' => true,
                'message' => 'Reimbursement updated successfully',
                'data' => $this->formatReimbursementResponse($reimbursement->load(['category', 'employee']), $companyTz)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update reimbursement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $reimbursement = Reimbursement::when(Auth::user()->type != 'super admin', function ($q) {
            $q->where('created_by', Auth::user()->creatorId());
        })->find($id);

        if (!$reimbursement) {
            return response()->json([
                'status' => false,
                'message' => 'Reimbursement not found.',
            ], 404);
        }

        // Don't allow deletion if already paid
        if ($reimbursement->status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete reimbursement that has already been paid.',
            ], 400);
        }

        try {
            $reimbursement->delete();

            return response()->json([
                'status' => true,
                'message' => 'Reimbursement deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete reimbursement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,paid',
            'notes' => 'required_if:status,rejected',
            // 'payment_method' => 'required_if:status,paid',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        $reimbursement = Reimbursement::when(Auth::user()->type != 'super admin', function ($q) {
            $q->where('created_by', Auth::user()->creatorId());
        })->find($id);

        if (!$reimbursement) {
            return response()->json([
                'status' => false,
                'message' => 'Reimbursement not found.',
            ], 404);
        }

        if ($request->status === 'approved' && $reimbursement->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Can only approve pending reimbursements.',
            ], 400);
        }

        if ($request->status === 'rejected' && $reimbursement->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Can only reject pending reimbursements.',
            ], 400);
        }

        try {
            switch ($request->status) {
                case 'approved':
                    $reimbursement->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'remark' => $request->remark ?? $reimbursement->remark,
                    ]);
                    break;

                case 'rejected':
                    $reimbursement->update([
                        'status' => 'rejected',
                        'rejected_by' => Auth::id(),
                        'rejected_at' => now(),
                        'remark' => $request->remark ?? $reimbursement->remark,
                    ]);
                    break;

                case 'paid':
                    $updateData = [
                        'status' => 'paid',
                        'paid_by' => Auth::id(),
                        'paid_at' => now(),
                        'payment_method' => $request?->payment_method ?? null,
                        'remark' => $request->remark ?? $reimbursement->remark,
                    ];

                    // Jika reimbursement masih pending (belum pernah diapprove),
                    // kita perlu menambahkan informasi approval
                    if ($reimbursement->status === 'pending') {
                        $updateData['approved_by'] = Auth::id();
                        $updateData['approved_at'] = now();
                    }

                    $reimbursement->update($updateData);
                    break;
            }

            $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
            $reimbursement->load(['category', 'employee', 'approver', 'rejecter', 'payer']);

            return response()->json([
                'status' => true,
                'message' => 'Reimbursement status updated successfully to ' . $request->status,
                'data' => $this->formatReimbursementResponse($reimbursement, $companyTz)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update reimbursement status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getPendingReimbursements()
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $pendingReimbursements = Reimbursement::with(['category', 'employee'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($reimbursement) use ($companyTz) {
                return $this->formatReimbursementResponse($reimbursement, $companyTz);
            });

        return response()->json([
            'status' => true,
            'message' => 'Pending reimbursements retrieved successfully',
            'data' => $pendingReimbursements
        ], 200);
    }

    public function getReimbursementsByStatus($status)
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        if (!in_array($status, ['pending', 'approved', 'rejected', 'paid'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status. Must be one of: pending, approved, rejected, paid',
            ], 400);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $reimbursements = Reimbursement::with(['category', 'employee', 'approver', 'rejecter', 'payer'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reimbursement) use ($companyTz) {
                return $this->formatReimbursementResponse($reimbursement, $companyTz);
            });

        return response()->json([
            'status' => true,
            'message' => ucfirst($status) . ' reimbursements retrieved successfully',
            'data' => $reimbursements
        ], 200);
    }

    public function getReimbursementsByEmployee($employeeId)
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $reimbursements = Reimbursement::with(['category', 'employee', 'approver', 'rejecter', 'payer'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reimbursement) use ($companyTz) {
                return $this->formatReimbursementResponse($reimbursement, $companyTz);
            });

        // Get total approved and pending for current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalRequested = Reimbursement::where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalApproved = Reimbursement::where('employee_id', $employeeId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return response()->json([
            'status' => true,
            'message' => 'Employee reimbursements retrieved successfully',
            'data' => [
                'reimbursements' => $reimbursements,
                'total_requested_month' => $totalRequested,
                'total_approved_month' => $totalApproved,
                'period' => [
                    'start_date' => $startOfMonth->format('Y-m-d'),
                    'end_date' => $endOfMonth->format('Y-m-d'),
                ],
            ]
        ], 200);
    }

    public function getReportsByDateRange(Request $request)
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ], 400);
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $reimbursements = Reimbursement::with(['category', 'employee', 'approver', 'rejecter', 'payer'])
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate summary statistics
        $totalPending = $reimbursements->where('status', 'pending')->sum('amount');
        $totalApproved = $reimbursements->where('status', 'approved')->sum('amount');
        $totalRejected = $reimbursements->where('status', 'rejected')->sum('amount');
        $totalPaid = $reimbursements->where('status', 'paid')->sum('amount');

        $formattedReimbursements = $reimbursements->map(function ($reimbursement) use ($companyTz) {
            return $this->formatReimbursementResponse($reimbursement, $companyTz);
        });

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement report generated successfully',
            'data' => [
                'reimbursements' => $formattedReimbursements,
                'summary' => [
                    'total_pending' => $totalPending,
                    'total_approved' => $totalApproved,
                    'total_rejected' => $totalRejected,
                    'total_paid' => $totalPaid,
                    'total_requests' => $reimbursements->count(),
                ],
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
            ]
        ], 200);
    }

    public function getCategories()
    {
        if (!Auth::user()->can('Manage Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $categories = ReimbursementCategory::where('is_active', true)
            ->when(Auth::user()->type != 'super admin', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            })
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement categories retrieved successfully',
            'data' => $categories
        ], 200);
    }
}
