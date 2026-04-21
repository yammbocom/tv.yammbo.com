<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\StremioAddonController;
use Illuminate\Support\Facades\Route;
use Wave\Facades\Wave;

// Stremio addon endpoints (mounted at root so manifest URL is https://tv.yammbo.com/manifest.json)
Route::get('/manifest.json', [StremioAddonController::class, 'manifest']);
Route::get('/catalog/{type}/{id}.json', [StremioAddonController::class, 'catalog']);
Route::get('/catalog/{type}/{id}/{extra}.json', [StremioAddonController::class, 'catalog']);
Route::get('/meta/{type}/{id}.json', [StremioAddonController::class, 'meta']);
Route::get('/stream/{type}/{id}.json', [StremioAddonController::class, 'stream']);

// Stremio SPA gated behind Wave auth — .htaccess rewrites /app and /app/ here.
// Deeper asset paths (/app/<hash>/...) are served directly by Apache.
Route::get('/app', function () {
    return response()->file(public_path('app/index.html'), ['Content-Type' => 'text/html']);
})->middleware('auth');

// Wave routes
Wave::routes();
