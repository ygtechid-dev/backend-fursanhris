<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\ReimbursementCategory;
use App\Models\Reimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReimbursementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get our existing employees
        $employees = Employee::take(2)->get();

        // Get some users for approvals/rejections/payments
        $users = User::take(3)->get();

        // Get reimbursement categories
        $categories = ReimbursementCategory::all();
        if ($categories->isEmpty()) {
            // If no categories exist, create some default ones
            $categories = $this->createDefaultCategories();
        }

        // Create 6 reimbursements (3 for each employee)
        $this->createPendingReimbursements($employees, $categories);
        $this->createRejectedReimbursements($employees, $categories, $users[0]);
        $this->createPaidReimbursements($employees, $categories, $users[1], $users[2]);
    }

    private function createDefaultCategories()
    {
        $categories = [
            ['name' => 'Transportation', 'description' => 'Costs related to travel and commuting', 'created_by' => 2],
            ['name' => 'Office Supplies', 'description' => 'Costs for office equipment and consumables', 'created_by' => 2],
            ['name' => 'Training', 'description' => 'Costs for courses, workshops and learning materials', 'created_by' => 2],
            ['name' => 'Meals', 'description' => 'Business meal expenses', 'created_by' => 2]
        ];

        $createdCategories = collect();

        foreach ($categories as $category) {
            $createdCategories->push(ReimbursementCategory::create($category));
        }

        return $createdCategories;
    }

    private function createPendingReimbursements($employees, $categories)
    {
        foreach ($employees as $employee) {
            Reimbursement::create([
                'employee_id' => $employee->id,
                'request_number' => 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'title' => 'Pending reimbursement for ' . $employee->name,
                'description' => 'This is a pending reimbursement request for various expenses',
                'amount' => rand(100000, 500000), // Assuming in smallest currency unit (e.g., cents/paisa)
                'receipt_path' => asset('storage/' . 'receipts/receipt-' . uniqid() . '.pdf'),
                'status' => 'pending',
                'transaction_date' => Carbon::now()->subDays(rand(1, 10)),
                'requested_at' => Carbon::now()->subDays(rand(1, 5)),
                'category_id' => $categories->random()->id,
                'notes' => 'Pending approval from finance department',
                'created_by' => $employee->user->creatorId()
            ]);
        }
    }

    private function createRejectedReimbursements($employees, $categories, $rejecter)
    {
        foreach ($employees as $employee) {
            $requestedAt = Carbon::now()->subDays(rand(15, 30));
            $rejectedAt = $requestedAt->copy()->addDays(rand(2, 5));

            Reimbursement::create([
                'employee_id' => $employee->id,
                'request_number' => 'REQ-' . date('Ymd', $requestedAt->timestamp) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'title' => 'Rejected reimbursement for ' . $employee->name,
                'description' => 'This reimbursement request was rejected due to insufficient documentation',
                'amount' => rand(50000, 200000),
                'receipt_path' => asset('storage/' . 'receipts/receipt-' . uniqid() . '.pdf'),
                'status' => 'rejected',
                'transaction_date' => $requestedAt->copy()->subDays(rand(1, 5)),
                'requested_at' => $requestedAt,
                'rejected_by' => $rejecter->id,
                'rejected_at' => $rejectedAt,
                'category_id' => $categories->random()->id,
                'notes' => 'Missing original receipt and proper documentation',
                'created_by' => $employee->user->creatorId()
            ]);
        }
    }

    private function createPaidReimbursements($employees, $categories, $approver, $payer)
    {
        foreach ($employees as $employee) {
            $requestedAt = Carbon::now()->subDays(rand(30, 60));
            $approvedAt = $requestedAt->copy()->addDays(rand(1, 3));
            $paidAt = $approvedAt->copy()->addDays(rand(2, 5));

            Reimbursement::create([
                'employee_id' => $employee->id,
                'request_number' => 'REQ-' . date('Ymd', $requestedAt->timestamp) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'title' => 'Paid reimbursement for ' . $employee->name,
                'description' => 'This reimbursement request has been approved and paid',
                'amount' => rand(200000, 1000000),
                'receipt_path' => asset('storage/' . 'receipts/receipt-' . uniqid() . '.pdf'),
                'status' => 'paid',
                'transaction_date' => $requestedAt->copy()->subDays(rand(1, 10)),
                'requested_at' => $requestedAt,
                'approved_by' => $approver->id,
                'approved_at' => $approvedAt,
                'paid_by' => $payer->id,
                'paid_at' => $paidAt,
                'payment_method' => $this->getRandomPaymentMethod(),
                'category_id' => $categories->random()->id,
                'notes' => 'Payment processed through Finance department',
                'created_by' => $employee->user->creatorId()
            ]);
        }
    }

    private function getRandomPaymentMethod()
    {
        $methods = ['bank_transfer', 'cash', 'check'];
        return $methods[array_rand($methods)];
    }
}
