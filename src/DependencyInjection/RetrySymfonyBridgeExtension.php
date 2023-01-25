<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\SymfonyBridge\DependencyInjection;

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\HandlerExceptionDeclarator\PublicCallbackDeclarator;
use ApacheBorys\Retry\MessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class RetrySymfonyBridgeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/config'));
        $loader->load('services.yaml');

        $configuration = new ConfigSchema();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['handlerExceptionDeclarator'])) {
            $config['handlerExceptionDeclarator']['class'] = PublicCallbackDeclarator::class;
            $config['handlerExceptionDeclarator']['arguments'] = [];
        }

        $definitionForExceptionHandler = new Definition(ExceptionHandler::class, [$config, new Reference(LoggerInterface::class)]);
        $definitionForExceptionHandler->addMethodCall('initHandler');
        $definitionForExceptionHandler->setPublic(true);

        $container->setDefinition(ExceptionHandler::class, $definitionForExceptionHandler);

        $definitionForMessageHandler = new Definition(MessageHandler::class, [$config, new Reference(LoggerInterface::class)]);
        $definitionForMessageHandler->setPublic(true);

        $container->setDefinition(MessageHandler::class, $definitionForMessageHandler);
    }
}