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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
final class RequestCache
{
    private ArrayCollection $cache;

    public function __construct()
    {
        $this->cache = new ArrayCollection();
    }

    public function cache(
        string $cacheContext,
    ): ArrayCollection {
        if (!$this->cache->containsKey($cacheContext)) {
            $this->cache->set($cacheContext, new ArrayCollection());
        }

        return $this->cache->get($cacheContext);
    }

    public function get(
        string $cacheContext,
        string $key,
        callable $callback,
    ): mixed {
        $cache = $this->cache($cacheContext);

        if (!$cache->containsKey($key)) {
            $cache->set($key, $callback());
        }

        return $cache->get($key);
    }
}
