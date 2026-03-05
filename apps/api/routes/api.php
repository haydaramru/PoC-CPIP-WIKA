<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/projects/summary',  [ProjectController::class, 'summary']);
Route::post('/projects/upload',  [ProjectController::class, 'upload']);
Route::apiResource('projects',   ProjectController::class);

// Ingestion file management
Route::get('/ingestion-files',                            [ProjectController::class, 'ingestionFiles']);
Route::get('/ingestion-files/{ingestionFile}/download',   [ProjectController::class, 'download']);
Route::post('/ingestion-files/{ingestionFile}/reprocess', [ProjectController::class, 'reprocess']);

// Insight
Route::get('/projects/{project}/insight', [ProjectController::class, 'insight']);