<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CPIP API Routes
|--------------------------------------------------------------------------
|
| Semua route di-prefix /api secara otomatis oleh Laravel.
|
| GET    /api/projects             → list semua project (+ filter, sort)
| GET    /api/projects/summary     → data agregat untuk dashboard
| GET    /api/projects/{id}        → detail 1 project
| POST   /api/projects             → create manual
| POST   /api/projects/upload      → upload Excel bulk
| PUT    /api/projects/{id}        → update project
| DELETE /api/projects/{id}        → hapus project
|
*/

// PENTING: 'summary' dan 'upload' harus didefinisikan SEBELUM route {project}
// supaya Laravel tidak salah tangkap "summary" sebagai ID.

Route::get('/projects/summary', [ProjectController::class, 'summary']);
Route::post('/projects/upload',  [ProjectController::class, 'upload']);
Route::apiResource('projects', ProjectController::class);