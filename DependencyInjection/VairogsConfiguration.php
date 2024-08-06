<?php declare(strict_types = 1);

namespace Vairogs\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function dirname;

final class VairogsConfiguration extends AbstractDependencyConfiguration
{
    public function registerGlobalMigrations(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($builder->hasExtension('doctrine')) {
            if ($builder->hasExtension('doctrine_migrations')) {
                $container->extension('doctrine_migrations', [
                    'migrations_paths' => [
                        'Vairogs\\Bundle\\Migrations' => dirname(__DIR__) . '/Resources/migrations',
                    ],
                ]);
            }
        }
    }

    public function addSection(
        ArrayNodeDefinition $rootNode,
        callable $enableIfStandalone,
        string $component,
    ): void {
    }
}
