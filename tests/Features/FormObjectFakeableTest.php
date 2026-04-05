<?php

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;
use TomEasterbrook\WireFake\Attributes\Fakeable;

// --- Form fixtures ---

class ProfileForm extends Form
{
    #[Fakeable('name')]
    public ?string $name = null;

    #[Fakeable('safeEmail')]
    public ?string $email = null;
}

class AddressForm extends Form
{
    #[Fakeable('city')]
    public ?string $city = null;

    public ?string $country = null;
}

class FormWithMountValues extends Form
{
    #[Fakeable('name')]
    public ?string $name = null;

    #[Fakeable('safeEmail')]
    public ?string $email = null;
}

// --- Component fixtures ---

class FormObjectComponent extends Component
{
    public ProfileForm $form;

    public function render()
    {
        return '<div>{{ $form->name }} {{ $form->email }}</div>';
    }
}

class MixedFormAndPropertyComponent extends Component
{
    public ProfileForm $form;

    #[Fakeable('city')]
    public ?string $city = null;

    public function render()
    {
        return '<div>{{ $form->name }} {{ $city }}</div>';
    }
}

class PartialFormAttributeComponent extends Component
{
    public AddressForm $form;

    public function render()
    {
        return '<div>{{ $form->city }} {{ $form->country }}</div>';
    }
}

class FormMountComponent extends Component
{
    public FormWithMountValues $form;

    public function mount(): void
    {
        $this->form->name = 'Set By Mount';
    }

    public function render()
    {
        return '<div>{{ $form->name }} {{ $form->email }}</div>';
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

it('fills Fakeable properties on a Livewire form object', function () {
    Livewire::test(FormObjectComponent::class)
        ->assertSet('form.name', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('form.email', fn ($value) => is_string($value) && str_contains($value, '@'));
});

it('fills both form and component-level properties', function () {
    Livewire::test(MixedFormAndPropertyComponent::class)
        ->assertSet('form.name', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('form.email', fn ($value) => is_string($value) && str_contains($value, '@'))
        ->assertSet('city', fn ($value) => is_string($value) && $value !== '');
});

it('only fills form properties that have Fakeable attributes', function () {
    Livewire::test(PartialFormAttributeComponent::class)
        ->assertSet('form.city', fn ($value) => is_string($value) && $value !== '')
        ->assertSet('form.country', null);
});

it('preserves form properties set by mount', function () {
    Livewire::test(FormMountComponent::class)
        ->assertSet('form.name', 'Set By Mount')
        ->assertSet('form.email', fn ($value) => is_string($value) && str_contains($value, '@'));
});

it('does nothing to form objects when guard fails', function () {
    config()->set('fakeable.enabled', false);

    Livewire::test(FormObjectComponent::class)
        ->assertSet('form.name', null)
        ->assertSet('form.email', null);
});
