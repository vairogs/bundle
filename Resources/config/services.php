<?php declare(strict_types = 1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (
    ContainerConfigurator $container,
): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load(namespace: 'Vairogs\\Bundle\\', resource: dirname(__DIR__, 2) . '/*')
        ->exclude(excludes: [dirname(__DIR__, 2) . '/{Entity,Resources}']);
};
