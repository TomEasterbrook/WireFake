<?php

namespace TomEasterbrook\WireFake\Services;

use Faker\Generator;
use Illuminate\Http\Request;

class FakeableGuard
{
    public function __construct(
        protected Request $request,
    ) {}

    public function allowed(): bool
    {
        return $this->isEnabled()
            && $this->isLocalEnvironment()
            && $this->isAllowedHost()
            && $this->fakerExists();
    }

    protected function isEnabled(): bool
    {
        return (bool) config('fakeable.enabled');
    }

    protected function isLocalEnvironment(): bool
    {
        return app()->environment('local');
    }

    protected function isAllowedHost(): bool
    {
        $host = $this->request->getHost();
        $patterns = config('fakeable.allowed_hosts', []);

        foreach ($patterns as $pattern) {
            if ($this->hostMatchesPattern($host, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function hostMatchesPattern(string $host, string $pattern): bool
    {
        $pattern = trim($pattern);

        if ($pattern === '') {
            return false;
        }

        // Glob semantics: *.test matches myapp.test and sub.myapp.test (any prefix before the suffix).
        return fnmatch($pattern, $host, FNM_CASEFOLD);
    }

    protected function fakerExists(): bool
    {
        return class_exists(Generator::class);
    }
}
