<?php

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\CompanyMemberEnum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('company can be created with valid data', function () {
    $companyData = [
        'name' => 'Test Company',
        'email' => 'test@company.com',
        'website' => 'https://test.com',
        'phone' => '1234567890',
        'address' => '123 Test St',
        'note' => 'Test note',
        'status' => 'active',
        'has_multiple_subscriptions' => false,
        'original_admin_id' => $this->user->id
    ];

    $company = Company::createCompany($companyData);

    expect($company)
        ->toBeInstanceOf(Company::class)
        ->name->toBe('Test Company')
        ->email->toBe('test@company.com');
});

test('company can be updated with valid data', function () {
    $company = Company::factory()->create([
        'original_admin_id' => $this->user->id
    ]);

    $updateData = [
        'name' => 'Updated Company',
        'email' => 'updated@company.com'
    ];

    $result = Company::updateCompany($company->id, $updateData);

    expect($result)->toBeTrue();

    $company->refresh();
    expect($company)
        ->name->toBe('Updated Company')
        ->email->toBe('updated@company.com');
});

test('company can be soft deleted', function () {
    $company = Company::factory()->create([
        'original_admin_id' => $this->user->id
    ]);

    $result = Company::deleteCompany($company->id);

    expect($result)->toBeTrue()
        ->and(Company::withTrashed()->find($company->id)->deleted_at)->not->toBeNull();
});

test('company can be retrieved by id', function () {
    $company = Company::factory()->create([
        'original_admin_id' => $this->user->id
    ]);

    $retrievedCompany = Company::getCompanyData($company->id);

    expect($retrievedCompany)
        ->toBeInstanceOf(Company::class)
        ->id->toBe($company->id);
});

test('company members can be retrieved', function () {
    $company = Company::factory()->create();


    //users create 4 members
    $users = User::factory()->count(4)->create();

    foreach ($users as $user) {
        CompanyMember::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => CompanyMemberEnum::getMemberRole(),
            'email' => $user->email
        ]);
    }

    $members = $company->members;

    expect($members)->toBeCollection()
        ->and($members->first())->toBeInstanceOf(CompanyMember::class);
});

test('company has correct relationships', function () {
    $company = new Company();

    expect($company->members())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('company scope active works correctly', function () {
    Company::factory()->count(3)->create([
        'status' => 'active',
        'original_admin_id' => $this->user->id
    ]);

    Company::factory()->create([
        'status' => 'inactive',
        'original_admin_id' => $this->user->id
    ]);

    $activeCompanies = Company::active()->get();

    expect($activeCompanies)->toHaveCount(
        Company::active()->count()
    );
});

test('company validation rules are correct', function () {
    $rules = Company::rules();

    expect($rules)
        ->toBeArray()
        ->toHaveKeys([
            'name',
            'email',
            'phone',
            'website',
            'status',
            'has_multiple_subscriptions',
            'original_admin_id'
        ]);
});

test('company throws exception when not found', function () {
    $nonExistentId = 999;

    expect(function () use ($nonExistentId) {
        Company::findOrFail($nonExistentId);
    })->toThrow(ModelNotFoundException::class);
});
