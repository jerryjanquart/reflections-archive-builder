<!DOCTYPE html>
<html>
<head>
    <title>Reflections Finder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #faf9f7;
            color: black;
            max-width: 800px;
            margin: 40px auto;
            line-height: 1.5;
            font-size: 17px;
        }

        .entry {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .scripture {
            font-weight: bold;
            font-size: 18px;
        }

        .day, .meta {
            color: black;
            font-size: 17px;
            margin-top: 20px;
            margin-bottom: 6px;
        }

        .preview {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .source-url {
            margin-bottom: 25px;
            font-size: 17px;
            color: black;
       }

    .source-url a {
        color: black;
        text-decoration: underline;
    }

    h2 {
        margin-bottom: 0px;
    }

    .structure p {
        margin-bottom: -6px;
        font-size: 14px;
        text-decoration: underline;
    }

    </style>
</head>
<body>

    <h1>Parsing the Reflections (Prototype)</h1>

    <p><strong>Files Created:</strong> {{ $createdCount }}</p>
    <p><strong>Files Skipped:</strong> {{ $skippedCount }}</p>

    @php
        $grouped = collect($results)->groupBy('scripture_reference');
    @endphp

    @foreach ($grouped as $scripture => $entries)
        <h2>{{ $scripture }}</h2>

        @foreach ($entries as $entry)
            <div class="entry">

                <div class="source-url">
                    <a href="{{ $url }}" target="_blank">Source: Daily Reflections—{{ $entry['day'] }}, {{ $entry['year'] }}</a><br />
                    Words: {{ $entry['word_count_formatted'] }}
                </div>

                <div class="content">
                    {!! nl2br($entry['content']) !!}
                </div>

                <!-- Biblical / Content Metadata (what it is) -->
                
                <div class="structure"><p>Biblical / Content Metadata</p></div>
                <div class="meta-folder" style="margin-top: 0.5rem; color: black;">
                    <strong>Book:</strong> {{ $entry['book'] }}; <strong>Chapter:</strong> {{ $entry['chapter'] }}@if(!empty($entry['verses'])); <strong>Verses: </strong>{{ $entry['verses'] }}@endif; <strong>Scripture Reference:</strong> {{ $entry['scripture_reference'] }}; <strong>Words:</strong> {{ $entry['word_count_formatted'] }}
                </div>

                 <div class="structure"><p>Archive / Folder Metadata</p></div>

                <!-- Archive / Folder Metadata (where it goes) -->
                
                <div class="meta-folder" style="margin-top: 0.5rem; color: black;">
                    <strong>Archive Path:</strong> {{ $entry['category'] }} / {{ $entry['range_folder'] }} / {{ $entry['chapter_folder'] }}<br />
                    <strong>Filename:</strong> {{ $entry['filename'] }}
                </div>

            </div>
        @endforeach
    @endforeach

</body>
</html>