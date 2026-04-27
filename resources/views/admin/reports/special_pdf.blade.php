<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>{{ __('reports.special_title') }}</h2>
    <table>
        <thead><tr><th>Staff</th><th>Subject</th><th>College</th><th>Dept</th><th>Semester</th><th>Overall</th></tr></thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r['staff'] }}</td>
                    <td>{{ $r['subject'] }}</td>
                    <td>{{ $r['college'] }}</td>
                    <td>{{ $r['department'] }}</td>
                    <td>{{ $r['semester'] }}</td>
                    <td>{{ $r['overall'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
