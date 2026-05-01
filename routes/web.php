<?php


use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\ReflectionSource;
use App\Services\ReflectionParserService;
use App\Http\Controllers\ReflectionSourceController;


Route::get('/parse', function () {
    
    $url = 'https://www.touchstonemag.com/daily_reflections/2007/04/13/april-13-april';
    $report = app(ReflectionParserService::class)->processUrl($url);

    return view('parse', $report);

});

Route::get('/reflection-sources-status', [ReflectionSourceController::class, 'status']);

Route::post('/process-next-reflection-sources', [ReflectionSourceController::class, 'processNext']);

Route::get('/import-reflection-sources', [ReflectionSourceController::class, 'import']);
























