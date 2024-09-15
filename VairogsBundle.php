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

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Bundle\DependencyInjection\Dependency;
use Vairogs\Bundle\DependencyInjection\VairogsConfiguration;
use Vairogs\Component\Functions\Iteration;
use Vairogs\Component\Functions\Local;
use Vairogs\Component\Functions\Vairogs;
use Vairogs\FullStack;

use function array_merge_recursive;
use function class_exists;
use function sprintf;

final class VairogsBundle extends AbstractBundle
{
    public function build(
        ContainerBuilder $container,
    ): void {
        foreach (Dependency::COMPONENTS as $class) {
            $object = new $class();

            if ($object instanceof Dependency) {
                $object->build();
            }
        }
    }

    public function configure(
        DefinitionConfigurator $definition,
    ): void {
        $rootNode = $definition
            ->rootNode();

        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Local\_WillBeAvailable;
            };
        }

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) use ($_helper) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = sprintf('%s/bundle', Vairogs::VAIROGS);

            return $_helper->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(class: FullStack::class) && $willBeAvailable(package: $package, class: $class) ? 'canBeDisabled' : 'canBeEnabled';

        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = Vairogs::VAIROGS . '/' . $component;
            $object = new $class();

            if ($object instanceof Dependency && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', Vairogs::VAIROGS)])) {
                $object->addSection($rootNode, $enableIfStandalone, $component);
            }
        }
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Iteration\_MakeOneDimension;
                use Local\_WillBeAvailable;
            };
        }

        foreach ($_helper->makeOneDimension([Vairogs::VAIROGS => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = Vairogs::VAIROGS . '/' . $component;
            $object = new $class();

            if ($object instanceof Dependency && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', Vairogs::VAIROGS)])) {
                if (!self::p($builder, $component, 'enabled')) {
                    continue;
                }

                $object->registerConfiguration($container, $builder, $component);
            }
        }

        (new VairogsConfiguration())->registerConfiguration($container, $builder, '');
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
                use Local\_WillBeAvailable;
            };
        }

        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = Vairogs::VAIROGS . '/' . $component;
            $object = new $class();

            if ($object instanceof Dependency && $_helper->willBeAvailable($package, $class, [sprintf('%s/bundle', Vairogs::VAIROGS)])) {
                $config = self::getConfig(Vairogs::VAIROGS, $builder)[$component] ?? [];

                if (true !== ($config['enabled'] ?? false)) {
                    continue;
                }

                $object->registerPreConfiguration($container, $builder, $component);
                $usesDoctrine = $usesDoctrine || $object->usesDoctrine();
            }
        }

        if ($usesDoctrine) {
            $vairogs->registerGlobalMigrations($container, $builder);
        }
    }

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
        return $builder->getParameter(sprintf('%s.%s.%s', Vairogs::VAIROGS, $component, $parameter));
    }
}
