<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #1a56db;
        }
        .meta {
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #666;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .card {
            display: table-cell;
            width: 33%;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #eee;
            text-align: center;
        }
        .card-title {
            font-size: 0.8em;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
        .card-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #1a56db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #444;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 0.8em;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Padel Reservation Report</h1>
        <p>{{ $title }}</p>
    </div>

    <div class="meta">
        Generated on: {{ $generated_at }}
    </div>

    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Total Revenue</div>
            <div class="card-value">Rp {{ number_format($total_revenue, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-title">Total Reservations</div>
            <div class="card-value">{{ $total_reservations }}</div>
        </div>
        <div class="card">
            <div class="card-title">Total Hours</div>
            <div class="card-value">{{ $total_hours }}</div>
        </div>
    </div>

    <h3>Court Performance</h3>
    <table>
        <thead>
            <tr>
                <th>Court Name</th>
                <th class="text-right">Reservations</th>
                <th class="text-right">Hours</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($court_stats as $courtName => $stats)
            <tr>
                <td>{{ $courtName }}</td>
                <td class="text-right">{{ $stats['count'] }}</td>
                <td class="text-right">{{ $stats['hours'] }}</td>
                <td class="text-right">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Detailed Reservations</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Court</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
            <tr>
                <td>{{ $reservation->start_time->format('d M Y') }}</td>
                <td>{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</td>
                <td>{{ $reservation->padelCourt->name ?? 'Unknown' }}</td>
                <td class="text-right">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} Padel Reservation System. All rights reserved.
    </div>
</body>
</html>
