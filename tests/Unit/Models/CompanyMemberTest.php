<?php

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyMember;
use App\Enums\CompanyMemberEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create([
        'original_admin_id' => $this->user->id
    ]);
});

test('company member can be created with valid data', function () {
    $memberData = [
        'email' => $this->user->email,
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole()
    ];

    $member = CompanyMember::createCompanyMember($memberData);

    expect($member)
        ->toBeInstanceOf(CompanyMember::class)
        ->company_id->toBe($this->company->id)
        ->user_id->toBe($this->user->id)
        ->role->toBe(CompanyMemberEnum::getMemberRole());
});

test('company member can be updated with valid data', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole(),
        'email' => $this->user->email
    ]);

    $updateData = [
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getAdminRole(),
        'email' => $this->user->email
    ];

    $result = CompanyMember::updateCompanyMember($member->id, $updateData);

    expect($result)
        ->toBeInstanceOf(CompanyMember::class)
        ->role->toBe(CompanyMemberEnum::getAdminRole());
});

test('company member can be soft deleted', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole(),
        'email' => $this->user->email
    ]);

    $result = CompanyMember::deleteCompanyMember($member->id);

    expect($result)->toBeTrue()
        ->and(CompanyMember::withTrashed()->find($member->id)->deleted_at)
        ->not->toBeNull();
});

test('company members can be retrieved by company id', function () {
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        CompanyMember::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => CompanyMemberEnum::getMemberRole()
        ]);
    }

    $members = CompanyMember::getCompanyMember($this->company->id);

    expect($members)
        ->toHaveCount(3)
        ->first()->toBeInstanceOf(CompanyMember::class);
});

test('company member has correct relationships', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole()
    ]);

    expect($member)
        ->company->toBeInstanceOf(Company::class)
        ->user->toBeInstanceOf(User::class);
});

test('company member validation rules are correct', function () {
    $rules = CompanyMember::rules();

    expect($rules)
        ->toBeArray()
        ->toHaveKeys(['company_id', 'user_id', 'role']);
});

test('company member can be restored after soft delete', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole(),
        'email' => $this->user->email
    ]);

    CompanyMember::deleteCompanyMember($member->id);
    $result = CompanyMember::rollbackDeleteCompanyMember($member->id);

    expect($result)->toBeTrue()
        ->and(CompanyMember::find($member->id))->not->toBeNull();
});

test('company member update can be rolled back', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'role' => CompanyMemberEnum::getMemberRole(),
        'email' => $this->user->email
    ]);

    $oldData = $member->toArray();

    CompanyMember::updateCompanyMember($member->id, [
        'role' => CompanyMemberEnum::getAdminRole(),
        'email' => $this->user->email
    ]);

    $result = CompanyMember::rollbackUpdateCompanyMember($member->id, $oldData);

    expect($result)
        ->toBeInstanceOf(CompanyMember::class)
        ->role->toBe(CompanyMemberEnum::getMemberRole());
});

test('invalid company member update returns false', function () {
    $member = CompanyMember::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->user->id,
        'email' => $this->user->email,
        'role' => CompanyMemberEnum::getMemberRole()
    ]);

    $result = CompanyMember::updateCompanyMember($member->id, ['role' => null]);

    expect($result)->toBeFalse();
});
