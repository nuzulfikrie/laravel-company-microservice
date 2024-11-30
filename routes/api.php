<?php

use App\Http\Controllers\CompanyMemberController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ServiceAuthMiddleware;
use App\Http\Middleware\VerifyTokenMiddleware;

Route::middleware([VerifyTokenMiddleware::class])->group(function () {
    Route::apiResource('company-members', CompanyMemberController::class);
    Route::apiResource('companies', CompanyController::class);
});
