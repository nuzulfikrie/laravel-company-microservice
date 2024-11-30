<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Feature tests: Extend Laravel's TestCase with RefreshDatabase
uses(TestCase::class)
    ->in('Feature');

// **Unit tests: Use Laravel's TestCase for tests that require Laravel's features**
uses(TestCase::class)
    ->in('Unit');

// Custom Expectations, etc.
expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
