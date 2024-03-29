<?php declare(strict_types = 1);

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Bundle\DependencyInjection\Dependency;
use Vairogs\Component\Functions\Composer;
use Vairogs\Component\Functions\Iteration;
use Vairogs\FullStack;

use function class_exists;
use function sprintf;

final class VairogsBundle extends AbstractBundle
{
    public const string VAIROGS = 'vairogs';
    public const string ENABLED = 'enabled';

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition
            ->rootNode();

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = sprintf('%s/bundle', self::VAIROGS);

            return (new Composer())->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(FullStack::class) && $willBeAvailable($package, $class) ? 'canBeDisabled' : 'canBeEnabled';

        foreach (Dependency::COMPONENTS as $package => $class) {
            if ((new Composer())->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                (new $class())->addSection($rootNode, $enableIfStandalone);
            }
        }
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ((new Iteration())->makeOneDimension([self::VAIROGS => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        foreach (Dependency::COMPONENTS as $package => $class) {
            if ((new Composer())->willBeAvailable($package, $class, [sprintf('%s/bundle', self::VAIROGS)])) {
                (new $class())->registerConfiguration($container, $builder);
            }
        }
    }

    public static function componentEnabled(ContainerBuilder $builder, string $component): bool
    {
        return $builder->getParameter(sprintf('%s.%s.%s', self::VAIROGS, $component, self::ENABLED));
    }
}
