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
use Vairogs\Bundle\VairogsBundle;
use Vairogs\Functions\Php;

use function dirname;
use function is_file;
use function sprintf;

abstract class AbstractDependencyConfiguration implements Dependency
{
    public function addSection(
        ArrayNodeDefinition $rootNode,
        callable $enableIfStandalone,
        string $component,
    ): void {
        $rootNode
            ->children()
                ->arrayNode($component)
                    ->{$enableIfStandalone(sprintf('%s/%s', VairogsBundle::VAIROGS, $component), self::class)}()
                ->end();
    }

    public function build(
        ContainerBuilder $container,
    ): void {
    }

    public function registerConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void {
        static $_helper = null;

        if (null === $_helper) {
            $_helper = new class {
                use Php\Traits\_GetReflection;
            };
        }

        $path = dirname($_helper->getReflection(static::class)->getFileName(), 2) . '/Resources/config/services.php';

        if (is_file($path)) {
            $container->import($path);
        }
    }

    public function registerPreConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void {
    }

    public function usesDoctrine(): bool
    {
        return false;
    }
}
