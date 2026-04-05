<?php

use Faker\Generator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Livewire\Component;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;
use TomEasterbrook\LivewireFakeable\Http\Middleware\InjectFakeableBanner;

// --- State class fixture ---

class ProfileState
{
    public function __invoke(Generator $faker): array
    {
        return [
            'firstName' => $faker->firstName(),
            'lastName' => $faker->lastName(),
        ];
    }
}

// --- Livewire component fixtures ---

class PropertyLevelFakeableComponent extends Component
{
    #[Fakeable('name')]
    public ?string $name = null;

    #[Fakeable('email')]
    public ?string $email = null;

    public function render()
    {
        return '<div>{{ $name }} {{ $email }}</div>';
    }
}

#[Fakeable(ProfileState::class)]
class ClassLevelFakeableComponent extends Component
{
    public ?string $firstName = null;

    public ?string $lastName = null;

    public function render()
    {
        return '<div>{{ $firstName }} {{ $lastName }}</div>';
    }
}

#[Fakeable(ProfileState::class)]
class MixedLevelFakeableComponent extends Component
{
    public ?string $firstName = null;

    public ?string $lastName = null;

    #[Fakeable('city')]
    public ?string $city = null;

    public function render()
    {
        return '<div>{{ $firstName }} {{ $lastName }} {{ $city }}</div>';
    }
}

class MountSetsFakeableComponent extends Component
{
    #[Fakeable('name')]
    public ?string $name = null;

    #[Fakeable('email')]
    public ?string $email = null;

    public function mount(): void
    {
        $this->name = 'Set By Mount';
    }

    public function render()
    {
        return '<div>{{ $name }} {{ $email }}</div>';
    }
}

class NoFakeableComponent extends Component
{
    public ?string $name = null;

    public function render()
    {
        return '<div>{{ $name }}</div>';
    }
}

// --- Tests ---

beforeEach(function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    config()->set('fakeable.enabled', true);
    config()->set('fakeable.allowed_hosts', ['localhost']);
    config()->set('fakeable.show_indicator', true);
    app()->detectEnvironment(fn () => 'local');
});

it('applies property-level Fakeable attributes after mount', function () {
    Livewire::test(PropertyLevelFakeableComponent::class)
        ->assertSet('name', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('email', fn ($value) => is_string($value) && str_contains($value, '@'));
});

it('applies class-level Fakeable attribute after mount', function () {
    Livewire::test(ClassLevelFakeableComponent::class)
        ->assertSet('firstName', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('lastName', fn ($value) => is_string($value) && $value !== '');
});

it('applies both class-level and property-level attributes', function () {
    Livewire::test(MixedLevelFakeableComponent::class)
        ->assertSet('firstName', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('lastName', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('city', fn ($value) => is_string($value) && $value !== '');
});

it('preserves properties set by mount', function () {
    Livewire::test(MountSetsFakeableComponent::class)
        ->assertSet('name', 'Set By Mount')
        ->assertSet('email', fn ($value) => is_string($value) && str_contains($value, '@'));
});

it('does nothing when guard fails', function () {
    config()->set('fakeable.enabled', false);

    Livewire::test(PropertyLevelFakeableComponent::class)
        ->assertSet('name', null)
        ->assertSet('email', null);
});

it('does nothing when environment is not local', function () {
    app()->detectEnvironment(fn () => 'production');

    Livewire::test(PropertyLevelFakeableComponent::class)
        ->assertSet('name', null)
        ->assertSet('email', null);
});

it('does nothing when host is not allowed', function () {
    config()->set('fakeable.allowed_hosts', ['other.test']);

    Livewire::test(PropertyLevelFakeableComponent::class)
        ->assertSet('name', null)
        ->assertSet('email', null);
});

it('leaves components without Fakeable attributes unchanged', function () {
    Livewire::test(NoFakeableComponent::class)
        ->assertSet('name', null);
});

// --- Banner middleware tests ---

it('injects banner when show_indicator is true and guard passes', function () {
    $html = '<html><body><div>content</div></body></html>';
    $response = new Response($html);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())
        ->toContain('id="fakeable-banner"')
        ->toContain('FAKEABLE');
});

it('does not inject banner when show_indicator is false', function () {
    config()->set('fakeable.show_indicator', false);

    $html = '<html><body><div>content</div></body></html>';
    $response = new Response($html);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->not->toContain('wirefake-banner');
});

it('does not inject banner when guard fails', function () {
    config()->set('fakeable.enabled', false);

    $html = '<html><body><div>content</div></body></html>';
    $response = new Response($html);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->not->toContain('wirefake-banner');
});

it('does not inject banner when response has no body tag', function () {
    $html = '<div>partial content</div>';
    $response = new Response($html);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->toBe($html);
});

it('does not corrupt JSON responses', function () {
    $json = '{"name":"Tom","email":"tom@example.com"}';
    $response = new Response($json, 200, ['Content-Type' => 'application/json']);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->toBe($json);
});

it('does not corrupt empty responses', function () {
    $response = new Response('', 204);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->toBe('');
});

it('handles redirect responses without error', function () {
    $response = new RedirectResponse('http://localhost/dashboard');

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->isRedirection())->toBeTrue();
});

it('does not inject banner into response with binary-like content', function () {
    $binary = random_bytes(64);
    $response = new Response($binary, 200, ['Content-Type' => 'application/octet-stream']);

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result->getContent())->toBe($binary);
});

it('handles streamed response where getContent returns false', function () {
    $response = new StreamedResponse(function () {
        echo '<html><body>streamed</body></html>';
    });

    $middleware = new InjectFakeableBanner;
    $result = $middleware->handle(new Request, fn () => $response);

    expect($result)->toBeInstanceOf(StreamedResponse::class)
        ->and($result->getContent())->toBeFalse();
});
