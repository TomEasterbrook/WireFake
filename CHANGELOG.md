# Changelog

All notable changes to Livewire Fakeable will be documented in this file.

## 0.1.1 - 2026-04-06

### Added

- **Deep empty detection for arrays** — arrays pre-filled with empty placeholder structures (all leaves `null`, `''`, or `[]`) are now treated as empty and replaced with fake data

## 0.1.0 - 2026-04-05

Initial release.

### Features

- **Property-level `#[Fakeable]`** — annotate public properties with any Faker formatter, optional seed, and formatter arguments
- **Bare `#[Fakeable]` inference** — automatically infer formatters from property name (e.g. `$email` → `safeEmail`), PHP type (`string` → `word`, `int` → `randomNumber`, etc.), or enum type (random case)
- **Array shapes** — generate arrays of structured fake data with `#[Fakeable(['key' => 'formatter'], count: N)]`
- **Class-level state classes** — point `#[Fakeable(StateClass::class)]` at an invokable class for full control
- **Livewire Form object support** — attributes on `Livewire\Form` subclass properties are resolved automatically
- **`HasFakeable` trait** — programmatic `$this->fakeable(StateClass::class)` from `mount()`
- **Four-layer safety guard** — enabled flag, local environment, host allowlist (`fnmatch` globs), and Faker availability
- **Collapsible banner** — visual indicator injected via middleware with localStorage persistence
- **Configurable locale** — any Faker-supported locale via `config/fakeable.php`
