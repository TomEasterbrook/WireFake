# WireFake

Fill your single-page component state with realistic fake data during local development.

This package provides a PHP attribute and a helper method that let you define Faker-powered state classes for your single-page components (Livewire, Inertia, etc.). Instead of manually seeding forms and pages with test data, define a state class once and have it automatically populated in local environments.

## Installation

```bash
composer require tomeasterbrook/wire-fake
```

Publish the config file:

```bash
php artisan vendor:publish --tag="wire-fake-config"
```

## Usage

### Faking Individual Properties

Apply the `#[Fakeable]` attribute directly to properties to fill them with fake data:

```php
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

The attribute accepts any [Faker formatter](https://fakerphp.org/formatters/) as a string.

You can also pass arguments to the formatter:

```php
#[Fakeable('sentence', words: 3)]
public string $title = '';

#[Fakeable('imageUrl', width: 200, height: 200)]
public string $avatar_url = '';
```

For consistent fake data across page loads (useful for screenshots or design reviews), pass a seed:

```php
#[Fakeable('name', seed: 42)]
public string $name = '';
```

### Faking Entire Component State

For more complex scenarios, define a state class that returns all the fake data at once. The class receives a `Faker\Generator` instance:

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

Then reference it with the `#[Fakeable]` attribute on the class:

```php
use App\FakerStates\ProfileFormState;

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

Or use the `fakeable` method programmatically:

```php
use App\FakerStates\ProfileFormState;

class EditProfilePage extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->fakeable(ProfileFormState::class);
    }
}
```

You can mix both approaches — use `#[Fakeable]` on individual properties for simple fields and a state class for properties that depend on each other.

### How It Works

Fakeable runs **after** the `mount` method completes and only fills properties that are still `null` or an empty string (`''`). This means any real data set during `mount` is preserved — fake data only fills in the gaps.

```php
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

This makes Fakeable safe to use on components that load real data — it will never overwrite existing state.

### Locale

Set the Faker locale in the config to generate fake data in your language:

```php
// config/fakeable.php
'locale' => 'fr_FR',
```

Defaults to `en_US`.

### Debug Indicator

When Fakeable is active, it injects a small visual banner into the page so you don't forget fake data is being applied. This can be disabled in the config:

```php
// config/fakeable.php
'show_indicator' => true,
```

### Safety

Fakeable has multiple layers of protection to ensure fake data never reaches production.

**Enabled toggle** — Fakeable can be explicitly disabled regardless of environment or host:

```php
// config/fakeable.php
'enabled' => env('FAKEABLE_ENABLED', true),
```

Set `FAKEABLE_ENABLED=false` in your `.env` to turn it off without removing any attributes or code.

**Environment check** — Fakeable only runs when `app()->environment('local')`. This is the first gate.

**Host allowlist** — Even in a local environment, Fakeable will only activate when the request host matches an allowed pattern. The default allowlist is:

```php
// config/fakeable.php
'allowed_hosts' => [
    '*.test',
    '*.dev',
    'localhost',
],
```

You can override this in the published config to match your local setup.

**Dev dependency** — `fakerphp/faker` remains a `require-dev` dependency. Fakeable uses `class_exists` checks so that if Faker isn't installed (as in production), the attributes and method are silently ignored with zero overhead.

All four checks must pass for fake data to be injected. If any one fails, the component behaves as normal.

Fakeable is also automatically inactive in the `testing` environment, so it will never interfere with your test assertions.

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
