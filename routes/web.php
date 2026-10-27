<?php

use App\Http\Controllers\PaperController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('papers.create');
});

Route::resource('papers', PaperController::class)->only(['index', 'create', 'store', 'show']);
Route::get('/papers/{paper}/status', [PaperController::class, 'status'])->name('papers.status');
Route::post('/papers/{paper}/reassess', [PaperController::class, 'reassess'])->name('papers.reassess');
Route::get('/papers/{paper}/export/json', [PaperController::class, 'exportJson'])->name('papers.export.json');
Route::post('/claims/{claim}/override', [PaperController::class, 'overrideClaim'])->name('claims.override');
