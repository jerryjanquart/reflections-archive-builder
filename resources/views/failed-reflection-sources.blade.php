<h1>Failed Reflection Sources</h1>

<p>
    <a href="/">Back to Status</a>
</p>

@if ($failedSources->isEmpty())
    <p>No failed sources. Nice.</p>
@else
    @foreach ($failedSources as $source)
        <div style="border:1px solid #ddd; border-radius:10px; padding:20px; margin-bottom:20px;">
            <h2>{{ $source->title }}</h2>

            <p>
                <strong>ID:</strong> {{ $source->id }}<br>
                <strong>Date:</strong> {{ $source->post_date }}<br>
                <strong>Files Created:</strong> {{ $source->files_created }}
            </p>

            <p>
                <a href="{{ $source->url }}" target="_blank">{{ $source->url }}</a>
                |
                <a href="{{ url('/preview-reflection?url=' . urlencode($source->url)) }}" target="_blank">
                    🔍 View Parsed Output
                </a>
            </p>

            @if ($source->error_message)
                <pre style="white-space: pre-wrap; background:#f7f7f7; padding:12px;">{{ $source->error_message }}</pre>
            @endif
        </div>
    @endforeach
@endif