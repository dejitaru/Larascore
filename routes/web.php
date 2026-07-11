<?php

use App\Http\Controllers\AnalyzeController;
use App\Http\Controllers\ScoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ScoreController::class, 'home'])->name('home');
Route::post('/analyze', [AnalyzeController::class, 'store'])->name('analyze.store');
Route::get('/score/{owner}/{repo}', [ScoreController::class, 'show'])->name('score.show');
