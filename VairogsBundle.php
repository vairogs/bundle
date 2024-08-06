<?php declare(strict_types = 1);

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Bundle\DependencyInjection\Dependency;
use Vairogs\Bundle\DependencyInjection\VairogsConfiguration;
use Vairogs\Component\Functions\Iteration;
use Vairogs\Component\Functions\Local;
use Vairogs\FullStack;

use function array_merge_recursive;
use function class_exists;
use function sprintf;

final class VairogsBundle extends AbstractBundle
{
    public const string VAIROGS = 'vairogs';

    public function configure(
        DefinitionConfigurator $definition,
    ): void {
        $rootNode = $definition
            ->rootNode();

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = sprintf('%s/bundle', self::VAIROGS);

            return (new class() {
                use Local\_WillBeAvailable;
            })->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(class: FullStack::class) && $willBeAvailable(package: $package, class: $class) ? 'canBeDisabled' : 'canBeEnabled';

        $available = new class() {
            use Local\_WillBeAvailable;
        };
        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = self::VAIROGS . '/' . $component;
            $object = new $class();
            if ($object instanceof Dependency && $available->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $object->addSection($rootNode, $enableIfStandalone, $component);
            }
        }
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        foreach ((new class() {
            use Iteration\_MakeOneDimension;
        })->makeOneDimension([self::VAIROGS => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        $available = new class() {
            use Local\_WillBeAvailable;
        };
        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = self::VAIROGS . '/' . $component;
            $object = new $class();
            if ($object instanceof Dependency && $available->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                if (!self::p($builder, $component, 'enabled')) {
                    continue;
                }

                $object->registerConfiguration($container, $builder, $component);
            }
        }
    }

    public function prependExtension(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $vairogs = new VairogsConfiguration();

        $usesDoctrine = false;
        $available = new class() {
            use Local\_WillBeAvailable;
        };

        foreach (Dependency::COMPONENTS as $component => $class) {
            $package = self::VAIROGS . '/' . $component;
            $object = new $class();
            if ($object instanceof Dependency && $available->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                $config = self::getConfig(self::VAIROGS, $builder)[$component] ?? [];
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

    public static function p(
        ContainerBuilder $builder,
        string $component,
        string $parameter,
    ): mixed {
        return $builder->getParameter(sprintf('%s.%s.%s', self::VAIROGS, $component, $parameter));
    }

    public static function getConfig(
        string $package,
        ContainerBuilder $builder,
    ): array {
        return array_merge_recursive(...$builder->getExtensionConfig($package));
    }
}
