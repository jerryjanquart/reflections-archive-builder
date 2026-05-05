<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReflectionParserService
{
    public function processUrl(string $url): array
    {

        // PHASES:
        // 1. Setup
        // 2. Fetch + Normalize
        // 3. Split -> break big thing into obvious sections
        // 4. Chunk -> refine those sections into meaningful units
        // 5. Parse
        // 6. Metadata
        // 7. Export

        // --------------------------------------------------
        // SETUP: Bible maps, book names, and parser patterns
        // --------------------------------------------------

        $chapterCounts = config('bible.chapter_counts');
        $categoryMap = config('bible.category_map');
        $bookAbbrev = config('bible.book_abbrev');

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

        // --------------------------------------------------
        // FETCH + NORMALIZE: Get post HTML and convert to text
        // --------------------------------------------------

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
            // Fallback: use full HTML if no main content wrapper is found
            $contentHtml = $html;
        }

        // Preserve paragraph-ish structure BEFORE stripping tags
        $contentHtml = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $contentHtml);
        $contentHtml = preg_replace('/<\s*\/p\s*>/i', "\n\n", $contentHtml);
        $contentHtml = preg_replace('/<\s*\/div\s*>/i', "\n\n", $contentHtml);
        $contentHtml = preg_replace('/<\s*\/h[1-6]\s*>/i', "\n\n", $contentHtml);
        $contentHtml = preg_replace('/<\s*li\s*>/i', "\n- ", $contentHtml);
        $contentHtml = preg_replace('/<\s*\/li\s*>/i', "\n", $contentHtml);




        // 1. Normalize italics first
        $contentHtml = preg_replace('/<(em|i)>(.*?)<\/\1>/is', '<i>$2</i>', $contentHtml);

        // 2. Then preserve blockquotes
        $contentHtml = preg_replace_callback(
            '/<blockquote[^>]*>(.*?)<\/blockquote>/is',
            function ($matches) {
                $quote = trim($matches[1]);

                $quote = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $quote);
                $quote = preg_replace('/<\s*\/p\s*>/i', "\n\n", $quote);

                // IMPORTANT: allow <i> so italics survive
                $quote = strip_tags($quote, '<i>');

                return "\n\n[QUOTE]\n{$quote}\n[/QUOTE]\n\n";
            },
            $contentHtml
        );

        $text = html_entity_decode(strip_tags($contentHtml, '<i>'));
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = trim($text);

        // --------------------------------------------------
        // SPLIT: Break weekly post into daily sections
        // --------------------------------------------------

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

            // --------------------------------------------------
            // CHUNK: Split one day into one or more reflections
            // --------------------------------------------------

            $reflectionChunks = [];
            $currentChunk = [];

            foreach ($lines as $line) {
                $isReflectionStart =
                    preg_match($reflectionStartPattern, $line)
                    || preg_match($specialBookPattern, $line);

                if ($isReflectionStart) {
                    if (! empty($currentChunk)) {
                        $reflectionChunks[] = $currentChunk;
                    }

                    $currentChunk = [$line];
                } else {
                    $currentChunk[] = $line;
                }
            }

            if (! empty($currentChunk)) {
                $reflectionChunks[] = $currentChunk;
            }

            // --------------------------------------------------
            // PARSE: Convert reflection chunks into structured entries
            // --------------------------------------------------

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

                    $firstLineRemainder = trim($m[4] ?? '');
                } elseif (preg_match($specialBookPattern, $titleLine, $m)) {
                    $book = trim($m[1]);
                    $chapter = null;
                    $verses = null;
                    $firstLineRemainder = trim($m[2] ?? '');
                } else {

                    // ---------------------------------------
                    // TOPIC-BASED FALLBACK (no scripture)
                    // ---------------------------------------

                    $firstParagraph = implode(' ', $chunk);

                    if (preg_match('/^(.{3,100}?):\s*(.+)$/s', $firstParagraph, $matches)) {

                        $topic = trim($matches[1]);
                        $openingBody = trim($matches[2]);

                        $remainingLines = array_slice($chunk, 1);

                        $content = trim(
                            $openingBody . "\n\n" . implode("\n\n", $remainingLines)
                        );

                        // Clean content (reuse your existing cleanup logic)
                        $content = preg_replace('/Posted on .*$/s', '', $content);
                        $content = preg_replace("/\n{3,}/", "\n\n", $content);
                        $content = trim($content);

                        $parsedDate = Carbon::parse("{$dayLabel}, {$year}");
                        $dateForFilename = $parsedDate->format('m.d.Y');

                        $wordCount = str_word_count($content);

                        $filename = Str::slug($topic) . ".DR{$dateForFilename}";

                        $results[] = [
                            'day' => $dayLabel,
                            'year' => $year,
                            'scripture_reference' => null,
                            'book' => 'TOPIC',
                            'chapter' => null,
                            'verses' => null,
                            'content' => $content,
                            'word_count' => $wordCount,
                            'word_count_formatted' => number_format($wordCount),
                            'chapter_folder' => 'biblical-topics',
                            'range_folder' => 'biblical-topics',
                            'category' => 'biblical-topics',
                            'filename' => $filename,
                        ];

                        continue;
                    }

                    // If it’s not even topic-based, skip as before
                    continue;
                }

                // Normalize book title
                $book = preg_replace('/^(The\s+)?Book\s+of\s+/i', '', $book);
                $book = preg_replace('/^The\s+/i', '', $book);

                $book = preg_replace('/^First\s+/i', '1 ', $book);
                $book = preg_replace('/^Second\s+/i', '2 ', $book);
                $book = preg_replace('/^Third\s+/i', '3 ', $book);

                $bookMap = [
                    'Song of Songs' => 'Song of Solomon',
                    'Canticles' => 'Song of Solomon',
                    'Psalm' => 'Psalms',
                ];

                if (isset($bookMap[$book])) {
                    $book = $bookMap[$book];
                }

                $scriptureReference = $chapter
                    ? ($verses ? "{$book} {$chapter}:{$verses}" : "{$book} {$chapter}")
                    : "The Book of {$book}";

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

                // --------------------------------------------------
                // METADATA: Build folder, filename, and display data
                // --------------------------------------------------

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

        // --------------------------------------------------
        // EXPORT: Send parsed entries to the Word doc exporter
        // --------------------------------------------------

        $export = app(ReflectionDocxExportService::class)
            ->export($results, $url);

        return [
            'results' => $results,
            'url' => $url,
            'createdCount' => $export['createdCount'],
            'skippedCount' => $export['skippedCount'],
            'createdFiles' => $export['createdFiles'],
            'skippedFiles' => $export['skippedFiles'],
        ];
    }
}