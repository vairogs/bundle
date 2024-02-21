<?php declare(strict_types = 1);

namespace Vairogs\Bundle;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vairogs\Component\Functions\Composer;
use Vairogs\Component\Functions\Iteration;
use Vairogs\Settings\DependencyInjection\SettingsConfiguration;
use function class_exists;
use Vairogs\FullStack;

final class VairogsBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void {
        $rootNode = $definition
            ->rootNode();

        $willBeAvailable = static function (string $package, string $class, ?string $parentPackage = null) {
            $parentPackages = (array) $parentPackage;
            $parentPackages[] = 'vairogs/bundle';

            return (new Composer())->willBeAvailable($package, $class, $parentPackages);
        };

        $enableIfStandalone = static fn (string $package, string $class) => !class_exists(FullStack::class) && $willBeAvailable($package, $class) ? 'canBeDisabled' : 'canBeEnabled';

        $this->addSettingsSection($rootNode, $enableIfStandalone);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ((new Iteration())->makeOneDimension(['vairogs' => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        $this->registerSettingsConfiguration($container, $builder);
    }

    private function addSettingsSection(ArrayNodeDefinition $rootNode, callable $enableIfStandalone):void
    {
        if((new Composer())->willBeAvailable('vairogs/settings', SettingsConfiguration::class, ['vairogs/bundle'])){
            (new SettingsConfiguration())->addSection($rootNode, $enableIfStandalone);
        }
    }

    private function registerSettingsConfiguration(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if((new Composer())->willBeAvailable('vairogs/settings', SettingsConfiguration::class, ['vairogs/bundle'])){
            (new SettingsConfiguration())->registerConfiguration($container, $builder);
        }
    }
}
