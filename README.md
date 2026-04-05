# WireFake

<p align="center">
    <a href="https://github.com/TomEasterbrook/WireFake/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/TomEasterbrook/WireFake/run-tests.yml?branch=main&label=tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/tomeasterbrook/wire-fake"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/tomeasterbrook/wire-fake?style=flat-square"></a>
    <a href="https://packagist.org/packages/tomeasterbrook/wire-fake"><img alt="Latest Version" src="https://img.shields.io/packagist/v/tomeasterbrook/wire-fake?style=flat-square"></a>
    <a href="LICENSE.md"><img alt="License" src="https://img.shields.io/packagist/l/tomeasterbrook/wire-fake?style=flat-square"></a>
</p>

------

> **Livewire 4.** Fill empty component state with [Faker](https://fakerphp.org/formatters/) while you build — after `mount`, only on your machine, never overwriting values you already set.

**WireFake** is a focused Laravel package with a simple idea: declare fake data next to your Livewire properties, and let a component hook apply it when it is safe. No seeding scripts scattered across `mount()` methods, and no guessing whether you are looking at real or dummy data.

- Install from **[packagist.org/packages/tomeasterbrook/wire-fake »](https://packagist.org/packages/tomeasterbrook/wire-fake)**
- Report issues on **[github.com/TomEasterbrook/WireFake »](https://github.com/TomEasterbrook/WireFake/issues)**

## Installation

```bash
composer require tomeasterbrook/wire-fake
```

The service provider is discovered automatically. Publish the config if you want to change locale, hosts, or the on-page indicator:

```bash
php artisan vendor:publish --tag="wire-fake-config"
```

Requires **PHP 8.1+**, **Laravel 10–13**, and **Livewire 4** (`livewire/livewire:^4.0` is required by this package).

## Usage

Annotate public properties with `#[Fakeable]` and a [Faker formatter](https://fakerphp.org/formatters/) name. WireFake runs **after** `mount` and only fills properties that are still `null` or `''`.

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
}
```

Pass arguments through to Faker, or fix a seed for stable reloads (screenshots, demos):

```php
#[Fakeable('sentence', words: 3)]
public string $title = '';

#[Fakeable('name', seed: 42)]
public string $name = '';
```

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
use TomEasterbrook\WireFake\Attributes\Fakeable;

#[Fakeable(ProfileFormState::class)]
class EditProfilePage extends Component
{
    public string $name = '';
    public string $email = '';
}
```

You can also call `fakeable()` from `mount()` with the `HasFakeable` trait, and mix class-level and property-level `#[Fakeable]` as needed.

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

**Locale** — set `locale` in `config/fakeable.php` (default `en_US`).

**Indicator** — when faking is active, a small **WireFake** label is added to the HTML so you do not confuse local data with production. Disable with `show_indicator` in the same config file.

## Safety

WireFake is for **local development** only. Faking runs only when **all** of the following are true: the package is enabled in config, the app environment is `local`, the request host matches `allowed_hosts`, and `Faker\Generator` exists. Typical `APP_ENV=testing` is not `local`, so tests are unaffected.

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

WireFake is open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
