<?php

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $data)) {
                $arguments[] = $data[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException("Missing required DTO property: {$name}");
            }
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof BaseDTO) {
                $data[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$key] = array_map(
                    fn ($item) => $item instanceof BaseDTO ? $item->toArray() : $item,
                    $value,
                );
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
