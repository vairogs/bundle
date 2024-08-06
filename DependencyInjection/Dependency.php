<?php declare(strict_types = 1);

namespace Vairogs\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vairogs\Component\Audit\DependencyInjection\AuditConfiguration;
use Vairogs\Component\Cache\DependencyInjection\CacheConfiguration;
use Vairogs\Component\DoctrineTools\DependencyInjection\DoctrineToolsConfiguration;
use Vairogs\Component\Mapper\DependencyInjection\MapperConfiguration;
use Vairogs\Component\Settings\DependencyInjection\SettingsConfiguration;

#[AutoconfigureTag]
interface Dependency
{
    public const string COMPONENT_AUDIT = 'audit';
    public const string COMPONENT_CACHE = 'cache';
    public const string COMPONENT_DOCTRINE = 'doctrine_tools';
    public const string COMPONENT_MAPPER = 'mapper';
    public const string COMPONENT_SETTINGS = 'settings';

    public const array COMPONENTS = [
        self::COMPONENT_AUDIT => AuditConfiguration::class,
        self::COMPONENT_CACHE => CacheConfiguration::class,
        self::COMPONENT_DOCTRINE => DoctrineToolsConfiguration::class,
        self::COMPONENT_MAPPER => MapperConfiguration::class,
        self::COMPONENT_SETTINGS => SettingsConfiguration::class,
    ];

    public function addSection(
        ArrayNodeDefinition $rootNode,
        callable $enableIfStandalone,
        string $component,
    ): void;

    public function registerConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void;

    public function registerPreConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void;

    public function usesDoctrine(): bool;
}
