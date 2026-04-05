# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WireFake is a Laravel package for Livewire 4 that auto-fills component state with realistic fake data during local development. Developers annotate public properties with `#[Fakeable]` attributes, and the package fills null/empty properties with Faker-generated data after component mount — only in safe conditions (local env, allowed hosts).

## Commands

```bash
composer test              # Run Pest tests
composer test-coverage     # Tests with coverage
composer analyse           # PHPStan (level 5, src/ and config/)
composer format            # Laravel Pint code formatting
vendor/bin/pest tests/path/to/TestFile.php              # Single test file
vendor/bin/pest --filter="test name"                     # Single test by name
```

## Architecture

The package hooks into Livewire's component lifecycle:

1. **FakeableBanner** (`src/Livewire/Hooks/FakeableBanner.php`) — Livewire ComponentHook registered at boot. After `mount()`, it invokes the guard and resolver, then optionally injects a visual indicator on `render()`.
2. **FakeableGuard** (`src/Services/FakeableGuard.php`) — Safety gate: checks enabled flag, local environment, allowed hosts (glob via `fnmatch`), and Faker availability. All four must pass.
3. **FakeableResolver** (`src/Services/FakeableResolver.php`) — Uses PHP reflection to find `#[Fakeable]` attributes on classes and properties, then generates values via Faker or state classes.
4. **Fakeable attribute** (`src/Attributes/Fakeable.php`) — Can be applied at class or property level. Accepts formatter name, optional seed, and formatter args. Can reference a state class instead of a Faker formatter.
5. **HasFakeable trait** (`src/Concerns/HasFakeable.php`) — Optional trait for manual `fakeable()` invocation from `mount()`.
6. **WireFakeServiceProvider** (`src/Providers/WireFakeServiceProvider.php`) — Extends Spatie's `PackageServiceProvider`, registers config and Livewire hook.

## Boost Guidelines

When adding or changing features, update `resources/boost/guidelines/core.blade.php` to document the new behaviour. This file is used by Laravel Boost to provide AI-assisted guidance to consumers of the package.

## Testing

- Uses Pest 4 with `orchestra/testbench` for Laravel package testing
- `tests/TestCase.php` registers both `LivewireServiceProvider` and `WireFakeServiceProvider`
- `tests/Features/` — Integration tests (component behavior)
- `tests/Unit/` — Unit tests (services)
- `tests/ArchTest.php` — Architecture checks (no `dd`, `dump`, `ray`)

## CI

GitHub Actions matrix: PHP 8.3–8.4 × Laravel 12–13 × prefer-lowest/stable × Ubuntu/Windows. Separate workflows for PHPStan and auto-formatting via Pint.
