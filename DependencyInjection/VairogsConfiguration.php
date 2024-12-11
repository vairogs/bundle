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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class VairogsConfiguration extends AbstractDependencyConfiguration
{
    public function addSection(
        ArrayNodeDefinition $rootNode,
        callable $enableIfStandalone,
        string $component,
    ): void {
    }

    public function registerGlobalMigrations(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($builder->hasExtension('doctrine')) {
            if ($builder->hasExtension('doctrine_migrations')) {
                $container->extension('doctrine_migrations', [
                    'migrations_paths' => [
                        'Vairogs\\Bundle\\Migrations' => __DIR__ . '/../Resources/migrations',
                    ],
                ]);
            }
        }
    }

    public function registerPreConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void {
        if ($builder->hasExtension('framework')) {
            $container->extension('framework', [
                'set_locale_from_accept_language' => true,
            ]);
        }
    }
}
