<?php declare(strict_types = 1);

namespace Vairogs\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vairogs\Bundle\VairogsBundle;
use Vairogs\Component\Settings\DependencyInjection\SettingsConfiguration;

#[AutoconfigureTag]
interface Dependency
{
    public const string COMPONENT_SETTINGS = 'settings';

    public const array COMPONENTS = [
        VairogsBundle::VAIROGS . '/' . self::COMPONENT_SETTINGS => SettingsConfiguration::class,
    ];

    public function addSection(ArrayNodeDefinition $rootNode, callable $enableIfStandalone): void;

    public function registerConfiguration(ContainerConfigurator $container, ContainerBuilder $builder): void;
}
