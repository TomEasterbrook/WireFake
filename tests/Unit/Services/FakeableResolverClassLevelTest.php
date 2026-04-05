<?php

use Faker\Generator;
use TomEasterbrook\WireFake\Attributes\Fakeable;
use TomEasterbrook\WireFake\Concerns\HasFakeable;
use TomEasterbrook\WireFake\Services\FakeableResolver;

beforeEach(function () {
    config()->set('fakeable.locale', 'en_US');
    config()->set('fakeable.enabled', true);
    config()->set('fakeable.allowed_hosts', ['localhost']);
    app()->detectEnvironment(fn () => 'local');
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

class ReferencesState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'references' => [
                ['name' => $faker->name(), 'relationship' => 'Line manager', 'phone' => $faker->phoneNumber(), 'email' => ''],
                ['name' => $faker->name(), 'relationship' => 'Supervisor', 'phone' => '', 'email' => $faker->safeEmail()],
            ],
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

#[Fakeable(ReferencesState::class)]
class ArrayPropertyComponent
{
    public array $references = [];
}

#[Fakeable(ReferencesState::class)]
class ArrayPropertyWithExistingValueComponent
{
    public array $references = [['name' => 'Existing', 'relationship' => 'Friend', 'phone' => '123', 'email' => '']];
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

it('skips protected properties from state class', function () {
    $component = new class
    {
        public ?string $name = null;

        protected ?string $email = null;
    };

    $result = (new FakeableResolver)->resolveStateClass($component, NameAndEmailState::class);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('email');
});

it('skips private properties from state class', function () {
    $component = new class
    {
        public ?string $name = null;

        private ?string $email = null;
    };

    $result = (new FakeableResolver)->resolveStateClass($component, NameAndEmailState::class);

    expect($result)->toHaveKey('name')
        ->and($result)->not->toHaveKey('email');
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

it('fills array properties from state class', function () {
    $component = new ArrayPropertyComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->toHaveKey('references')
        ->and($result['references'])->toBeArray()->toHaveCount(2)
        ->and($result['references'][0])->toHaveKeys(['name', 'relationship', 'phone', 'email'])
        ->and($result['references'][1])->toHaveKeys(['name', 'relationship', 'phone', 'email']);
});

it('does not overwrite non-empty array properties', function () {
    $component = new ArrayPropertyWithExistingValueComponent;

    $result = (new FakeableResolver)->resolve($component);

    expect($result)->not->toHaveKey('references');
});
