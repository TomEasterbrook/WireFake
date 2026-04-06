<?php

use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;
use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

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

it('resolves array shape with count', function () {
    $component = new class
    {
        #[Fakeable(['name' => 'name', 'phone' => 'phoneNumber', 'email' => 'safeEmail'], count: 2)]
        public array $references = [];
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('references')
        ->and($result['references'])->toBeArray()->toHaveCount(2)
        ->and($result['references'][0])->toHaveKeys(['name', 'phone', 'email'])
        ->and($result['references'][0]['name'])->toBeString()->not->toBeEmpty()
        ->and($result['references'][0]['email'])->toContain('@')
        ->and($result['references'][1])->toHaveKeys(['name', 'phone', 'email']);
});

it('resolves array shape with default count of 1', function () {
    $component = new class
    {
        #[Fakeable(['city' => 'city', 'postcode' => 'postcode'])]
        public array $address = [];
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('address')
        ->and($result['address'])->toHaveCount(1)
        ->and($result['address'][0])->toHaveKeys(['city', 'postcode']);
});

it('resolves array shape with seed for deterministic output', function () {
    $component1 = new class
    {
        #[Fakeable(['name' => 'name'], seed: 42, count: 2)]
        public array $items = [];
    };

    $component2 = new class
    {
        #[Fakeable(['name' => 'name'], seed: 42, count: 2)]
        public array $items = [];
    };

    $result1 = (new FakeableResolver)->resolve($component1);
    $result2 = (new FakeableResolver)->resolve($component2);

    expect($result1['items'])->toBe($result2['items']);
});

it('skips property and reports when formatter does not exist', function () {
    $component = new class
    {
        #[Fakeable('notARealFormatter')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('name');
});

it('skips property and reports when array shape has invalid formatter', function () {
    $component = new class
    {
        #[Fakeable(['name' => 'name', 'bad' => 'notARealFormatter'], count: 1)]
        public array $items = [];
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('items');
});

it('resolves valid properties even when another has an invalid formatter', function () {
    $component = new class
    {
        #[Fakeable('notARealFormatter')]
        public ?string $broken = null;

        #[Fakeable('name')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('broken')
        ->and($result)->toHaveKey('name')
        ->and($result['name'])->toBeString()->not->toBeEmpty();
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

it('skips property and reports when formatter is an empty string', function () {
    $component = new class
    {
        #[Fakeable('')]
        public ?string $name = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('name');
});

it('skips protected properties', function () {
    $component = new class
    {
        #[Fakeable('name')]
        protected ?string $name = null;

        #[Fakeable('email')]
        public ?string $email = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('email')
        ->and($result)->not->toHaveKey('name');
});

it('skips private properties', function () {
    $component = new class
    {
        #[Fakeable('name')]
        private ?string $name = null;

        #[Fakeable('email')]
        public ?string $email = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('email')
        ->and($result)->not->toHaveKey('name');
});

it('resolves array properties with empty placeholder structures', function () {
    $component = new class
    {
        #[Fakeable(['name' => 'name', 'phone' => 'phoneNumber', 'email' => 'safeEmail'], count: 2)]
        public array $references = [
            ['name' => '', 'phone' => '', 'email' => ''],
            ['name' => '', 'phone' => '', 'email' => ''],
        ];
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('references')
        ->and($result['references'])->toBeArray()->toHaveCount(2)
        ->and($result['references'][0]['name'])->toBeString()->not->toBeEmpty()
        ->and($result['references'][0]['email'])->toContain('@')
        ->and($result['references'][1]['name'])->toBeString()->not->toBeEmpty();
});

it('skips array properties with non-empty placeholder structures', function () {
    $component = new class
    {
        #[Fakeable(['name' => 'name', 'phone' => 'phoneNumber'], count: 1)]
        public array $references = [
            ['name' => 'Tom', 'phone' => ''],
        ];
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('references');
});

it('accepts named formatterArguments parameter', function () {
    $component = new class
    {
        #[Fakeable('sentence', formatterArguments: [12])]
        public ?string $bio = null;
    };

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('bio')
        ->and($result['bio'])->toBeString()->not->toBeEmpty();
});

it('produces same result with positional and named formatterArguments', function () {
    $component1 = new class
    {
        #[Fakeable('sentence', seed: 42, nbWords: 3)]
        public ?string $bio = null;
    };

    $component2 = new class
    {
        #[Fakeable('sentence', seed: 42, formatterArguments: ['nbWords' => 3])]
        public ?string $bio = null;
    };

    $result1 = (new FakeableResolver)->resolve($component1);
    $result2 = (new FakeableResolver)->resolve($component2);

    expect($result1['bio'])->toBe($result2['bio']);
});
