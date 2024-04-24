<?php
declare(strict_types=1);

namespace Opctim\CspBundle\EventSubscriber;

use Opctim\CspBundle\Event\AddCspHeaderEvent;
use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class CspHeaderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CspHeaderBuilderService  $headerBuilderService,
        private EventDispatcherInterface $eventDispatcher
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $headerKeys = [
            'Content-Security-Policy',
            'X-Content-Security-Policy',
            'X-WebKit-CSP'
        ];

        $addCspHeaderEvent = new AddCspHeaderEvent();

        $this->eventDispatcher->dispatch($addCspHeaderEvent, AddCspHeaderEvent::NAME);

        if ($addCspHeaderEvent->isModified()) {
            $cspHeader = $addCspHeaderEvent->getCspHeaderValue();
        } else {
            $cspHeader = $this->headerBuilderService->build();
        }

        foreach ($headerKeys as $headerKey) {
            $event->getResponse()->headers->set($headerKey, $cspHeader);
        }
    }
}