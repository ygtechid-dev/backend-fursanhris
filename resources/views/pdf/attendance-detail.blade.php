<!-- resources/views/pdf/attendance-detail.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Detail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 5px;
            margin-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .metrics {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .photo-container {
            margin: 10px 0;
            text-align: center;
        }
        .photo {
            max-width: 300px;
            max-height: 200px;
            margin: 10px auto;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Detail Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Employee Information</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Name:</span>
                <span class="value">{{ $attendance->employee->name }}</span>
            </div>
            <div class="info-item">
                <span class="label">Employee ID:</span>
                <span class="value">{{ $attendance->employee->id }}</span>
            </div>
            <div class="info-item">
                <span class="label">Date:</span>
                <span class="value">{{ $attendance->date }}</span>
            </div>
            <div class="info-item">
                <span class="label">Timezone:</span>
                <span class="value">{{ $companyTz }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Clock In Details</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Time:</span>
                <span class="value">{{ $clockIn ? $clockIn->format('H:i:s') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Location:</span>
                <span class="value">{{ $attendance->clock_in_location ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Coordinates:</span>
                <span class="value">
                    {{ $attendance->clock_in_latitude }}, {{ $attendance->clock_in_longitude }}
                </span>
            </div>
        </div>
        @if($attendance->clock_in_photo)
        <div class="photo-container">
            <img class="photo" src="{{ storage_path('app/public/' . $attendance->clock_in_photo) }}" alt="Clock In Photo">
        </div>
        @endif
        @if($attendance->clock_in_notes)
        <div class="info-item">
            <span class="label">Notes:</span>
            <span class="value">{{ $attendance->clock_in_notes }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Clock Out Details</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Time:</span>
                <span class="value">{{ $clockOut ? $clockOut->format('H:i:s') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Location:</span>
                <span class="value">{{ $attendance->clock_out_location ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="label">Coordinates:</span>
                <span class="value">
                    {{ $attendance->clock_out_latitude ?? 'N/A' }}, {{ $attendance->clock_out_longitude ?? 'N/A' }}
                </span>
            </div>
        </div>
        @if($attendance->clock_out_photo)
        <div class="photo-container">
            <img class="photo" src="{{ storage_path('app/public/' . $attendance->clock_out_photo) }}" alt="Clock Out Photo">
        </div>
        @endif
        @if($attendance->clock_out_notes)
        <div class="info-item">
            <span class="label">Notes:</span>
            <span class="value">{{ $attendance->clock_out_notes }}</span>
        </div>
        @endif
    </div>

    <div class="section metrics">
        <div class="section-title">Attendance Metrics</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Status:</span>
                <span class="value">{{ $attendance->status }}</span>
            </div>
            <div class="info-item">
                <span class="label">Late Duration:</span>
                <span class="value">{{ $attendance->late }}</span>
            </div>
            <div class="info-item">
                <span class="label">Early Leaving:</span>
                <span class="value">{{ $attendance->early_leaving }}</span>
            </div>
            <div class="info-item">
                <span class="label">Overtime:</span>
                <span class="value">{{ $attendance->overtime }}</span>
            </div>
            <div class="info-item">
                <span class="label">Total Rest:</span>
                <span class="value">{{ $attendance->total_rest }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a system-generated report. Generated at {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>