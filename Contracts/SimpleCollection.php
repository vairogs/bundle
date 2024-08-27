<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Contracts;

use ArrayIterator;

interface SimpleCollection
{
    public function add(
        mixed $element,
    ): bool;

    public function clear(): void;

    public function contains(
        mixed $element,
    ): bool;

    public function containsKey(
        int|string $key,
    ): bool;

    public function count(): int;

    public function exists(
        callable $predicate,
    ): bool;

    public function filter(
        callable $predicate,
    ): self;

    public function forAll(
        callable $predicate,
    ): bool;

    public function get(
        int|string $key,
    ): mixed;

    public function getIterator(): ArrayIterator;

    public function indexOf(
        mixed $element,
    ): int|string|false;

    public function isEmpty(): bool;

    public function map(
        callable $func,
    ): self;

    public function offsetExists(
        mixed $offset,
    ): bool;

    public function offsetGet(
        mixed $offset,
    ): mixed;

    public function offsetSet(
        mixed $offset,
        mixed $value,
    ): void;

    public function offsetUnset(
        mixed $offset,
    ): void;

    public function partition(
        callable $predicate,
    ): array;

    public function remove(
        int|string $key,
    ): mixed;

    public function removeElement(
        mixed $element,
    ): bool;

    public function set(
        int|string $key,
        mixed $value,
    ): void;

    public function slice(
        int $offset,
        ?int $length = null,
    ): array|self;

    public function toArray(): array;
}
