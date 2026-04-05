<?php

use TomEasterbrook\WireFake\Attributes\Fakeable;
use TomEasterbrook\WireFake\Services\FakeableResolver;

beforeEach(function () {
    config()->set('fakeable.locale', 'en_US');
});

it('resolves a property with a simple formatter', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public ?string $fullName = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('fullName')
        ->and($result['fullName'])->toBeString()->not->toBeEmpty();
});

it('passes extra arguments to the formatter', function () {
    $component = new class
    {
        #[Fakeable('sentence', nbWords: 3)]
        public ?string $bio = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('bio')
        ->and($result['bio'])->toBeString()->not->toBeEmpty();
});

it('seeds the faker instance when seed is specified', function () {
    $component1 = new class
    {
        #[Fakeable('name', seed: 42)]
        public ?string $name = null;
    };

    $component2 = new class
    {
        #[Fakeable('name', seed: 42)]
        public ?string $name = null;
    };

    $result1 = (new FakeableResolver)->resolve($component1);
    $result2 = (new FakeableResolver)->resolve($component2);

    expect($result1['name'])->toBe($result2['name']);
});

it('produces different values with different seeds', function () {
    $component1 = new class
    {
        #[Fakeable('name', seed: 1)]
        public ?string $name = null;
    };

    $component2 = new class
    {
        #[Fakeable('name', seed: 9999)]
        public ?string $name = null;
    };

    $result1 = (new FakeableResolver)->resolve($component1);
    $result2 = (new FakeableResolver)->resolve($component2);

    expect($result1['name'])->not->toBe($result2['name']);
});

it('skips properties with non-null non-empty values', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public string $name = 'Existing Name';
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toBeEmpty();
});

it('resolves properties with null value', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
});

it('resolves properties with empty string value', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public string $name = '';
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
});

it('skips properties without the Fakeable attribute', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public ?string $name = null;

        public ?string $email = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('email');
});

it('resolves multiple properties', function () {
    $component = new class
    {
        #[Fakeable('name')]
        public ?string $name = null;

        #[Fakeable('email')]
        public ?string $email = null;

        #[Fakeable('city')]
        public ?string $city = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveCount(3)
        ->toHaveKeys(['name', 'email', 'city']);
});

it('uses the configured locale', function () {
    config()->set('fakeable.locale', 'ja_JP');

    $component = new class
    {
        #[Fakeable('name')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    // Verify it returns a value (locale was applied without error)
    expect($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
});
