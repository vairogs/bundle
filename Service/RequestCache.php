<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Vairogs\Bundle\Collection\SimpleArrayCollection;
use Vairogs\Bundle\Collection\SimpleObjectCollection;
use Vairogs\Bundle\Constants\Context;
use Vairogs\Bundle\Contracts\SimpleCollection;

#[Autoconfigure(public: true)]
final readonly class RequestCache
{
    private SimpleCollection $cache;

    public function __construct(
        private bool $useObject = true,
    ) {
        $this->cache = $this->new();
    }

    public function cache(
        Context $cacheContext,
    ): SimpleCollection {
        if (!$this->cache->containsKey($cacheContext->value)) {
            $this->cache->set($cacheContext->value, $this->new());
        }

        return $this->cache->get($cacheContext->value);
    }

    public function get(
        Context $cacheContext,
        string $key,
        callable $callback,
        string ...$subkeys,
    ): mixed {
        $cache = $this->cache($cacheContext);

        $currentKey = $key;
        foreach ($subkeys as $subkey) {
            if (!$cache->containsKey($currentKey)) {
                $cache->set($currentKey, $this->new());
            }
            $cache = $cache->get($currentKey);
            $currentKey = $subkey;
        }

        if (!$cache->containsKey($currentKey)) {
            $cache->set($currentKey, $callback());
        }

        return $cache->get($currentKey);
    }

    public function getValue(
        Context $cacheContext,
        string $key,
        mixed $default = null,
        string ...$subkeys,
    ): mixed {
        $cache = $this->cache($cacheContext);

        $currentKey = $key;

        foreach ($subkeys as $subkey) {
            if (!$cache->containsKey($currentKey)) {
                return $default;
            }
            $cache = $cache->get($currentKey);
            $currentKey = $subkey;
        }

        return $cache->containsKey($currentKey) ? $cache->get($currentKey) : $default;
    }

    private function new(): SimpleCollection
    {
        return $this->useObject ? new SimpleObjectCollection() : new SimpleArrayCollection();
    }
}
