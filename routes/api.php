<?php

use App\Http\Controllers\Api\ResumeController;
use Illuminate\Support\Facades\Route;

Route::post('/resume/upload', [ResumeController::class, 'upload']);
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
    ]);
});
