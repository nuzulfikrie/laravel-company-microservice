<?php

use Illuminate\Support\Facades\Route;
use CompanyManagement\Controllers\AuthController;
use CompanyManagement\Middleware\ValidateCompanyManagementToken;

Route::middleware([ValidateCompanyManagementToken::class])->group(function () {
    Route::post('/companies', [AuthController::class, 'createCompany']);
    Route::put('/companies/{id}', [AuthController::class, 'updateCompany']);
    Route::delete('/companies/{id}', [AuthController::class, 'deleteCompany']);
    Route::post('/companies/{companyId}/members', [AuthController::class, 'addCompanyMember']);
});
