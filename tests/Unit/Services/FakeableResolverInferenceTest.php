<?php

use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;
use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

beforeEach(function () {
    config()->set('fakeable.locale', 'en_US');
});

// --- Name-based inference ---

it('infers name from property name "email"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $email = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('email')
        ->and($result['email'])->toBeString()->toContain('@');
});

it('infers name from property name "firstName"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $firstName = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('firstName')
        ->and($result['firstName'])->toBeString()->not->toBeEmpty();
});

it('infers name from property name "phone"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $phone = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('phone')
        ->and($result['phone'])->toBeString()->not->toBeEmpty();
});

it('infers name from property name "city"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $city = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('city')
        ->and($result['city'])->toBeString()->not->toBeEmpty();
});

it('infers name from property name "company"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $company = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('company')
        ->and($result['company'])->toBeString()->not->toBeEmpty();
});

it('infers name from property name "description"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $description = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('description')
        ->and($result['description'])->toBeString()->not->toBeEmpty();
});

it('infers name from property name "url"', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $url = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('url')
        ->and($result['url'])->toBeString()->toContain('://');
});

it('infers snake_case property names', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $first_name = null;

        #[Fakeable]
        public ?string $last_name = null;

        #[Fakeable]
        public ?string $email_address = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKeys(['first_name', 'last_name', 'email_address'])
        ->and($result['first_name'])->toBeString()->not->toBeEmpty()
        ->and($result['last_name'])->toBeString()->not->toBeEmpty()
        ->and($result['email_address'])->toBeString()->toContain('@');
});

// --- Type-based inference ---

it('infers string type as word for unknown property names', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $foo = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('foo')
        ->and($result['foo'])->toBeString()->not->toBeEmpty();
});

it('infers int type as randomNumber', function () {
    $component = new class
    {
        #[Fakeable]
        public ?int $quantity = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('quantity')
        ->and($result['quantity'])->toBeInt();
});

it('infers float type as randomFloat', function () {
    $component = new class
    {
        #[Fakeable]
        public ?float $price = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('price')
        ->and($result['price'])->toBeFloat();
});

it('infers bool type as boolean', function () {
    $component = new class
    {
        #[Fakeable]
        public ?bool $isActive = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('isActive')
        ->and($result['isActive'])->toBeBool();
});

// --- Precedence ---

it('prefers name map over type map', function () {
    $component = new class
    {
        #[Fakeable]
        public ?string $email = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    // Name map gives safeEmail (contains @), not type map word
    expect($result['email'])->toContain('@');
});

it('explicit formatter takes precedence over inference', function () {
    $component = new class
    {
        #[Fakeable('city')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    // Should use 'city' formatter, not inferred 'name' from the name map
    expect($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
});

// --- No match ---

it('does not resolve bare Fakeable on an unrecognised class type', function () {
    $component = new class
    {
        #[Fakeable]
        public ?stdClass $data = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('data');
});

it('does not resolve bare Fakeable on an untyped property', function () {
    $component = new class
    {
        #[Fakeable]
        public $mystery = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('mystery');
});
