# WireFake

**Realistic fake state for [Livewire 4](https://livewire.laravel.com/) components — declared on the component, applied only where it is safe.**

WireFake is a focused Laravel package for the Livewire ecosystem: it plugs into Livewire 4’s **component hook** system so you can ship forms, wizards, and dashboards with **declarative, Faker-backed defaults** during local development. No manual `mount()` seeding, no duplicate fixtures scattered across components, and no risk of forgetting you are looking at dummy data.

### Why it exists

Livewire components are where your UI state lives. Building or polishing that UI often means fighting empty fields, placeholder strings, and one-off `mount()` hacks. WireFake keeps that concern **co-located with the component** (attributes and small state classes), **respects real data** when `mount()` already set it, and **stays off** in production, tests, and non-local hosts by design.

### Built for Livewire 4, not bolted on

- **Native integration** — Registers a `ComponentHook` via the package service provider; behavior runs through Livewire’s own lifecycle (`after('mount')`, optional indicator `after('render')`).
- **Idiomatic API** — PHP 8 attributes on properties or classes, optional `HasFakeable` trait for imperative use, full access to [Faker formatters](https://fakerphp.org/formatters/).
- **Safe by default** — Config flag, `local` environment, host allowlist, and Faker availability must all pass. Normal test runs use `APP_ENV=testing` (not `local`), so WireFake stays off. See **Safety** below.

WireFake targets **Livewire 4 only**. It does not support Livewire 3, Inertia, or other stacks — that scope is intentional so the API and guarantees stay sharp for Livewire teams.

## Requirements

- **PHP** 8.1+
- **Laravel** 10, 11, 12, or 13 (via `illuminate/contracts`)
- **Livewire** 4.x (`livewire/livewire:^4.0` — installed automatically with this package)

## Installation

```bash
composer require tomeasterbrook/wire-fake
```

The service provider is discovered automatically. Publish the config when you want to tune locale, hosts, or the on-screen indicator:

```bash
php artisan vendor:publish --tag="wire-fake-config"
```

## Usage

Annotate `Livewire\Component` classes (or properties) and keep developing: WireFake fills gaps **after** `mount`, so production code paths stay honest.

### Faking individual properties

Apply the `#[Fakeable]` attribute directly to properties:

```php
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class EditProfilePage extends Component
{
    #[Fakeable('name')]
    public string $name = '';

    #[Fakeable('safeEmail')]
    public string $email = '';

    #[Fakeable('paragraph')]
    public string $bio = '';

    // ...
}
```

The first argument is any [Faker formatter](https://fakerphp.org/formatters/) name.

Pass formatter arguments as named or variadic parameters:

```php
#[Fakeable('sentence', words: 3)]
public string $title = '';

#[Fakeable('imageUrl', width: 200, height: 200)]
public string $avatar_url = '';
```

For **stable** fake data across reloads (screenshots, design review, demos), pass a seed:

```php
#[Fakeable('name', seed: 42)]
public string $name = '';
```

### Faking entire component state

For correlated fields or richer setups, use a **state class** that receives `Faker\Generator` and returns a property map:

```php
use Faker\Generator;

class ProfileFormState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'name' => $faker->name(),
            'email' => $faker->safeEmail(),
            'bio' => $faker->paragraph(),
            'avatar_url' => $faker->imageUrl(200, 200),
        ];
    }
}
```

Point the component at it with `#[Fakeable]` on the **class**:

```php
use App\FakerStates\ProfileFormState;
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

#[Fakeable(ProfileFormState::class)]
class EditProfilePage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $bio = '';
    public string $avatar_url = '';

    // ...
}
```

Or call `fakeable()` from `mount()` (or anywhere you prefer) using the `HasFakeable` trait:

```php
use App\FakerStates\ProfileFormState;
use Livewire\Component;
use TomEasterbrook\WireFake\Concerns\HasFakeable;

class EditProfilePage extends Component
{
    use HasFakeable;

    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->fakeable(ProfileFormState::class);
    }
}
```

You can **combine** class-level state with property-level formatters when some fields are trivial and others belong in a shared state object.

### How it works

Resolution runs **after** `mount` and only assigns to properties that are still `null` or `''`. Values set in `mount()` (e.g. from a model or request) are left untouched — WireFake fills **gaps**, not your real data.

```php
use App\FakerStates\ProfileFormState;
use App\Models\User;
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

#[Fakeable(ProfileFormState::class)]
class EditProfilePage extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(User $user): void
    {
        $this->name = $user->name; // kept — not empty
        // $this->email is still '' — will be filled with fake data
    }
}
```

That makes it practical on **edit** flows as well as **create** flows: fake only what you did not hydrate.

### Locale

Set the Faker locale in the config to generate fake data in your language:

```php
// config/fakeable.php
'locale' => 'fr_FR',
```

Defaults to `en_US`.

### Debug indicator

When faking is active, a small **WireFake** corner badge is added to the rendered HTML so you never confuse local dummy data with production. Turn it off in config if you prefer a clean canvas:

```php
// config/fakeable.php
'show_indicator' => true,
```

### Safety

WireFake is designed for **local developer experience**, not for seeding production or staging. Several conditions must all pass before anything runs:

**Enabled toggle** — Turn everything off without touching component code:

```php
// config/fakeable.php
'enabled' => env('FAKEABLE_ENABLED', true),
```

Set `FAKEABLE_ENABLED=false` in your `.env` to turn it off without removing any attributes or code.

**Environment** — Only when `app()->environment('local')`.

**Host allowlist** — Even in `local`, the request host must match an allowed pattern (stops surprises if `APP_ENV` is mis-set). Defaults:

```php
// config/fakeable.php
'allowed_hosts' => [
    '*.test',
    '*.dev',
    'localhost',
],
```

You can override this in the published config to match your local setup.

**Faker available** — Uses `class_exists(Faker\Generator::class)`. No Faker class in the autoloader means no operation (typical in lean production installs). Most Laravel apps already ship `fakerphp/faker` in dev; add it if yours does not.

**Testing** — With the usual `APP_ENV=testing` setup, the app is not `local`, so WireFake does not run and Pest/PHPUnit stay deterministic.

All of the above must pass for injection. Otherwise components behave exactly as they would without WireFake.

## Configuration Reference

```php
// config/fakeable.php
return [
    'enabled' => env('FAKEABLE_ENABLED', true),

    'allowed_hosts' => [
        '*.test',
        '*.dev',
        'localhost',
    ],

    'locale' => 'en_US',

    'show_indicator' => true,
];
```

## Ecosystem

WireFake is **MIT-licensed** and scoped on purpose: safe, declarative fake state for **Livewire 4** components, implemented with Livewire’s own hooks. If you use it in libraries or apps, or want to harden docs and edge cases for the wider Livewire community, see [Contributing](CONTRIBUTING.md) — focused issues and pull requests keep the package credible as ecosystem infrastructure.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tom Easterbrook](https://github.com/TomEasterbrook)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
