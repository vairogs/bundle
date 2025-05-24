<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Traits;

use ReflectionClass;
use ReflectionException;
use Vairogs\Bundle\Constants\BundleContext;
use Vairogs\Functions\Memoize\MemoizeCache;
use Vairogs\Functions\Php;

use function is_object;

trait _LoadReflection
{
    /**
     * @throws ReflectionException
     */
    public function loadReflection(
        object|string $objectOrClass,
        MemoizeCache $memoize,
    ): ReflectionClass {
        $class = $objectOrClass;

        if (is_object($objectOrClass)) {
            $class = $objectOrClass::class;
        }

        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Php\Traits\_GetReflection;
            };
        }

        $reflection = $memoize->memoize(BundleContext::REFLECTION, $class, static fn () => $_helper->getReflection($objectOrClass));
        $memoize->memoize(BundleContext::REFLECTION, $reflection->getName(), static fn () => $reflection);

        return $reflection;
    }
}
