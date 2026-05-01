<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reflection Sources Status</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            max-width: 900px;
            margin: 40px auto;
            line-height: 1.5;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 16px;
            border-radius: 8px;
        }

        .number {
            font-size: 28px;
            font-weight: bold;
        }

        li {
            margin-bottom: 14px;
        }

        .date {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <h1>Reflection Sources Status</h1>

    <div class="stats">
        <div class="card">
            <div class="number">{{ $total }}</div>
            <div>Total Sources</div>
        </div>

        <div class="card">
            <div class="number">{{ $imported }}</div>
            <div>Imported / Waiting</div>
        </div>

        <div class="card">
            <div class="number">{{ $processed }}</div>
            <div>Processed</div>
        </div>

        <div class="card">
            <div class="number">{{ $skipped }}</div>
            <div>Skipped</div>
        </div>

        <div class="card">
            <div class="number">{{ $failed }}</div>
            <div>Failed</div>
        </div>

        <div class="card">
            <div class="number">{{ $needsReview }}</div>
            <div>Needs Review</div>
        </div>
    </div>

    <hr>

    <h2>Next 10 URLs to Process</h2>

    @if ($nextSources->isEmpty())
        <p>No imported sources waiting to be processed.</p>
    @else
        <ol>
            @foreach ($nextSources as $source)
                <li>
                    <strong>{{ $source->title ?: 'Untitled' }}</strong><br>
                    <span class="date">{{ $source->post_date }}</span><br>
                    <a href="{{ $source->url }}" target="_blank">{{ $source->url }}</a>
                </li>
            @endforeach
        </ol>
    @endif

</body>
</html>