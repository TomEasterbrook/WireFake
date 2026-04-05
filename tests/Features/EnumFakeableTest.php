<?php

use Livewire\Component;
use Livewire\Livewire;
use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;

// --- Enum fixtures ---

enum LivewireStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Archived = 'archived';
}

enum LivewirePriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}

// --- Component fixtures ---

class StringEnumComponent extends Component
{
    #[Fakeable]
    public ?LivewireStatus $status = null;

    #[Fakeable('name')]
    public ?string $name = null;

    public function render()
    {
        return '<div>{{ $status?->value }} {{ $name }}</div>';
    }
}

class IntEnumComponent extends Component
{
    #[Fakeable]
    public ?LivewirePriority $priority = null;

    public function render()
    {
        return '<div>{{ $priority?->value }}</div>';
    }
}

class EnumSetByMountComponent extends Component
{
    #[Fakeable]
    public ?LivewireStatus $status = null;

    public function mount(): void
    {
        $this->status = LivewireStatus::Active;
    }

    public function render()
    {
        return '<div>{{ $status->value }}</div>';
    }
}

// --- Tests ---

beforeEach(function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    config()->set('fakeable.enabled', true);
    config()->set('fakeable.allowed_hosts', ['localhost']);
    config()->set('fakeable.show_indicator', false);
    app()->detectEnvironment(fn () => 'local');
});

it('fills a nullable string-backed enum property with a random case', function () {
    Livewire::test(StringEnumComponent::class)
        ->assertSet('status', fn ($value) => $value instanceof LivewireStatus)
        ->assertSet('name', fn ($value) => is_string($value) && $value !== '');
});

it('fills a nullable int-backed enum property with a random case', function () {
    Livewire::test(IntEnumComponent::class)
        ->assertSet('priority', fn ($value) => $value instanceof LivewirePriority);
});

it('preserves enum value set by mount', function () {
    Livewire::test(EnumSetByMountComponent::class)
        ->assertSet('status', LivewireStatus::Active);
});
