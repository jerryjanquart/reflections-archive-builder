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


// ONLY NEEDED TO DO ONCE
// Route::get('/import-reflection-sources', [ReflectionSourceController::class, 'import']);
























