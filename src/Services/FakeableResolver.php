<?php

namespace TomEasterbrook\WireFake\Services;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class FakeableResolver
{
    public function resolve(object $component): array
    {
        $reflection = new ReflectionClass($component);
        $fakeValues = [];

        $fakeValues = $this->resolveClassLevel($reflection, $component, $fakeValues);
        $fakeValues = $this->resolvePropertyLevel($reflection, $component, $fakeValues);

        return $fakeValues;
    }

    public function resolveStateClass(object $component, string $stateClass): array
    {
        $reflection = new ReflectionClass($component);
        $faker = $this->createFaker(null);
        $state = (new $stateClass)($faker);
        $resolved = [];

        foreach ($state as $propertyName => $value) {
            if (! $reflection->hasProperty($propertyName)) {
                continue;
            }

            $property = $reflection->getProperty($propertyName);

            if (! $property->isPublic()) {
                continue;
            }

            $currentValue = $property->getValue($component);

            if ($currentValue !== null && $currentValue !== '' && $currentValue !== []) {
                continue;
            }

            $resolved[$propertyName] = $value;
        }

        return $resolved;
    }

    protected function resolveClassLevel(ReflectionClass $reflection, object $component, array $fakeValues): array
    {
        $classAttributes = $reflection->getAttributes(Fakeable::class);

        foreach ($classAttributes as $attribute) {
            $fakeable = $attribute->newInstance();
            $stateClass = $fakeable->formatter;
            $faker = $this->createFaker($fakeable->seed);
            $state = (new $stateClass)($faker);

            foreach ($state as $propertyName => $value) {
                if (! $reflection->hasProperty($propertyName)) {
                    continue;
                }

                $property = $reflection->getProperty($propertyName);

                if (! $property->isPublic()) {
                    continue;
                }

                $currentValue = $property->getValue($component);

                if ($currentValue !== null && $currentValue !== '' && $currentValue !== []) {
                    continue;
                }

                $fakeValues[$propertyName] = $value;
            }
        }

        return $fakeValues;
    }

    protected function resolvePropertyLevel(ReflectionClass $reflection, object $component, array $fakeValues): array
    {
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(Fakeable::class);

            if ($attributes === []) {
                continue;
            }

            if (array_key_exists($property->getName(), $fakeValues)) {
                continue;
            }

            $currentValue = $property->getValue($component);

            if ($currentValue !== null && $currentValue !== '' && $currentValue !== []) {
                continue;
            }

            $fakeable = $attributes[0]->newInstance();
            $faker = $this->createFaker($fakeable->seed);

            try {
                if (is_array($fakeable->formatter)) {
                    $fakeValues[$property->getName()] = $this->resolveArrayShape($faker, $fakeable->formatter, $fakeable->count);
                } else {
                    $fakeValues[$property->getName()] = $faker->{$fakeable->formatter}(...$fakeable->formatterArguments);
                }
            } catch (InvalidArgumentException) {
                $formatter = is_array($fakeable->formatter) ? json_encode($fakeable->formatter) : $fakeable->formatter;
                report(new InvalidArgumentException("WireFake: unknown Faker formatter \"{$formatter}\" on property \${$property->getName()}"));
            }
        }

        return $fakeValues;
    }

    protected function resolveArrayShape(Generator $faker, array $shape, int $count): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $row = [];

            foreach ($shape as $key => $formatter) {
                $row[$key] = $faker->{$formatter}();
            }

            $rows[] = $row;
        }

        return $rows;
    }

    protected function createFaker(?int $seed): Generator
    {
        $locale = config('fakeable.locale', 'en_US');
        $faker = Factory::create($locale);

        if ($seed !== null) {
            $faker->seed($seed);
        }

        return $faker;
    }
}
