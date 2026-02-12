<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Core\Schema;

use TYPO3\CMS\Core\Schema\Exception\UndefinedSchemaException;

final readonly class SchemaCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    public function __construct(
        /**
         * @var array<string, SchemaInterface>
         */
        private array $items
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \InvalidArgumentException('A schema cannot be set.', 1712539286);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \InvalidArgumentException('A schema cannot be unset.', 1712539285);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_values(array_map(fn($item): string => $item->getName(), $this->items));
    }

    /**
     * Get a schema from the loaded TCA. Ensure to check for a schema with ->has() before
     * calling ->get().
     */
    public function get(string $schemaName): TcaSchema
    {
        if (!$this->has($schemaName)) {
            throw new UndefinedSchemaException('No TCA schema exists for the name "' . $schemaName . '".', 1661540376);
        }
        if (str_contains($schemaName, '.')) {
            [$mainSchema, $subSchema] = explode('.', $schemaName, 2);
            return $this->get($mainSchema)->getSubSchema($subSchema);
        }
        if (!$this->items[$schemaName] instanceof TcaSchema) {
            throw new \RuntimeException('The schema "' . $schemaName . '" is not of type TcaSchema.', 1773758542);
        }
        return $this->items[$schemaName];
    }

    /**
     * Checks if a schema exists, does not build the schema if not needed, thus it's very slim
     * and only creates a schema if a sub-schema is requested.
     */
    public function has(string $schemaName): bool
    {
        if (str_contains($schemaName, '.')) {
            [$mainSchema, $subSchema] = explode('.', $schemaName, 2);
            if (!$this->has($mainSchema)) {
                return false;
            }
            return $this->get($mainSchema)->hasSubSchema($subSchema);
        }
        return isset($this->items[$schemaName]);
    }

    public static function __set_state(array $state): self
    {
        return new self(...$state);
    }
}
