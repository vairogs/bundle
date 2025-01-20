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

use ApiPlatform\Metadata\ApiProperty;
use ReflectionException;
use Vairogs\Bundle\Constants\BundleContext;
use Vairogs\Bundle\Service\RequestCache;
use Vairogs\Component\Mapper\Exception\MappingException;

use function is_object;

trait _GetReadProperty
{
    /**
     * @throws ReflectionException
     */
    public function getReadProperty(
        object|string $class,
        RequestCache $requestCache,
    ): string {
        if (is_object($class)) {
            $class = $class::class;
        }

        return $requestCache->memoize(BundleContext::READ_PROPERTY, $class, static function () use ($class, $requestCache) {
            $property = null;

            static $_helper = null;

            if (null === $_helper) {
                $_helper = new class {
                    use _LoadReflection;
                };
            }

            foreach ($_helper->loadReflection($class, $requestCache)->getProperties() as $reflectionProperty) {
                if ([] !== ($attributes = $reflectionProperty->getAttributes(ApiProperty::class))) {
                    $prop = $attributes[0]->newInstance();

                    if ($prop->isIdentifier()) {
                        $property = $reflectionProperty->getName();

                        break;
                    }
                }
            }

            if (null === $property) {
                throw new MappingException("Class $class does not have a read property!");
            }

            return $property;
        });
    }
}
