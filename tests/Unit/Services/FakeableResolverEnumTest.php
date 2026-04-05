<?php

use TomEasterbrook\WireFake\Attributes\Fakeable;
use TomEasterbrook\WireFake\Services\FakeableResolver;

beforeEach(function () {
    config()->set('fakeable.locale', 'en_US');
});

// --- Enum fixtures ---

enum SimpleStatus
{
    case Pending;
    case Active;
    case Archived;
}

enum StringBackedColour: string
{
    case Red = 'red';
    case Green = 'green';
    case Blue = 'blue';
}

enum IntBackedPriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}

// --- Tests ---

it('resolves a bare Fakeable on a unit enum property', function () {
    $component = new class
    {
        #[Fakeable]
        public ?SimpleStatus $status = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('status')
        ->and($result['status'])->toBeInstanceOf(SimpleStatus::class);
});

it('resolves a bare Fakeable on a string-backed enum property', function () {
    $component = new class
    {
        #[Fakeable]
        public ?StringBackedColour $colour = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('colour')
        ->and($result['colour'])->toBeInstanceOf(StringBackedColour::class);
});

it('resolves a bare Fakeable on an int-backed enum property', function () {
    $component = new class
    {
        #[Fakeable]
        public ?IntBackedPriority $priority = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('priority')
        ->and($result['priority'])->toBeInstanceOf(IntBackedPriority::class);
});

it('skips enum property that already has a value', function () {
    $component = new class
    {
        #[Fakeable]
        public SimpleStatus $status = SimpleStatus::Active;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('status');
});

it('does not resolve bare Fakeable on a non-enum property', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('name');
});

it('mixes enum and formatter properties', function () {
    $component = new class
    {
        #[Fakeable]
        public ?SimpleStatus $status = null;

        #[Fakeable('name')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('status')
        ->and($result['status'])->toBeInstanceOf(SimpleStatus::class)
        ->and($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
});

it('produces deterministic enum with seed', function () {
    $make = fn () => new class
    {
        #[Fakeable(seed: 42)]
        public ?StringBackedColour $colour = null;
    };

    $result1 = (new FakeableResolver)->resolve($make());
    $result2 = (new FakeableResolver)->resolve($make());

    expect($result1['colour'])->toBe($result2['colour']);
});
