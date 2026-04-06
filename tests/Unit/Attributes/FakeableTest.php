<?php

use TomEasterbrook\LivewireFakeable\Attributes\Fakeable;

it('can be instantiated with just a formatter', function () {
    $fakeable = new Fakeable('name');

    expect($fakeable->formatter)->toBe('name')
        ->and($fakeable->seed)->toBeNull()
        ->and($fakeable->formatterArguments)->toBe([]);
});

it('can be instantiated with formatter arguments', function () {
    $fakeable = new Fakeable('sentence', words: 3);

    expect($fakeable->formatter)->toBe('sentence')
        ->and($fakeable->seed)->toBeNull()
        ->and($fakeable->formatterArguments)->toBe(['words' => 3]);
});

it('can be instantiated with a seed', function () {
    $fakeable = new Fakeable('name', seed: 42);

    expect($fakeable->formatter)->toBe('name')
        ->and($fakeable->seed)->toBe(42)
        ->and($fakeable->formatterArguments)->toBe([]);
});

it('can be instantiated with a seed and formatter arguments', function () {
    $fakeable = new Fakeable('sentence', seed: 42, words: 3);

    expect($fakeable->formatter)->toBe('sentence')
        ->and($fakeable->seed)->toBe(42)
        ->and($fakeable->formatterArguments)->toBe(['words' => 3]);
});

it('can be instantiated with named formatterArguments', function () {
    $fakeable = new Fakeable('sentence', formatterArguments: [12]);

    expect($fakeable->formatter)->toBe('sentence')
        ->and($fakeable->seed)->toBeNull()
        ->and($fakeable->formatterArguments)->toBe([12]);
});

it('prefers explicit formatterArguments over variadic arguments', function () {
    $fakeable = new Fakeable('sentence', null, 1, formatterArguments: [12]);

    expect($fakeable->formatterArguments)->toBe([12]);
});

it('falls back to variadic arguments when formatterArguments is empty', function () {
    $fakeable = new Fakeable('sentence', nbWords: 3);

    expect($fakeable->formatterArguments)->toBe(['nbWords' => 3]);
});

it('can be instantiated with a class FQCN', function () {
    $fakeable = new Fakeable(stdClass::class);

    expect($fakeable->formatter)->toBe(stdClass::class)
        ->and($fakeable->seed)->toBeNull()
        ->and($fakeable->formatterArguments)->toBe([]);
});

it('targets both class and property', function () {
    $reflection = new ReflectionClass(Fakeable::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();

    expect($attribute->flags)->toBe(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY);
});

it('can be applied to a class', function () {
    $reflection = new ReflectionClass(FakeableClassFixture::class);
    $attributes = $reflection->getAttributes(Fakeable::class);

    expect($attributes)->toHaveCount(1);

    $fakeable = $attributes[0]->newInstance();

    expect($fakeable->formatter)->toBe('stdClass');
});

it('can be applied to a property', function () {
    $reflection = new ReflectionProperty(FakeablePropertyFixture::class, 'name');
    $attributes = $reflection->getAttributes(Fakeable::class);

    expect($attributes)->toHaveCount(1);

    $fakeable = $attributes[0]->newInstance();

    expect($fakeable->formatter)->toBe('name');
});

#[Fakeable('stdClass')]
class FakeableClassFixture {}

class FakeablePropertyFixture
{
    #[Fakeable('name')]
    public string $name;
}
