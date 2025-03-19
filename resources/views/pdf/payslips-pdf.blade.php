{{-- <!DOCTYPE html>
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
</html> --}}

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
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background-color: #fff;
        }
        .card-content {
            padding: 16px;
        }
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .grid-container {
            display: table;
            width: 100%;
        }
        .grid-row {
            display: table-row;
        }
        .grid-cell {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }
        .grid-cell-6 {
            width: 50%;
        }
        .label {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }
        .value {
            font-size: 15px;
        }
        .capitalize {
            text-transform: capitalize;
        }
        .chip {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .chip-success {
            background-color: #4caf50;
        }
        .chip-warning {
            background-color: #ff9800;
        }
        .divider {
            height: 0.5px;
            background-color: #ddd;
            padding: 0 !important;
            margin: 0;
        }
        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .font-medium {
            font-weight: 500;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .bg-primary-lighten {
            background-color: #e3f2fd;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 0;
            text-align: left;
        }
        th {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">FURSAN-HRIS</div>
            <div class="payslip-title">PAYSLIP FOR {{ strtoupper($monthName) }} {{ $payslip->year }}</div>
            <div class="payslip-number">Ref: {{ $payslip->payslip_number }}</div>
        </div>

        <!-- Employee Information -->
        <div class="card">
            <div class="card-content">
                <div class="card-title">Employee Information</div>
                <div class="grid-container">
                    <div class="grid-row">
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Name:</div>
                            <div class="value">{{ $employee->name }}</div>
                        </div>
                        {{-- <div class="grid-cell grid-cell-6">
                            <div class="label">ID:</div>
                            <div class="value">{{ $employee->employee_id }}</div>
                        </div> --}}
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Department:</div>
                            <div class="value">{{ $employee->department->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="grid-row">
                        {{-- <div class="grid-cell grid-cell-6">
                            <div class="label">Department:</div>
                            <div class="value">{{ $employee->department->name ?? '-' }}</div>
                        </div> --}}
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Branch:</div>
                            <div class="value">{{ $employee->branch->name ?? '-' }}</div>
                        </div>
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Position:</div>
                            <div class="value">{{ $employee->designation->name ?? '-' }}</div>
                        </div>
                    </div>
                    {{-- <div class="grid-row">
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Position:</div>
                            <div class="value">{{ $employee->designation->name ?? '-' }}</div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>

        <!-- Salary Information -->
        <div class="card">
            <div class="card-content">
                <div class="card-title">Salary Information</div>
                <div class="grid-container">
                    <div class="grid-row">
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Salary Type:</div>
                            <div class="value capitalize">{{ $payslip->salary_type }}</div>
                        </div>
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Basic Salary:</div>
                            <div class="value">{{ number_format($payslip->basic_salary, 2) }}</div>
                        </div>
                    </div>
                    <div class="grid-row">
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Payment Status:</div>
                            <div class="value">
                                <span class="chip {{ $payslip->payment_status == 'paid' ? 'chip-success' : 'chip-warning' }}">
                                    {{ $payslip->payment_status }}
                                </span>
                            </div>
                        </div>
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Payment Date:</div>
                            <div class="value">
                                @if($payslip->payment_status == 'paid' && $payslip->payment_date)
                                    {{ date('d M Y', strtotime($payslip->payment_date)) }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="grid-row">
                        <div class="grid-cell grid-cell-6">
                            <div class="label">Payment Method:</div>
                            <div class="value capitalize">{{ $payslip->payment_method ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="card">
            <div class="card-content">
                <div class="card-title">Earnings</div>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="divider" colspan="2"></td>
                        </tr>
                        <tr class="py-2">
                            <td>Basic Salary</td>
                            <td class="text-right">{{ number_format($payslip->basic_salary, 2) }}</td>
                        </tr>
                        @if(count($allowances) > 0)
                            <tr>
                                <td class="divider" colspan="2"></td>
                            </tr>
                            @foreach($allowances as $allowance)
                            <tr class="py-2">
                                <td>{{ $allowance['title'] ?? 'Allowance' }}</td>
                                <td class="text-right">{{ number_format($allowance['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @endif
                        @if($payslip->total_overtime > 0)
                            <tr>
                                <td class="divider" colspan="2"></td>
                            </tr>
                            <tr class="py-2">
                                <td>Overtime</td>
                                <td class="text-right">{{ number_format($payslip->total_overtime, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="divider" colspan="2"></td>
                        </tr>
                        <tr class="py-2 font-bold">
                            <td>Total Earnings</td>
                            <td class="text-right">{{ number_format($payslip->basic_salary + $payslip->total_allowance + $payslip->total_overtime, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deductions -->
        @if(count($deductions) > 0)
        <div class="card">
            <div class="card-content">
                <div class="card-title">Deductions</div>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="divider" colspan="2"></td>
                        </tr>
                        @foreach($deductions as $deduction)
                        <tr class="py-2">
                            <td>{{ $deduction['title'] ?? 'Deduction' }}</td>
                            <td class="text-right">{{ number_format($deduction['amount'], 2) }}</td>
                        </tr>
                        @if(!$loop->last)
                        <tr>
                            <td class="divider" colspan="2"></td>
                        </tr>
                        @endif
                        @endforeach
                        <tr>
                            <td class="divider" colspan="2"></td>
                        </tr>
                        <tr class="py-2 font-bold">
                            <td>Total Deductions</td>
                            <td class="text-right">{{ number_format($payslip->total_deduction, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Net Salary -->
        <div class="card">
            <div class="card-content bg-primary-lighten">
                <table>
                    <tr class="py-2 font-bold">
                        <td><div style="font-size: 18px;">NET SALARY:</div></td>
                        <td class="text-right"><div style="font-size: 18px;">{{ number_format($payslip->net_salary, 2) }}</div></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Note if any -->
        @if($payslip->note)
        <div class="card">
            <div class="card-content">
                <div class="card-title">Note</div>
                <p>{{ $payslip->note }}</p>
            </div>
        </div>
        @endif

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Generated on {{ now()->timezone($companyTz)->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>