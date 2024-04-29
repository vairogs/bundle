<?php declare(strict_types = 1);

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Bundle\DependencyInjection\Dependency;
use Vairogs\Component\Functions\Iteration;
use Vairogs\Component\Functions\Local;
use Vairogs\FullStack;

use function class_exists;
use function sprintf;

final class VairogsBundle extends AbstractBundle
{
    public const string VAIROGS = 'vairogs';
    public const string ENABLED = 'enabled';

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
        foreach (Dependency::COMPONENTS as $package => $class) {
            if ($available->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                (new $class())->addSection($rootNode, $enableIfStandalone);
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
        foreach (Dependency::COMPONENTS as $package => $class) {
            if ($available->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                (new $class())->registerConfiguration($container, $builder);
            }
        }
    }

    public static function p(
        ContainerBuilder $builder,
        string $component,
        string $parameter,
    ): mixed {
        return $builder->getParameter(sprintf('%s.%s.%s', self::VAIROGS, $component, $parameter));
    }

    public static function enabled(
        ContainerBuilder $builder,
        string $component,
    ): bool {
        return self::p($builder, $component, self::ENABLED);
    }
}
