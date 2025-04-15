<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Bundle\DependencyInjection\Dependency;
use Vairogs\Bundle\DependencyInjection\VairogsConfiguration;
use Vairogs\FullStack;
use Vairogs\Functions\Iteration;
use Vairogs\Functions\Local;

use function array_merge_recursive;
use function class_exists;
use function is_a;
use function is_bool;
use function sprintf;

final class VairogsBundle extends AbstractBundle
{
    public const string VAIROGS = 'vairogs';

    public function build(
        ContainerBuilder $container,
    ): void {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Local\Traits\_Exists;
                use Local\Traits\_WillBeAvailable;
            };
        }

        foreach (Dependency::COMPONENTS as $component => $class) {
            if (!$_helper->exists($class)) {
                continue;
            }

            $package = self::VAIROGS . '/' . $component;
            $object = new $class();

            if (is_a($object, Dependency::class) && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $object->build($container);
            }
        }

        new VairogsConfiguration()->build($container);
    }

    public function configure(
        DefinitionConfigurator $definition,
    ): void {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $definition
            ->rootNode();

        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Local\Traits\_Exists;
                use Local\Traits\_WillBeAvailable;
            };
        }

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) use ($_helper) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = sprintf('%s/bundle', self::VAIROGS);

            return $_helper->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(class: FullStack::class) && $willBeAvailable(package: $package, class: $class) ? 'canBeDisabled' : 'canBeEnabled';

        foreach (Dependency::COMPONENTS as $component => $class) {
            if (!$_helper->exists($class)) {
                continue;
            }

            $package = self::VAIROGS . '/' . $component;
            $object = new $class();

            if (is_a($object, Dependency::class) && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $object->addSection($rootNode, $enableIfStandalone, $component);
            }
        }
    }

    /**
     * @param array<mixed, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Iteration\Traits\_MakeOneDimension;
                use Local\Traits\_Exists;
                use Local\Traits\_WillBeAvailable;
            };
        }

        foreach ($_helper->makeOneDimension([self::VAIROGS => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        foreach (Dependency::COMPONENTS as $component => $class) {
            if (!$_helper->exists($class)) {
                continue;
            }

            $package = self::VAIROGS . '/' . $component;
            $object = new $class();

            if (is_a($object, Dependency::class) && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $enabled = self::p($builder, $component, 'enabled');

                if (!is_bool($enabled) || !$enabled) {
                    continue;
                }

                $object->registerConfiguration($container, $builder, $component);
            }
        }

        new VairogsConfiguration()->registerConfiguration($container, $builder, '');
    }

    public function prependExtension(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $vairogs = new VairogsConfiguration();

        $usesDoctrine = false;

        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Local\Traits\_Exists;
                use Local\Traits\_WillBeAvailable;
            };
        }

        foreach (Dependency::COMPONENTS as $component => $class) {
            if (!$_helper->exists($class)) {
                continue;
            }

            $package = self::VAIROGS . '/' . $component;
            $object = new $class();

            if (is_a($object, Dependency::class) && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $config = self::getConfig(self::VAIROGS, $builder)[$component] ?? [];

                if (true !== ($config['enabled'] ?? false)) {
                    continue;
                }

                $object->registerPreConfiguration($container, $builder, $component);
                $usesDoctrine = $usesDoctrine || $object->usesDoctrine();
            }
        }

        $vairogs->registerPreConfiguration($container, $builder, '');

        if ($usesDoctrine) {
            $vairogs->registerGlobalMigrations($container, $builder);
        }
    }

    /** @return array<mixed, mixed> */
    public static function getConfig(
        string $package,
        ContainerBuilder $builder,
    ): array {
        return array_merge_recursive(...$builder->getExtensionConfig($package));
    }

    public static function p(
        ContainerBuilder $builder,
        string $component,
        string $parameter,
    ): mixed {
        return $builder->getParameter(sprintf('%s.%s.%s', self::VAIROGS, $component, $parameter));
    }
}
