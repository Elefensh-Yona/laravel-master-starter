<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Workspace summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 2px; }
        .muted { color: #666; font-size: 11px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        h2 { font-size: 14px; margin: 16px 0 6px; }
    </style>
</head>
<body>
    <h1>Workspace summary</h1>
    <p class="muted">Generated {{ $generatedAt }} by {{ $actor?->name ?? 'system' }}</p>

    <h2>Counts</h2>
    <table>
        <thead>
            <tr><th>Metric</th><th>Value</th></tr>
        </thead>
        <tbody>
            @foreach ($summary['counts'] as $label => $value)
                <tr>
                    <td>{{ str($label)->headline() }}</td>
                    <td>{{ number_format((float) $value) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Recent users</h2>
    <table>
        <thead>
            <tr><th>Name</th><th>Email</th><th>Roles</th><th>Joined</th></tr>
        </thead>
        <tbody>
            @forelse ($summary['recentUsers'] as $user)
                <tr>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ implode(', ', $user['roles']) }}</td>
                    <td>{{ $user['createdAt'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No users yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent activity</h2>
    <table>
        <thead>
            <tr><th>Event</th><th>Description</th><th>When</th></tr>
        </thead>
        <tbody>
            @forelse ($summary['recentEvents'] as $event)
                <tr>
                    <td>{{ $event['event'] }}</td>
                    <td>{{ $event['description'] }}</td>
                    <td>{{ $event['createdAt'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No activity recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
