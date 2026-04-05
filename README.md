# WireFake

<p align="center">
  <a href="https://packagist.org/packages/tomeasterbrook/wire-fake"><img src="https://img.shields.io/packagist/v/tomeasterbrook/wire-fake?style=for-the-badge" alt="Packagist version"></a>
  <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/tomeasterbrook/wire-fake?style=for-the-badge" alt="License MIT"></a>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777bb4?style=for-the-badge" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/Livewire-4-fb70a9?style=for-the-badge" alt="Livewire 4">
</p>

<p align="center"><strong>Fake data on your Livewire components — one attribute away. Local only. Never overwrites real <code>mount()</code> data.</strong></p>

---

| | |
| :--- | :--- |
| **Does** | After `mount`, fills empty public properties from [Faker](https://fakerphp.org/formatters/) via `#[Fakeable]` or a small state class |
| **Doesn’t** | Run outside `local`, on disallowed hosts, without Faker, or in tests — [details](#safety) below |

```bash
composer require tomeasterbrook/wire-fake
```

```php
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class EditProfilePage extends Component
{
    #[Fakeable('name')]
    public string $name = '';

    #[Fakeable('safeEmail')]
    public string $email = '';
}
```

---

## Install

Auto-discovery is on. Optional config:

```bash
php artisan vendor:publish --tag="wire-fake-config"
```

**Requires:** PHP 8.1+, Laravel 10–13, `livewire/livewire:^4.0` (pulled in by this package).

---

## Usage

### Properties

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

Use any [Faker formatter](https://fakerphp.org/formatters/) as the first argument. Extra arguments go to the formatter:

```php
#[Fakeable('sentence', words: 3)]
public string $title = '';

#[Fakeable('imageUrl', width: 200, height: 200)]
public string $avatar_url = '';
```

Stable values across reloads (screenshots, demos):

```php
#[Fakeable('name', seed: 42)]
public string $name = '';
```

### Whole component (state class)

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

### `fakeable()` helper

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

Class-level and property-level `#[Fakeable]` can be mixed.

### After `mount` — only empty fields

Runs **after** `mount`. Only `null` or `''` get filled; anything you set in `mount()` stays.

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
        $this->name = $user->name; // kept
        // $this->email still '' → fake filled
    }
}
```

### Locale

```php
// config/fakeable.php
'locale' => 'fr_FR', // default en_US
```

### On-screen badge

When active, a small **WireFake** label is injected so you know data is fake. Toggle:

```php
// config/fakeable.php
'show_indicator' => true,
```

---

## Safety

For **local development** only. Injection runs only if **all** of these pass:

1. `config('fakeable.enabled')` — e.g. `FAKEABLE_ENABLED` in `.env`
2. `app()->environment('local')`
3. Request host matches `allowed_hosts` (`*.test`, `*.dev`, `localhost` by default)
4. `class_exists(Faker\Generator::class)` — add `fakerphp/faker` in dev if needed

Usual `APP_ENV=testing` means not `local`, so tests stay clean.

---

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

---

## Testing

```bash
composer test
```

## Changelog

[CHANGELOG](CHANGELOG.md)

## Contributing

[CONTRIBUTING](CONTRIBUTING.md)

## Security

[Security policy](../../security/policy)

## Credits

- [Tom Easterbrook](https://github.com/TomEasterbrook)
- [All Contributors](../../contributors)

## License

[MIT](LICENSE.md)
