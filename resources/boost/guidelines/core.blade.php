## WireFake (tomeasterbrook/wire-fake)

WireFake fills **empty** public Livewire 4 component state with [Faker](https://fakerphp.org/formatters/) during **local** development. It runs **after** `mount` and only sets properties that are still `null`, `''`, or `[]` — it never overwrites real data you assigned in `mount` or elsewhere.

### When faking runs

All must be true: `config('fakeable.enabled')`, `app()->environment('local')`, request host matches `config('fakeable.allowed_hosts')`, and `Faker\Generator` is available. Typical `APP_ENV=testing` is not `local`, so tests are unaffected. Do not rely on WireFake in production code paths.

### Property-level: Faker formatters

Use `TomEasterbrook\WireFake\Attributes\Fakeable` on **public** properties. The first argument is the Faker method name; optional `seed` for stable values; additional arguments are passed to that formatter.

@verbatim
<code-snippet name="WireFake property attributes" lang="php">
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class EditProfilePage extends Component
{
    #[Fakeable('name')]
    public string $name = '';

    #[Fakeable('safeEmail')]
    public string $email = '';

    #[Fakeable('sentence', words: 3)]
    public string $title = '';

    #[Fakeable('name', seed: 42)]
    public string $stableName = '';
}
</code-snippet>
@endverbatim

### Class-level: invokable state class

Point `#[Fakeable]` at an invokable class that receives `Faker\Generator` and returns an array keyed by **public** property names. Empty properties get those values.

@verbatim
<code-snippet name="WireFake state class" lang="php">
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

// On the component:
// #[Fakeable(ProfileFormState::class)]
// public string $name = '';
// public string $email = '';
</code-snippet>
@endverbatim

### Property-level: array shapes

Pass an array of `key => formatter` pairs to generate structured array data without a state class. Use `count` to control how many rows are generated (defaults to 1).

@verbatim
<code-snippet name="WireFake array shape" lang="php">
use Livewire\Component;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class ReferencesPage extends Component
{
    #[Fakeable(['name' => 'name', 'phone' => 'phoneNumber', 'email' => 'safeEmail'], count: 2)]
    public array $references = [];

    #[Fakeable(['city' => 'city', 'postcode' => 'postcode'])]
    public array $address = [];
}
</code-snippet>
@endverbatim

### `mount()` and `HasFakeable`

Use `TomEasterbrook\WireFake\Concerns\HasFakeable` and `$this->fakeable(SomeStateClass::class)` inside `mount()` when you need to register state programmatically; same empty-only rules apply.

### Configuration

Publish: `php artisan vendor:publish --tag="wire-fake-config"`. Keys live under `config/fakeable.php`: `enabled` (`FAKEABLE_ENABLED`), `allowed_hosts`, `locale`, `show_indicator` (on-page **WireFake** label when faking is active).

### Do not

Do not use the `WireFake` facade for normal faking — resolution is handled by the Livewire hook after `mount`. Prefer `#[Fakeable]` and optional `HasFakeable` as above.
