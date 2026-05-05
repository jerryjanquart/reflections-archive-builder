<?php

namespace App\Http\Controllers;

use App\Models\ReflectionSource;
use App\Services\ReflectionParserService;
use Illuminate\Support\Facades\Http;

class ReflectionSourceController extends Controller
{
    
    public function status()
    {
        $total = ReflectionSource::count();
        $imported = ReflectionSource::where('status', 'imported')->count();
        $processed = ReflectionSource::where('status', 'processed')->count();
        $skipped = ReflectionSource::where('status', 'skipped')->count();
        $failed = ReflectionSource::where('status', 'failed')->count();
        $needsReview = ReflectionSource::where('status', 'needs_review')->count();

        $nextSources = ReflectionSource::where('status', 'imported')
            ->orderBy('post_date')
            ->limit(10)
            ->get();

        return view('reflection-sources-status', compact(
            'total',
            'imported',
            'processed',
            'skipped',
            'failed',
            'needsReview',
            'nextSources'
        ));
    }


    public function import()
    {
        $page = 1;
        $imported = 0;
        $skipped = 0;

        do {
            $response = Http::get('https://www.touchstonemag.com/daily_reflections/wp-json/wp/v2/posts', [
                'per_page' => 100,
                'orderby' => 'date',
                'order' => 'asc',
                'page' => $page,
            ]);

            if (! $response->successful()) {
                break;
            }

            $posts = $response->json();

            foreach ($posts as $post) {
                $source = ReflectionSource::firstOrCreate(
                    ['url' => $post['link']],
                    [
                        'title' => html_entity_decode(strip_tags($post['title']['rendered'] ?? '')),
                        'post_date' => $post['date'] ?? null,
                        'status' => 'imported',
                    ]
                );

                if ($source->wasRecentlyCreated) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            $page++;
        } while (! empty($posts));

        return "Import complete. Imported: {$imported}. Skipped existing: {$skipped}.";
    }


    public function processNext(ReflectionParserService $parser)
    {
        $sources = ReflectionSource::where('status', 'imported')
            ->orderBy('post_date')
            ->limit(10)
            ->get();

        $batchReports = [];

        foreach ($sources as $source) {
            try {
                $report = $parser->processUrl($source->url);

                $createdCount = $report['createdCount'] ?? 0;
                $skippedCount = $report['skippedCount'] ?? 0;

                $source->update([
                    'status' => $createdCount > 0 ? 'processed' : 'skipped',
                    'files_created' => $createdCount,
                    'error_message' => null,
                    'processed_at' => now(),
                ]);

                $batchReports[] = [
                    'url' => $source->url,
                    'title' => $source->title,
                    'status' => $createdCount > 0 ? 'processed' : 'skipped',
                    'createdCount' => $createdCount,
                    'skippedCount' => $skippedCount,
                    'createdFiles' => $report['createdFiles'] ?? [],
                    'skippedFiles' => $report['skippedFiles'] ?? [],
                    'results' => $report['results'] ?? [],
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                $source->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'processed_at' => now(),
                ]);

                $batchReports[] = [
                    'url' => $source->url,
                    'title' => $source->title,
                    'status' => 'failed',
                    'createdCount' => 0,
                    'skippedCount' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return view('process-next-reflection-sources', compact('batchReports'));
    }


}