<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Collection;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Vairogs\Bundle\Contracts\SimpleCollection;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_search;
use function array_slice;
use function count;
use function in_array;

use const ARRAY_FILTER_USE_BOTH;

final class SimpleArrayCollection implements Countable, IteratorAggregate, ArrayAccess, SimpleCollection
{
    public function __construct(
        private array $elements = [],
    ) {
    }

    public function add(
        mixed $element,
    ): bool {
        $this->elements[] = $element;

        return true;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function contains(
        mixed $element,
    ): bool {
        return in_array($element, $this->elements, true);
    }

    public function containsKey(
        int|string $key,
    ): bool {
        return array_key_exists($key, $this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function exists(
        callable $predicate,
    ): bool {
        foreach ($this->elements as $key => $element) {
            if ($predicate($key, $element)) {
                return true;
            }
        }

        return false;
    }

    public function filter(
        callable $predicate,
    ): self {
        return new self(array_filter($this->elements, $predicate, ARRAY_FILTER_USE_BOTH));
    }

    public function forAll(
        callable $predicate,
    ): bool {
        foreach ($this->elements as $key => $element) {
            if (!$predicate($key, $element)) {
                return false;
            }
        }

        return true;
    }

    public function get(
        int|string $key,
    ): mixed {
        return $this->elements[$key] ?? null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function indexOf(
        mixed $element,
    ): int|string|false {
        return array_search($element, $this->elements, true);
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function map(
        callable $func,
    ): self {
        return new self(array_map($func, $this->elements));
    }

    public function offsetExists(
        mixed $offset,
    ): bool {
        return array_key_exists($offset, $this->elements);
    }

    public function offsetGet(
        mixed $offset,
    ): mixed {
        return $this->elements[$offset] ?? null;
    }

    public function offsetSet(
        mixed $offset,
        mixed $value,
    ): void {
        if (null === $offset) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    public function offsetUnset(
        mixed $offset,
    ): void {
        unset($this->elements[$offset]);
    }

    public function partition(
        callable $predicate,
    ): array {
        $matches = [];
        $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($predicate($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [new self($matches), new self($noMatches)];
    }

    public function remove(
        int|string $key,
    ): mixed {
        if (array_key_exists($key, $this->elements)) {
            $removedElement = $this->elements[$key];
            unset($this->elements[$key]);

            return $removedElement;
        }

        return null;
    }

    public function removeElement(
        mixed $element,
    ): bool {
        $key = array_search($element, $this->elements, true);

        if (false !== $key) {
            unset($this->elements[$key]);

            return true;
        }

        return false;
    }

    public function set(
        int|string $key,
        mixed $value,
    ): void {
        $this->elements[$key] = $value;
    }

    public function slice(
        int $offset,
        ?int $length = null,
    ): array {
        return array_slice($this->elements, $offset, $length, true);
    }

    public function toArray(): array
    {
        return $this->elements;
    }
}
