<?php


use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\ReflectionSource;
use App\Services\ReflectionParserService;


Route::get('/parse', function () {
    
    $url = 'https://www.touchstonemag.com/daily_reflections/2007/04/13/april-13-april';
    $report = app(ReflectionParserService::class)->processUrl($url);

    return view('parse', $report);

});


Route::get('/reflection-sources-status', function () {
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
});





Route::post('/process-next-reflection-sources', function () {
    $sources = ReflectionSource::where('status', 'imported')
        ->orderBy('post_date')
        ->limit(10)
        ->get();

    $batchReports = [];

    foreach ($sources as $source) {
        try {
            $report = app(ReflectionParserService::class)
                ->processUrl($source->url);

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
});




















Route::get('/import-reflection-sources', function () {
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
});

























