<?php

use Faker\Generator;
use Livewire\Component;
use Livewire\Livewire;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;
use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

/**
 * Invokable state class for class-level #[Fakeable] on anonymous components.
 * PHP attributes cannot reference anonymous invokable classes.
 */
class InlineFakeableState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'alpha' => $faker->lexify('????'),
            'beta' => $faker->numerify('###'),
        ];
    }
}

beforeEach(function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    config()->set('fakeable.enabled', true);
    config()->set('fakeable.allowed_hosts', ['localhost']);
    config()->set('fakeable.show_indicator', false);
    app()->detectEnvironment(fn () => 'local');
});

it('fills property-level Fakeable attributes on an anonymous Livewire component', function () {
    $instance = new class extends Component
    {
        #[Fakeable('uuid')]
        public ?string $requestId = null;

        #[Fakeable('safeEmail')]
        public ?string $contactEmail = null;

        public function render()
        {
            return '<div>inline</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('requestId', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('contactEmail', fn ($value) => is_string($value) && str_contains($value, '@'));
});

it('fills class-level Fakeable on an anonymous Livewire component', function () {
    $instance = new #[Fakeable(InlineFakeableState::class)] class extends Component
    {
        public ?string $alpha = null;

        public ?string $beta = null;

        public function render()
        {
            return '<div>inline-class</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('alpha', fn ($value) => is_string($value) && strlen($value) === 4)
        ->assertSet('beta', fn ($value) => is_string($value) && strlen($value) === 3 && ctype_digit($value));
});

it('fills class-level and property-level Fakeable on one anonymous component', function () {
    $instance = new #[Fakeable(InlineFakeableState::class)] class extends Component
    {
        public ?string $alpha = null;

        public ?string $beta = null;

        #[Fakeable('city')]
        public ?string $city = null;

        public function render()
        {
            return '<div>inline-mixed</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('alpha', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('beta', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('city', fn ($value) => is_string($value) && $value !== '');
});

it('resolves Fakeable for anonymous component instances via FakeableResolver', function () {
    $component = new #[Fakeable(InlineFakeableState::class)] class extends Component
    {
        public ?string $alpha = null;

        public ?string $beta = null;

        #[Fakeable('ipv4')]
        public ?string $ip = null;

        public function render()
        {
            return '<div></div>';
        }
    };

    $resolved = (new FakeableResolver)->resolve($component);

    expect($resolved)->toHaveKeys(['alpha', 'beta', 'ip'])
        ->and($resolved['alpha'])->toBeString()->not->toBeEmpty()
        ->and($resolved['beta'])->toBeString()->not->toBeEmpty()
        ->and($resolved['ip'])->toMatch('/^\d{1,3}(\.\d{1,3}){3}$/');
});

it('fills array properties with empty placeholder structures', function () {
    $instance = new class extends Component
    {
        #[Fakeable(['name' => 'name', 'phone' => 'phoneNumber', 'email' => 'safeEmail'], count: 2)]
        public array $references = [
            ['name' => '', 'phone' => '', 'email' => ''],
            ['name' => '', 'phone' => '', 'email' => ''],
        ];

        public function render()
        {
            return '<div>placeholders</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('references', fn ($value) => is_array($value)
            && count($value) === 2
            && $value[0]['name'] !== ''
            && str_contains($value[0]['email'], '@')
        );
});

it('fills properties using named formatter arguments', function () {
    $instance = new class extends Component
    {
        #[Fakeable('sentence', nbWords: 3)]
        public ?string $title = null;

        public function render()
        {
            return '<div>named-args</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('title', fn ($value) => is_string($value) && $value !== '');
});

it('does not overwrite mount-assigned public properties on an anonymous component', function () {
    $instance = new class extends Component
    {
        #[Fakeable('word')]
        public ?string $label = null;

        #[Fakeable('sentence')]
        public ?string $description = null;

        public function mount(): void
        {
            $this->label = 'Fixed in mount';
        }

        public function render()
        {
            return '<div>{{ $label }}</div>';
        }
    };

    Livewire::test($instance)
        ->assertSet('label', 'Fixed in mount')
        ->assertSet('description', fn ($value) => is_string($value) && $value !== '');
});
