<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'amount' => $reimbursement->amount,
            'receipt_path' => $reimbursement->receipt_path,
            'status' => $reimbursement->status,
            'transaction_date' => $reimbursement->transaction_date,
            'requested_at' => $reimbursement->requested_at,
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

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        $reimbursements = Reimbursement::where('employee_id', $employee->id)
            ->with(['category', 'approver', 'rejecter', 'payer'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reimbursement) use ($companyTz) {
                return $this->formatReimbursementResponse($reimbursement, $companyTz);
            });

        // Menghitung total reimburse yang diajukan untuk periode 1 bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalRequested = Reimbursement::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Menghitung total reimburse yang diapprove untuk periode 1 bulan ini
        $totalApproved = Reimbursement::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement retrieved successfully',
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

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Reimbursement')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:reimbursement_categories,id',
            'amount' => 'required',
            'transaction_date' => 'required',
            // 'items' => 'required|array|min:1',
            // 'items.*.description' => 'required|string',
            // 'items.*.amount' => 'required|numeric|min:0',
            // 'items.*.date' => 'required|date',
            // 'items.*.receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $employee = $user->employee;

        // Generate request number
        $requestNumber = 'REIM-' . date('Ymd') . '-' . Str::random(5);

        // Calculate total amount
        // $totalAmount = 0;
        // foreach ($request->items as $item) {
        //     $totalAmount += $item['amount'];
        // }

        // Create reimbursement
        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'request_number' => $requestNumber,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'status' => 'pending',
            'requested_at' => now(),
            'transaction_date' => $request->transaction_date,
            'category_id' => $request->category_id,
            'created_by' => $user->creatorId(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reimbursement request created successfully',
            'data' => $reimbursement->load('category')
        ], 201);
    }

    public function getCategories()
    {
        $categories = ReimbursementCategory::where('is_active', true)->get();
        return response()->json([
            'status' => true,
            'message' => 'Reimbursement Category retrieved successfully',
            'data' => $categories
        ], 200);
    }
}
