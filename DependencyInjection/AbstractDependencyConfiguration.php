<?php declare(strict_types = 1);

namespace Vairogs\Bundle\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vairogs\Bundle\VairogsBundle;

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

    public function registerConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
        string $component,
    ): void {
        $path = dirname((new ReflectionClass(static::class))->getFileName(), 2) . '/Resources/config/services.php';

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
