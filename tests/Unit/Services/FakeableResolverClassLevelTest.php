<?php

use Faker\Generator;
use TomEasterbrook\WireFake\Attributes\Fakeable;
use TomEasterbrook\WireFake\Concerns\HasFakeable;
use TomEasterbrook\WireFake\Services\FakeableResolver;

beforeEach(function () {
    config()->set('fakeable.locale', 'en_US');
});

// --- State class fixtures ---

class NameAndEmailState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'name' => $faker->name(),
            'email' => $faker->email(),
        ];
    }
}

class OverflowState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'name' => $faker->name(),
            'nonExistent' => 'should be ignored',
        ];
    }
}

class ReceiverState
{
    public static ?Generator $received = null;

    public function __invoke(Generator $faker): array
    {
        static::$received = $faker;

        return ['name' => $faker->name()];
    }
}

// --- Component fixtures ---

#[Fakeable(NameAndEmailState::class)]
class ClassLevelComponent
{
    public ?string $name = null;

    public ?string $email = null;
}

#[Fakeable(ReceiverState::class)]
class ReceiverComponent
{
    public ?string $name = null;
}

#[Fakeable(NameAndEmailState::class)]
class ClassLevelWithExistingValueComponent
{
    public ?string $name = null;

    public string $email = 'existing@example.com';
}

#[Fakeable(NameAndEmailState::class)]
class ClassLevelWithEmptyStringComponent
{
    public string $name = '';

    public string $email = 'existing@example.com';
}

#[Fakeable(OverflowState::class)]
class OverflowComponent
{
    public ?string $name = null;
}

#[Fakeable(NameAndEmailState::class)]
class MixedComponent
{
    public ?string $name = null;

    public ?string $email = null;

    #[Fakeable('city')]
    public ?string $city = null;
}

#[Fakeable(NameAndEmailState::class)]
class ClassAndPropertyOverlapComponent
{
    #[Fakeable('name', seed: 99)]
    public ?string $name = null;

    public ?string $email = null;
}

// --- Tests ---

it('detects class-level Fakeable attribute and invokes state class', function () {
    $component = new ClassLevelComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKeys(['name', 'email'])
        ->and($result['name'])->toBeString()->not->toBeEmpty()
        ->and($result['email'])->toBeString()->toContain('@');
});

it('state class receives a Faker Generator instance', function () {
    ReceiverState::$received = null;
    $component = new ReceiverComponent;

    (new FakeableResolver)->resolve($component);

    expect(ReceiverState::$received)->toBeInstanceOf(Generator::class);
});

it('only fills properties that are null', function () {
    $component = new ClassLevelWithExistingValueComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('email');
});

it('only fills properties that are empty string', function () {
    $component = new ClassLevelWithEmptyStringComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('email');
});

it('skips non-existent properties from state array', function () {
    $component = new OverflowComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('nonExistent');
});

it('mixes property-level and class-level attributes', function () {
    $component = new MixedComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKeys(['name', 'email', 'city'])
        ->and($result['city'])->toBeString()->not->toBeEmpty();
});

it('class-level values are not overridden by property-level', function () {
    $component = new ClassAndPropertyOverlapComponent;

    $result = (new FakeableResolver)->resolve($component);

    // Class-level runs first; property-level skips already-resolved keys
    expect($result)->toHaveKeys(['name', 'email']);
});

// --- HasFakeable trait tests ---

it('provides fakeable() method via HasFakeable trait', function () {
    $component = new class
    {
        use HasFakeable;

        public ?string $name = null;

        public ?string $email = null;
    };

    $component->fakeable(NameAndEmailState::class);

    expect($component->name)->toBeString()->not->toBeEmpty()
        ->and($component->email)->toBeString()->toContain('@');
});

it('fakeable() method only fills null or empty properties', function () {
    $component = new class
    {
        use HasFakeable;

        public ?string $name = null;

        public string $email = 'keep@this.com';
    };

    $component->fakeable(NameAndEmailState::class);

    expect($component->name)->toBeString()->not->toBeEmpty()
        ->and($component->email)->toBe('keep@this.com');
});

it('fakeable() method can be called programmatically like in mount()', function () {
    $component = new class
    {
        use HasFakeable;

        public ?string $name = null;

        public ?string $email = null;

        public function mount(): void
        {
            $this->fakeable(NameAndEmailState::class);
        }
    };

    $component->mount();

    expect($component->name)->toBeString()->not->toBeEmpty()
        ->and($component->email)->toBeString()->toContain('@');
});
