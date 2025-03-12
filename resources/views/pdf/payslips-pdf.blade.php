<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip->payslip_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payslip-title {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .payslip-number {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            flex: 1;
            font-weight: bold;
        }
        .info-value {
            flex: 2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .net-salary {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ config('app.name', 'Company Name') }}</div>
            <div class="payslip-title">PAYSLIP FOR {{ strtoupper($monthName) }} {{ $payslip->year }}</div>
            <div class="payslip-number">Ref: {{ $payslip->payslip_number }}</div>
        </div>

        <div class="section">
            <div class="section-title">Employee Information</div>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $employee->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">ID:</div>
                <div class="info-value">{{ $employee->employee_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department:</div>
                <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Branch:</div>
                <div class="info-value">{{ $employee->branch->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Position:</div>
                <div class="info-value">{{ $employee->designation->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Salary Information</div>
            <div class="info-row">
                <div class="info-label">Salary Type:</div>
                <div class="info-value">{{ ucfirst($payslip->salary_type) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Basic Salary:</div>
                <div class="info-value">{{ number_format($payslip->basic_salary, 2) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Status:</div>
                <div class="info-value">{{ ucfirst($payslip->payment_status) }}</div>
            </div>
            @if($payslip->payment_status == 'paid')
            <div class="info-row">
                <div class="info-label">Payment Date:</div>
                <div class="info-value">{{ date('d M Y', strtotime($payslip->payment_date)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Method:</div>
                <div class="info-value">{{ \App\Models\Utility::formatText($payslip->payment_method) }}</div>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Earnings</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td style="text-align: right;">{{ number_format($payslip->basic_salary, 2) }}</td>
                    </tr>
                    @if(count($allowances) > 0)
                        @foreach($allowances as $allowance)
                        <tr>
                            <td>{{ $allowance['title'] ?? 'Allowance' }}</td>
                            <td style="text-align: right;">{{ number_format($allowance['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                    @if($payslip->total_overtime > 0)
                    <tr>
                        <td>Overtime</td>
                        <td style="text-align: right;">{{ number_format($payslip->total_overtime, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total Earnings</td>
                        <td style="text-align: right;">{{ number_format($payslip->basic_salary + $payslip->total_allowance + $payslip->total_overtime, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Deductions</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($deductions) > 0)
                        @foreach($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction['title'] ?? 'Deduction' }}</td>
                            <td style="text-align: right;">{{ number_format($deduction['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" style="text-align: center;">No deductions</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td style="text-align: right;">{{ number_format($payslip->total_deduction, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="net-salary">
            NET SALARY: {{ number_format($payslip->net_salary, 2) }}
        </div>

        @if($payslip->note)
        <div class="section">
            <div class="section-title">Note</div>
            <p>{{ $payslip->note }}</p>
        </div>
        @endif

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Generated on {{ now()->timezone($companyTz)->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>