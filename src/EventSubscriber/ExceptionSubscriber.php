<?php
declare(strict_types=1);

namespace App\Bundle\EventSubscriber;

use ApacheBorys\Retry\ExceptionHandler;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private ExceptionHandler $exceptionHandler;

    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
            ],
            ConsoleEvents::ERROR => [
                ['processConsoleError', 10],
            ]
        ];
    }

    public function processException(ExceptionEvent $event): void
    {
        $this->exceptionHandler->getDeclarator()->getCallback()($event->getThrowable());
    }

    public function processConsoleError(ConsoleErrorEvent $event): void
    {
        $this->exceptionHandler->getDeclarator()->getCallback()($event->getError());
    }
}
