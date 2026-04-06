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


## Installation

```bash
composer require tomeasterbrook/livewire-fakeable
```

The service provider is discovered automatically. Publish the config if you want to change locale, hosts, or the on-page indicator:

```bash
php artisan vendor:publish --tag="livewire-fakeable-config"
```

## Quick start

Annotate public properties with `#[Fakeable]` and a [Faker formatter](https://fakerphp.org/formatters/) name. Empty properties are filled after `mount` — only in local dev, never overwriting values you already set.

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

Use `#[Fakeable]` without a formatter to infer one automatically from the property name, type, or enum.

See the [full documentation](https://tomeasterbrook.github.io/livewire-fakeable/) for array shapes, state classes, Form objects, seeds, locale, and more.

## Safety

Faking only runs when **all** of these are true — otherwise the package does nothing:

- `enabled` is `true` in config
- App environment is `local`
- Request host matches an `allowed_hosts` glob (e.g. `*.test`)
- `Faker\Generator` is available

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
