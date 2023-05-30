<?php
declare(strict_types=1);

namespace ApacheBorys\Retry\SymfonyBridge\DependencyInjection;

use ApacheBorys\Retry\ExceptionHandler;
use ApacheBorys\Retry\HandlerExceptionDeclarator\PublicCallbackDeclarator;
use ApacheBorys\Retry\HandlerFactory;
use ApacheBorys\Retry\MessageHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class RetryExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/config'));
        $loader->load('services.yaml');

        $configuration = new ConfigSchema();
        $config = $this->processConfiguration($configuration, $configs);

        $this->normalizeConfig($config);

        $container->setDefinition(
            'retry.exception_handler_factory',
            new Definition(HandlerFactory::class, [$config, new Reference(LoggerInterface::class)])
        );

        $container->setDefinition(ExceptionHandler::class, $this->defineExceptionHandler());

        $container->setDefinition(MessageHandler::class, $this->defineMessageHandler());
    }

    private function normalizeConfig(array &$config): void
    {
        if (!isset($config['handlerExceptionDeclarator'])) {
            $config['handlerExceptionDeclarator']['class'] = PublicCallbackDeclarator::class;
            $config['handlerExceptionDeclarator']['arguments'] = [];
        }
    }

    private function defineExceptionHandler(): Definition
    {
        $exceptionHandlerDefinition = new Definition(ExceptionHandler::class);
        $exceptionHandlerDefinition->setFactory([new Reference('retry.exception_handler_factory'), 'createExceptionHandler']);
        $exceptionHandlerDefinition->addArgument(new Reference(ContainerInterface::class));
        $exceptionHandlerDefinition->setPublic(true);

        return $exceptionHandlerDefinition;
    }

    private function defineMessageHandler(): Definition
    {
        $messageHandlerDefinition = new Definition(MessageHandler::class);
        $messageHandlerDefinition->setFactory([new Reference('retry.exception_handler_factory'), 'createMessageHandler']);
        $messageHandlerDefinition->addArgument(new Reference(ContainerInterface::class));
        $messageHandlerDefinition->setPublic(true);

        return $messageHandlerDefinition;
    }
}