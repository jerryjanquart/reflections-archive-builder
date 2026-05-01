<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Process Next Reflection Sources</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            line-height: 1.5;
        }

        .item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .processed { border-left: 6px solid #2f855a; }
        .skipped { border-left: 6px solid #718096; }
        .failed { border-left: 6px solid #c53030; }

        .meta {
            color: #666;
            font-size: 14px;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <h1>Process Next Reflection Sources</h1>

    @if (empty($batchReports))
        <p>No imported sources waiting to be processed.</p>
    @else
        <p>Processed {{ count($batchReports) }} source URLs.</p>

        @foreach ($batchReports as $report)
            <div class="item {{ $report['status'] }}">
                <div class="status">{{ $report['status'] }}</div>

                <h2>{{ $report['title'] ?: 'Untitled' }}</h2>

                <p class="meta">
                    Created: {{ $report['createdCount'] }} |
                    Skipped: {{ $report['skippedCount'] }}
                </p>

                <p>
                    <a href="{{ $report['url'] }}" target="_blank">{{ $report['url'] }}</a>
                </p>

                @if ($report['error'])
                    <p><strong>Error:</strong> {{ $report['error'] }}</p>
                @endif
            </div>
        @endforeach
    @endif

    <p>
        <a href="/reflection-sources-status">Back to status</a>
    </p>

</body>
</html>