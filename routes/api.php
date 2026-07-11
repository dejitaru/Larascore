<?php

use App\Http\Controllers\Api\AnalysisResultController;
use Illuminate\Support\Facades\Route;

Route::post('/analysis-result', [AnalysisResultController::class, 'store'])->name('api.analysis-result');
