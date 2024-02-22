<?php declare(strict_types = 1);

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Component\Functions\Composer;
use Vairogs\Component\Functions\Iteration;
use Vairogs\Component\Settings\DependencyInjection\SettingsConfiguration;
use Vairogs\FullStack;

use function class_exists;

final class VairogsBundle extends AbstractBundle
{
    private const array COMPONENTS = [
        'vairogs/settings' => SettingsConfiguration::class,
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition
            ->rootNode();

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = 'vairogs/bundle';

            return (new Composer())->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(FullStack::class) && $willBeAvailable($package, $class) ? 'canBeDisabled' : 'canBeEnabled';

        foreach (self::COMPONENTS as $package => $class) {
            if ((new Composer())->willBeAvailable($package, $class, ['vairogs/bundle'])) {
                (new $class())->addSection($rootNode, $enableIfStandalone);
            }
        }
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ((new Iteration())->makeOneDimension(['vairogs' => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        foreach (self::COMPONENTS as $package => $class) {
            if ((new Composer())->willBeAvailable($package, $class, ['vairogs/bundle'])) {
                (new $class())->registerConfiguration($container, $builder);
            }
        }
    }
}
