<?php


use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\ReflectionSource;
use App\Services\ReflectionParserService;
use App\Http\Controllers\ReflectionSourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReflectionSourceController::class, 'status']);

Route::get('/parse', function () {
    
    $source = ReflectionSource::where('status', 'imported')
        ->orderBy('post_date')
        ->first();

    if (! $source) {
        return 'No more imported sources.';
    }

    $url = $source->url;

    $report = app(ReflectionParserService::class)
        ->processUrl($url);

    return view('parse', $report);

});

Route::get('/parse-url', function () {

    $url = 'https://www.touchstonemag.com/daily_reflections/2009/01/30/january-30-february-6/';

    $report = app(ReflectionParserService::class)
        ->processUrl($url);

    return view('parse', $report);

});

Route::get('/preview-reflection', function () {

    $url = request('url');

    if (! $url) {
        return 'No URL provided.';
    }

    $report = app(ReflectionParserService::class)
        ->processUrl($url);

    return view('parse', $report);

});

Route::post('/process-next-reflection-sources', [ReflectionSourceController::class, 'processNext']);

Route::get('/failed-reflection-sources', function () {

    $failedSources = ReflectionSource::where('status', 'failed')
        ->orderBy('post_date')
        ->get();

    return view('failed-reflection-sources', [
        'failedSources' => $failedSources,
    ]);

});


Route::get('/skipped-reflection-sources', function () {

    $skippedSources = ReflectionSource::where('status', 'skipped')
        ->orderBy('post_date')
        ->get();

    return view('skipped-reflection-sources', [
        'skippedSources' => $skippedSources,
    ]);

});


/*
Route::get('/reset-reflection-sources', function () {
    \App\Models\ReflectionSource::whereIn('status', [
        'processed',
        'skipped',
        'failed',
        'needs_review',
    ])->update([
        'status' => 'imported',
        'files_created' => 0,
        'error_message' => null,
        'processed_at' => null,
    ]);

    return 'Reflection sources reset.';
});
*/

// ONLY NEEDED TO DO ONCE
// Route::get('/import-reflection-sources', [ReflectionSourceController::class, 'import']);
























