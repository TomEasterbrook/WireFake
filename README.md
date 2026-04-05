<p align="center">
    <img src="docs/img/logo.png" alt="Livewire Fakeable" width="800">
</p>

<p align="center">
    <a href="https://github.com/TomEasterbrook/livewire-fakeable/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/TomEasterbrook/livewire-fakeable/run-tests.yml?branch=main&label=tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/tomeasterbrook/livewire-fakeable"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/tomeasterbrook/livewire-fakeable?style=flat-square"></a>
    <a href="https://packagist.org/packages/tomeasterbrook/livewire-fakeable"><img alt="Latest Version" src="https://img.shields.io/packagist/v/tomeasterbrook/livewire-fakeable?style=flat-square"></a>
    <a href="LICENSE.md"><img alt="License" src="https://img.shields.io/packagist/l/tomeasterbrook/livewire-fakeable?style=flat-square"></a>
</p>

------

> **Livewire 4.** Fill empty component state with [Faker](https://fakerphp.org/formatters/) while you build — after `mount`, only on your machine, never overwriting values you already set.

**Livewire Fakeable** is a focused Laravel package with a simple idea: declare fake data next to your Livewire properties, and let a component hook apply it when it is safe. No seeding scripts scattered across `mount()` methods, and no guessing whether you are looking at real or dummy data.

- Install from **[packagist.org/packages/tomeasterbrook/livewire-fakeable »](https://packagist.org/packages/tomeasterbrook/livewire-fakeable)**
- Report issues on **[github.com/TomEasterbrook/livewire-fakeable »](https://github.com/TomEasterbrook/livewire-fakeable/issues)**

## Installation

```bash
composer require tomeasterbrook/livewire-fakeable
```

The service provider is discovered automatically. Publish the config if you want to change locale, hosts, or the on-page indicator:

```bash
php artisan vendor:publish --tag="livewire-fakeable-config"
```

Requires **PHP 8.1+**, **Laravel 10–13**, and **Livewire 4** (`livewire/livewire:^4.0` is required by this package).

## Usage

### Explicit formatters

Annotate public properties with `#[Fakeable]` and a [Faker formatter](https://fakerphp.org/formatters/) name. Livewire Fakeable runs **after** `mount` and only fills properties that are still `null`, `''`, or `[]`.

```php
use Livewire\Component;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;

class EditProfilePage extends Component
{
    #[Fakeable('name')]
    public string $name = '';

    #[Fakeable('safeEmail')]
    public string $email = '';

    #[Fakeable('paragraph')]
    public string $bio = '';
}
```

Pass arguments through to Faker, or fix a seed for stable reloads (screenshots, demos):

```php
#[Fakeable('sentence', nbWords: 3)]
public string $title = '';

#[Fakeable('name', seed: 42)]
public string $name = '';
```

### Bare `#[Fakeable]` — automatic inference

Use the attribute without a formatter and Livewire Fakeable will infer one automatically. It checks in order:

1. **Enum type** — picks a random case from the property's enum type.
2. **Property name** — maps common names to Faker formatters (see table below).
3. **PHP type** — falls back to the property's type (`string` → `word`, `int` → `randomNumber`, `float` → `randomFloat`, `bool` → `boolean`).

```php
#[Fakeable]
public string $email = '';       // inferred → safeEmail

#[Fakeable]
public int $quantity = 0;        // inferred → randomNumber

#[Fakeable]
public OrderStatus $status;      // inferred → random enum case
```

<details>
<summary>Property name → formatter map</summary>

| Property name(s) | Formatter |
|---|---|
| `name`, `fullName`, `full_name` | `name` |
| `firstName`, `first_name` | `firstName` |
| `lastName`, `last_name` | `lastName` |
| `email`, `emailAddress`, `email_address` | `safeEmail` |
| `phone`, `phoneNumber`, `phone_number` | `phoneNumber` |
| `address` | `address` |
| `street`, `streetAddress`, `street_address` | `streetAddress` |
| `city` | `city` |
| `state` | `state` |
| `country` | `country` |
| `postcode`, `zipCode`, `zip_code` | `postcode` |
| `company`, `companyName`, `company_name` | `company` |
| `title` | `sentence` |
| `description`, `bio` | `paragraph` |
| `summary` | `sentence` |
| `url`, `website` | `url` |
| `username`, `user_name` | `userName` |

</details>

### Enums

PHP enums (unit, string-backed, and int-backed) are supported out of the box. A bare `#[Fakeable]` attribute detects the enum type and picks a random case. Seeds are supported for deterministic selection.

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
}

#[Fakeable]
public OrderStatus $status;

#[Fakeable(seed: 42)]
public OrderStatus $lockedStatus;
```

### Array shapes

Generate arrays of fake data by passing an array shape — keys are output keys, values are Faker formatter names. Use `count` to control the number of rows:

```php
#[Fakeable(['name' => 'name', 'email' => 'safeEmail'], count: 3)]
public array $users = [];
```

This produces an array like:

```php
[
    ['name' => 'Jane Doe', 'email' => 'jane@example.com'],
    ['name' => 'John Smith', 'email' => 'john@example.com'],
    ['name' => 'Alice Brown', 'email' => 'alice@example.com'],
]
```

### State classes

For a whole component, use a state class that returns an array keyed by property name, then reference it on the class:

```php
use Faker\Generator;

class ProfileFormState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'name' => $faker->name(),
            'email' => $faker->safeEmail(),
        ];
    }
}
```

```php
use App\FakerStates\ProfileFormState;
use Livewire\Component;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;

#[Fakeable(ProfileFormState::class)]
class EditProfilePage extends Component
{
    public string $name = '';
    public string $email = '';
}
```

You can mix class-level and property-level `#[Fakeable]` — property-level attributes take precedence.

### Livewire Form objects

Livewire Fakeable automatically resolves `#[Fakeable]` attributes on [Livewire Form](https://livewire.laravel.com/docs/forms) properties too. Both property-level and class-level attributes work on Form classes:

```php
use Livewire\Form;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;

class ProfileForm extends Form
{
    #[Fakeable('name')]
    public string $name = '';

    #[Fakeable('safeEmail')]
    public string $email = '';
}
```

```php
class EditProfilePage extends Component
{
    public ProfileForm $form;
}
```

### `HasFakeable` trait

You can also call `fakeable()` from `mount()` with the `HasFakeable` trait for programmatic control. The trait respects the same guard conditions.

```php
use App\FakerStates\ProfileFormState;
use Livewire\Component;
use TomEasterbrook\LivewireFakeable\Concerns\HasFakeable;

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

### Locale & indicator

**Locale** — set `locale` in `config/fakeable.php` (default `en_US`). Any [Faker-supported locale](https://fakerphp.org/) works.

**Indicator** — when faking is active, a collapsible banner is injected before `</body>` so you do not confuse local data with production. Its collapsed state persists in `localStorage`. Disable with `show_indicator` in the config.

## Safety

Livewire Fakeable is for **local development** only. Faking runs only when **all** of the following are true:

1. `enabled` is `true` in config
2. The app environment is `local`
3. The request host matches at least one `allowed_hosts` pattern (case-insensitive `fnmatch` globs — `*.test` matches `myapp.test` and `sub.myapp.test`)
4. `Faker\Generator` exists as a class

Because `APP_ENV=testing` is not `local`, your test suite is unaffected. If any condition fails, Livewire Fakeable does nothing — no properties are touched, no banner is injected.

## Configuration

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

Please see [CHANGELOG](CHANGELOG.md).

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md).

## Security

Please see [our security policy](../../security/policy).

## Credits

- [Tom Easterbrook](https://github.com/TomEasterbrook)
- [All Contributors](../../contributors)

## License

Livewire Fakeable is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
