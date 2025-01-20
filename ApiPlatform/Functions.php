<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\ApiPlatform;

use ApiPlatform\Metadata\ApiResource;
use ReflectionException;
use Vairogs\Bundle\Service\RequestCache;
use Vairogs\Bundle\Traits\_LoadReflection;
use Vairogs\Component\Mapper\Attribute\SimpleApiResource;

readonly class Functions
{
    public function __construct(
        private RequestCache $requestCache,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function isResource(
        object|string $object,
    ): bool {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use _LoadReflection;
            };
        }

        $reflection = $_helper->loadReflection($object, $this->requestCache);

        return [] !== $reflection->getAttributes(ApiResource::class) || [] !== $reflection->getAttributes(SimpleApiResource::class);
    }
}
