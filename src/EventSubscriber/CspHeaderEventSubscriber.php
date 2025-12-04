<?php
declare(strict_types=1);

namespace Opctim\CspBundle\EventSubscriber;

use Opctim\CspBundle\Event\AddCspHeaderEvent;
use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CspHeaderEventSubscriber implements EventSubscriberInterface
{
    private string $cspHeader = '';
    private EventDispatcherInterface $eventDispatcher;
    private CspHeaderBuilderService $headerBuilderService;


    public function __construct(
        CspHeaderBuilderService  $headerBuilderService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->headerBuilderService = $headerBuilderService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $addCspHeaderEvent = new AddCspHeaderEvent($event->getRequest());

        $this->eventDispatcher->dispatch($addCspHeaderEvent, AddCspHeaderEvent::NAME);

        if ($addCspHeaderEvent->isModified()) {
            $this->cspHeader = $addCspHeaderEvent->getCspHeaderValue();

            return;
        }

        $this->cspHeader = $this->headerBuilderService->build();
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $headerKeys = [
            'Content-Security-Policy'
        ];

        $response = $event->getResponse();
        $reportingEndpointsHeader = $this->headerBuilderService->buildReportingEndpointsHeader();

        if ($reportingEndpointsHeader) {
            $response->headers->set('Reporting-Endpoints', $reportingEndpointsHeader);
        }

        foreach ($headerKeys as $headerKey) {
            $response->headers->set($headerKey, $this->cspHeader);
        }
    }
}
