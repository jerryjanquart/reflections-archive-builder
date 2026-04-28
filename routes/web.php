<?php

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

Route::get('/parse', function () {

    $url = 'https://www.touchstonemag.com/daily_reflections/2007/04/13/april-13-april';
        // https://www.touchstonemag.com/daily_reflections/2007/04/13/april-13-april/
        // https://www.touchstonemag.com/daily_reflections/2007/04/20/april-20-april/
        // https://www.touchstonemag.com/daily_reflections/2007/04/27/april-27-may-4/
        // https://www.touchstonemag.com/daily_reflections/2007/10/22/october-19th/

    $chapterCounts = [
        'Matthew' => 28,
        'Mark' => 16,
        'Luke' => 24,
        'John' => 21,
        'Acts' => 28,
        'Romans' => 16,
        '1 Corinthians' => 16,
        '2 Corinthians' => 13,
        'Galatians' => 6,
        'Ephesians' => 6,
        'Philippians' => 4,
        'Colossians' => 4,
        '1 Thessalonians' => 5,
        '2 Thessalonians' => 3,
        '1 Timothy' => 6,
        '2 Timothy' => 4,
        'Titus' => 3,
        'Philemon' => 1,
        'Hebrews' => 13,
        'James' => 5,
        '1 Peter' => 5,
        '2 Peter' => 3,
        '1 John' => 5,
        '2 John' => 1,
        '3 John' => 1,
        'Jude' => 1,
        'Revelation' => 22,

        // Add OT as needed later
        '2 Chronicles' => 36,
        'Psalms' => 150,
        'Psalm' => 150,
        'Song of Solomon' => 8,
        'The Song of Solomon' => 8,
        'Exodus' => 40,
    ];

    $categoryMap = [
        // Gospels
        'Matthew' => 'Gospels',
        'Mark' => 'Gospels',
        'Luke' => 'Gospels',
        'John' => 'Gospels',

        // Psalms
        'Psalm' => 'Psalms',
        'Psalms' => 'Psalms',

        // New Testament w/o Gospels
        'Acts' => 'New Testament w/o Gospels',
        'Romans' => 'New Testament w/o Gospels',
        '1 Corinthians' => 'New Testament w/o Gospels',
        '2 Corinthians' => 'New Testament w/o Gospels',
        'Galatians' => 'New Testament w/o Gospels',
        'Ephesians' => 'New Testament w/o Gospels',
        'Philippians' => 'New Testament w/o Gospels',
        'Colossians' => 'New Testament w/o Gospels',
        '1 Thessalonians' => 'New Testament w/o Gospels',
        '2 Thessalonians' => 'New Testament w/o Gospels',
        '1 Timothy' => 'New Testament w/o Gospels',
        '2 Timothy' => 'New Testament w/o Gospels',
        'Titus' => 'New Testament w/o Gospels',
        'Philemon' => 'New Testament w/o Gospels',
        'Hebrews' => 'New Testament w/o Gospels',
        'James' => 'New Testament w/o Gospels',
        '1 Peter' => 'New Testament w/o Gospels',
        '2 Peter' => 'New Testament w/o Gospels',
        '1 John' => 'New Testament w/o Gospels',
        '2 John' => 'New Testament w/o Gospels',
        '3 John' => 'New Testament w/o Gospels',
        'Jude' => 'New Testament w/o Gospels',
        'Revelation' => 'New Testament w/o Gospels',

        // Old Testament w/o Psalms
        'Genesis' => 'Old Testament--w/o Psalms',
        'Exodus' => 'Old Testament--w/o Psalms',
        'Leviticus' => 'Old Testament--w/o Psalms',
        'Numbers' => 'Old Testament--w/o Psalms',
        'Deuteronomy' => 'Old Testament--w/o Psalms',
        'Joshua' => 'Old Testament--w/o Psalms',
        'Judges' => 'Old Testament--w/o Psalms',
        'Ruth' => 'Old Testament--w/o Psalms',
        '1 Samuel' => 'Old Testament--w/o Psalms',
        '2 Samuel' => 'Old Testament--w/o Psalms',
        '1 Kings' => 'Old Testament--w/o Psalms',
        '2 Kings' => 'Old Testament--w/o Psalms',
        '1 Chronicles' => 'Old Testament--w/o Psalms',
        '2 Chronicles' => 'Old Testament--w/o Psalms',
        'Ezra' => 'Old Testament--w/o Psalms',
        'Nehemiah' => 'Old Testament--w/o Psalms',
        'Esther' => 'Old Testament--w/o Psalms',
        'Job' => 'Old Testament--w/o Psalms',
        'Proverbs' => 'Old Testament--w/o Psalms',
        'Ecclesiastes' => 'Old Testament--w/o Psalms',
        'Song of Solomon' => 'Old Testament--w/o Psalms',
        'The Song of Solomon' => 'Old Testament--w/o Psalms',
        'Isaiah' => 'Old Testament--w/o Psalms',
        'Jeremiah' => 'Old Testament--w/o Psalms',
        'Lamentations' => 'Old Testament--w/o Psalms',
        'Ezekiel' => 'Old Testament--w/o Psalms',
        'Daniel' => 'Old Testament--w/o Psalms',
        'Hosea' => 'Old Testament--w/o Psalms',
        'Joel' => 'Old Testament--w/o Psalms',
        'Amos' => 'Old Testament--w/o Psalms',
        'Obadiah' => 'Old Testament--w/o Psalms',
        'Jonah' => 'Old Testament--w/o Psalms',
        'Micah' => 'Old Testament--w/o Psalms',
        'Nahum' => 'Old Testament--w/o Psalms',
        'Habakkuk' => 'Old Testament--w/o Psalms',
        'Zephaniah' => 'Old Testament--w/o Psalms',
        'Haggai' => 'Old Testament--w/o Psalms',
        'Zechariah' => 'Old Testament--w/o Psalms',
        'Malachi' => 'Old Testament--w/o Psalms',
    ];

    $bookAbbrev = [
        'Matthew' => 'Mt',
        'Mark' => 'Mk',
        'Luke' => 'Lk',
        'John' => 'Jn',
        'Acts' => 'Acts',
        'Romans' => 'Rom',
        'Galatians' => 'Gal',
        'Ephesians' => 'Eph',
        'Philippians' => 'Phil',
        'Colossians' => 'Col',
        'Psalm' => 'Ps',
        'Psalms' => 'Ps',
        'Song of Solomon' => 'Sg',
        'The Song of Solomon' => 'Sg',
        // expand as needed
    ];

    $bookNames = array_unique(array_merge(
    array_keys($chapterCounts),
    array_keys($categoryMap),
        ['Psalm', 'Psalms', 'Song of Solomon']
    ));

    // Longest first so "Song of Solomon" matches before "Song"
    usort($bookNames, fn($a, $b) => strlen($b) <=> strlen($a));

    $bookPattern = implode('|', array_map(fn($book) => preg_quote($book, '/'), $bookNames));

    $reflectionStartPattern = '/^(' . $bookPattern . ')\s+(\d+)(?::([\d\-–]+))?(?:\s*\([^)]*\))?(?::\s*(.*))?$/';

    $specialBookPattern = '/^The Book of ([A-Za-z]+(?:\s+[A-Za-z]+)*):\s*(.*)$/';

    preg_match('/daily_reflections\/(\d{4})\//', $url, $yearMatch);
    $year = $yearMatch[1] ?? null;

    $html = Http::get($url)->body();

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Try to grab the main article content only
    $nodes = $xpath->query("//*[contains(@class, 'entry-content') or contains(@class, 'post-content') or contains(@class, 'td-post-content')]");

    $contentHtml = '';

    if ($nodes->length > 0) {
        $node = $nodes->item(0);

        foreach ($node->childNodes as $child) {
            $contentHtml .= $dom->saveHTML($child);
        }
    } else {
        // fallback: use full html if no main content wrapper is found
        $contentHtml = $html;
    }

    // Preserve paragraph-ish structure BEFORE stripping tags
    $contentHtml = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $contentHtml);
    $contentHtml = preg_replace('/<\s*\/p\s*>/i', "\n\n", $contentHtml);
    $contentHtml = preg_replace('/<\s*\/div\s*>/i', "\n\n", $contentHtml);
    $contentHtml = preg_replace('/<\s*\/h[1-6]\s*>/i', "\n\n", $contentHtml);
    $contentHtml = preg_replace('/<\s*li\s*>/i', "\n- ", $contentHtml);
    $contentHtml = preg_replace('/<\s*\/li\s*>/i', "\n", $contentHtml);

    // Preserve italics before stripping tags
    $contentHtml = preg_replace('/<(em|i)>(.*?)<\/\1>/is', '<i>$2</i>', $contentHtml);
    
    $text = html_entity_decode(strip_tags($contentHtml, '<i>'));
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    $text = trim($text);

    preg_match_all(
        '/((Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),\s+[A-Za-z]+\s+\d+)(.*?)(?=(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),\s+[A-Za-z]+\s+\d+|$)/s',
        $text,
        $matches,
        PREG_SET_ORDER
    );

    $results = [];

    foreach ($matches as $match) {
        $dayLabel = trim($match[1]);
        $body = trim($match[3]);

        $lines = preg_split('/\R+/', $body);
        $lines = array_values(array_filter(array_map('trim', $lines)));

        if (count($lines) === 0) {
            continue;
        }

        // Split one day into one or more reflection chunks
        $reflectionChunks = [];
        $currentChunk = [];

        foreach ($lines as $line) {
            $isReflectionStart =
                preg_match($reflectionStartPattern, $line)
                || preg_match($specialBookPattern, $line);

            if ($isReflectionStart) {
                if (!empty($currentChunk)) {
                    $reflectionChunks[] = $currentChunk;
                }
                $currentChunk = [$line];
            } else {
                $currentChunk[] = $line;
            }
        }

        if (!empty($currentChunk)) {
            $reflectionChunks[] = $currentChunk;
        }

        // Process each reflection chunk separately
        foreach ($reflectionChunks as $chunk) {
            if (count($chunk) === 0) {
                continue;
            }

            $titleLine = $chunk[0];

            $book = null;
            $chapter = null;
            $verses = null;
            $scriptureReference = null;
            $firstLineRemainder = '';

            if (preg_match($reflectionStartPattern, $titleLine, $m)) {
                $book = trim($m[1]);
                $chapter = (int) $m[2];
                $verses = $m[3] ?? null;

                $scriptureReference = $verses
                    ? "{$book} {$chapter}:{$verses}"
                    : "{$book} {$chapter}";

                $firstLineRemainder = trim($m[4] ?? '');
            } elseif (preg_match($specialBookPattern, $titleLine, $m)) {
                $book = trim($m[1]);
                $chapter = null;
                $verses = null;
                $scriptureReference = "The Book of {$book}";
                $firstLineRemainder = trim($m[2] ?? '');
            } else {
                continue;
            }

            $contentLines = $chunk;
            array_shift($contentLines);

            $content = trim(
                ($firstLineRemainder ? $firstLineRemainder . "\n\n" : '') .
                implode("\n\n", $contentLines)
            );

            // Remove duplicate leading verse range, e.g. "1-17 The Christian's new state..."
            $content = preg_replace('/^\d+[\-–]?\d*\s+/', '', $content);

            // Remove WordPress footer junk
            $content = preg_replace('/Posted on .*$/s', '', $content);

            // Normalize excessive line breaks again after cleanup
            $content = preg_replace("/\n{3,}/", "\n\n", $content);
            $content = trim($content);

            $rangeFolder = null;

            if (isset($chapterCounts[$book])) {
                $lastChapter = $chapterCounts[$book];
                $rangeFolder = "{$book} 1–{$lastChapter}";
            }

            $category = $categoryMap[$book] ?? null;

            $parsedDate = Carbon::parse("{$dayLabel}, {$year}");
            $dateForFilename = $parsedDate->format('m.d.Y');

            $wordCount = str_word_count($content);

            $abbrev = $bookAbbrev[$book] ?? $book;
            $versesPart = $verses ?: '0';

            $filename = "{$abbrev}{$chapter}.{$versesPart}.{$wordCount}.DR{$dateForFilename}";

            $results[] = [
                'day' => $dayLabel,
                'year' => $year,
                'scripture_reference' => $scriptureReference,
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
                'content' => $content,
                'word_count' => $wordCount,
                'word_count_formatted' => number_format($wordCount),
                'chapter_folder' => $book . ' ' . $chapter,
                'range_folder' => $rangeFolder,
                'category' => $category,
                'filename' => $filename,
            ];
        }
    }

    // BEGIN FILE OUTPUT

    // add code for main folder generation

    $outputBasePath = storage_path('app/ddg-output');

    if (! is_dir($outputBasePath)) {
        mkdir($outputBasePath, 0755, true);
    }

    // generate the sub folders

    $sanitize = function ($value) {
        return str_replace(['/', '\\'], '-', $value);
    };

    $addFormattedParagraph = function ($section, $text) {
    $textRun = $section->addTextRun();

    $parts = preg_split('/(<i>.*?<\/i>)/is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match('/^<i>(.*?)<\/i>$/is', $part, $m)) {
                $textRun->addText(strip_tags($m[1]), ['italic' => true]);
            } else {
                $textRun->addText(strip_tags($part));
            }
        }
    };

    $createdCount = 0;
    $skippedCount = 0;

    $createdFiles = [];
    $skippedFiles = [];

    foreach ($results as $entry) {
        $folderPath = $outputBasePath
        . DIRECTORY_SEPARATOR . $sanitize($entry['category'])
        . DIRECTORY_SEPARATOR . $sanitize($entry['range_folder'])
        . DIRECTORY_SEPARATOR . $sanitize($entry['chapter_folder']);

        if (! is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // generate the wod /**
        
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title (scripture reference)
        $section->addText(
            $entry['scripture_reference'],
            ['bold' => true, 'size' => 14]
        );

        // Date line
        $section->addText('Posted at Daily Reflections on ' . $entry['day'] . ', ' . $entry['year']);

        // Word count
        $section->addText('Word Count: ' . $entry['word_count_formatted']);

        // Body

        $paragraphs = preg_split("/\n\s*\n/", $entry['content']);

        foreach ($paragraphs as $p) {
            $addFormattedParagraph($section, $p);
        }

        // Add spacing
        $section->addTextBreak(1);

        // Source URL
        $section->addLink(
            $url,
            $url,
            ['italic' => true, 'size' => 10, 'color' => '0000FF']
        );

        // Generated line
        $section->addText(
            'File generated by the REFLECTIONS ARCHIVE BUILDER on ' . now()->format('F j, Y') . '.',
            ['size' => 10, 'color' => '666666']
        );

        $filePath = $folderPath
        . DIRECTORY_SEPARATOR
        . $entry['filename'] . '.docx';

        // Skip if file already exists and add to counter
if (file_exists($filePath)) {
    $skippedCount++;

    $skippedFiles[] = [
        'title' => $entry['scripture_reference'],
        'filename' => $entry['filename'] . '.docx',
        'path' => $filePath,
        'reason' => 'already exists',
    ];

        continue;
    }

    // Save file
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($filePath);
    $createdCount++;

    $createdFiles[] = [
        'title' => $entry['scripture_reference'],
        'filename' => $entry['filename'] . '.docx',
        'path' => $filePath,
    ];

    
    }

    return view('parse', compact(
        'results',
        'url',
        'createdCount',
        'skippedCount',
        'createdFiles',
        'skippedFiles'
    ));

});