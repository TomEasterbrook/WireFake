<?php

namespace TomEasterbrook\WireFake;

use Faker\Factory;
use Faker\Generator;
use ReflectionClass;
use ReflectionProperty;
use TomEasterbrook\WireFake\Attributes\Fakeable;

class FakeableResolver
{
    public function resolve(object $component): array
    {
        $reflection = new ReflectionClass($component);
        $fakeValues = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes = $property->getAttributes(Fakeable::class);

            if ($attributes === []) {
                continue;
            }

            $currentValue = $property->getValue($component);

            if ($currentValue !== null && $currentValue !== '') {
                continue;
            }

            $fakeable = $attributes[0]->newInstance();
            $faker = $this->createFaker($fakeable->seed);

            $fakeValues[$property->getName()] = $faker->{$fakeable->formatter}(...$fakeable->formatterArguments);
        }

        return $fakeValues;
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
