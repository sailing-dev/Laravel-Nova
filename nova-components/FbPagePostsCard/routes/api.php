<?php

use App\Http\Controllers\FbReporting\FbPagePostSchedulersController;
use App\Http\Controllers\FbReporting\FbPagePostsController;
use App\Http\Controllers\FbReporting\FbPagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/
Route::post('/submit-page-post', [FbPagePostsController::class, 'submit']);
Route::get('/load-scheduled-drafts', [FbPagePostSchedulersController::class, 'getAllScheduled']);
Route::get('/load-post-library', [FbPagePostsController::class, 'loadLibrary']);
Route::post('/update-page-post', [FbPagePostsController::class, 'update']);
Route::get('/load-page-groups', [FbPagesController::class, 'loadPageGroups']);
Route::post('/schedule-page-post', [FbPagePostSchedulersController::class, 'create']);

Route::delete('/delete-scheduled-draft', [FbPagePostSchedulersController::class, 'delete']);
Route::delete('/delete-post', [FbPagePostsController::class, 'delete']);
