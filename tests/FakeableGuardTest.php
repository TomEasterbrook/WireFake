<?php

use Illuminate\Http\Request;
use TomEasterbrook\WireFake\FakeableGuard;

beforeEach(function () {
    config()->set('fakeable.enabled', true);
    config()->set('fakeable.allowed_hosts', ['*.test', '*.dev', 'localhost']);
    app()->detectEnvironment(fn () => 'local');
});

function createGuard(string $host = 'myapp.test'): FakeableGuard
{
    $request = Request::create("http://{$host}/");

    return new FakeableGuard($request);
}

it('returns true when all conditions pass', function () {
    expect(createGuard()->allowed())->toBeTrue();
});

it('returns false when config fakeable.enabled is false', function () {
    config()->set('fakeable.enabled', false);

    expect(createGuard()->allowed())->toBeFalse();
});

it('returns false when environment is not local', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(createGuard()->allowed())->toBeFalse();
});

it('returns false when environment is testing', function () {
    app()->detectEnvironment(fn () => 'testing');

    expect(createGuard()->allowed())->toBeFalse();
});

it('returns false when request host does not match allowed hosts', function () {
    expect(createGuard('evil.com')->allowed())->toBeFalse();
});

it('matches wildcard host patterns', function () {
    expect(createGuard('myapp.test')->allowed())->toBeTrue()
        ->and(createGuard('myapp.dev')->allowed())->toBeTrue()
        ->and(createGuard('localhost')->allowed())->toBeTrue();
});

it('does not match partial wildcard hosts', function () {
    expect(createGuard('sub.myapp.test')->allowed())->toBeFalse();
});

it('returns false when Faker class does not exist', function () {
    $guard = Mockery::mock(FakeableGuard::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $guard->__construct(Request::create('http://myapp.test/'));
    $guard->shouldReceive('fakerExists')->andReturn(false);

    expect($guard->allowed())->toBeFalse();
});

it('returns false when multiple conditions fail', function () {
    config()->set('fakeable.enabled', false);
    app()->detectEnvironment(fn () => 'production');

    expect(createGuard('evil.com')->allowed())->toBeFalse();
});
