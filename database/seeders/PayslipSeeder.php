<?php

namespace Database\Seeders;

use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayslipSeeder extends Seeder
{
    /**
     * Run the database seeds to generate payslips.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Generating payslips...');

        // Get current month and year
        $currentDate = Carbon::now();
        $month = $currentDate->month;
        $year = $currentDate->year;

        // Generate payslips for current month
        $this->generateMonthPayslips($month, $year);

        // Generate payslips for previous month
        $previousDate = $currentDate->copy()->subMonth();
        $this->generateMonthPayslips($previousDate->month, $previousDate->year);

        $this->command->info('Payslips generated successfully!');
    }

    /**
     * Generate payslips for a specific month and year
     *
     * @param int $month
     * @param int $year
     * @return void
     */
    private function generateMonthPayslips($month, $year)
    {
        $createdBy = 2; // Assuming admin user ID is 1, adjust as needed

        $this->command->info("Generating payslips for {$month}/{$year}...");

        // Call the static generatePayslip method from Payslip model
        $payslips = Payslip::generatePayslip($month, $year, $createdBy);

        $this->command->info("Generated " . count($payslips) . " payslips for {$month}/{$year}");

        // Optionally set some payslips as paid for demo purposes
        $this->setRandomPayslipsAsPaid($payslips);
    }

    /**
     * Set some of the generated payslips as paid for demonstration purposes
     *
     * @param array $payslips
     * @return void
     */
    private function setRandomPayslipsAsPaid($payslips)
    {
        // Mark ~70% of payslips as paid for demo purposes
        foreach ($payslips as $payslip) {
            if (rand(1, 10) <= 7) {
                $paymentMethods = ['bank_transfer', 'cash'];
                $randomPaymentMethod = $paymentMethods[array_rand($paymentMethods)];

                // Set payment date between 1-10 days ago
                $paymentDate = Carbon::now()->subDays(rand(1, 10))->format('Y-m-d');

                $payslip->updatePaymentStatus(
                    $payslip->id,
                    'paid',
                    $randomPaymentMethod,
                    $paymentDate
                );

                $this->command->info("Marked payslip #{$payslip->payslip_number} as paid");
            }
        }
    }
}
