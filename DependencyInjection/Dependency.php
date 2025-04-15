<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vairogs\Component\CacheWarmer\DependencyInjection\CacheWarmerConfiguration;
use Vairogs\Component\DoctrineTools\DependencyInjection\DoctrineToolsConfiguration;
use Vairogs\Component\Mapper\DependencyInjection\MapperConfiguration;
use Vairogs\Component\SpxProfiler\DependencyInjection\SpxProfilerConfiguration;

#[AutoconfigureTag]
interface Dependency
{
    public const array COMPONENTS = [
        self::COMPONENT_CACHE_WARMER => CacheWarmerConfiguration::class,
        self::COMPONENT_DOCTRINE => DoctrineToolsConfiguration::class,
        self::COMPONENT_MAPPER => MapperConfiguration::class,
        self::COMPONENT_SPX_PROFILER => SpxProfilerConfiguration::class,
    ];

    public const string COMPONENT_CACHE_WARMER = 'cache_warmer';
    public const string COMPONENT_DOCTRINE = 'doctrine_tools';
    public const string COMPONENT_MAPPER = 'mapper';
    public const string COMPONENT_SPX_PROFILER = 'spx_profiler';

    public function addSection(
        ArrayNodeDefinition $rootNode,
        callable $enableIfStandalone,
        string $component,
    ): void;

    public function build(
        ContainerBuilder $container,
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
