<?php

use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

it('considers null as empty', function () {
    expect((new FakeableResolver)->isEmpty(null))->toBeTrue();
});

it('considers empty string as empty', function () {
    expect((new FakeableResolver)->isEmpty(''))->toBeTrue();
});

it('considers empty array as empty', function () {
    expect((new FakeableResolver)->isEmpty([]))->toBeTrue();
});

it('considers a non-empty string as not empty', function () {
    expect((new FakeableResolver)->isEmpty('hello'))->toBeFalse();
});

it('considers an array with all empty leaves as empty', function () {
    expect((new FakeableResolver)->isEmpty([['name' => '', 'phone' => '']]))->toBeTrue();
});

it('considers an array with a non-empty leaf as not empty', function () {
    expect((new FakeableResolver)->isEmpty([['name' => 'Tom', 'phone' => '']]))->toBeFalse();
});

it('considers an array containing zero as not empty', function () {
    expect((new FakeableResolver)->isEmpty([0]))->toBeFalse();
});

it('considers an array containing false as not empty', function () {
    expect((new FakeableResolver)->isEmpty([false]))->toBeFalse();
});

it('considers deeply nested empty arrays as empty', function () {
    expect((new FakeableResolver)->isEmpty([
        ['name' => '', 'details' => ['phone' => '', 'email' => '']],
        ['name' => '', 'details' => ['phone' => '', 'email' => '']],
    ]))->toBeTrue();
});

it('considers deeply nested arrays with one non-empty leaf as not empty', function () {
    expect((new FakeableResolver)->isEmpty([
        ['name' => '', 'details' => ['phone' => '', 'email' => 'tom@example.com']],
        ['name' => '', 'details' => ['phone' => '', 'email' => '']],
    ]))->toBeFalse();
});
