<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\EventSubscriber;

use Opctim\CspBundle\Event\AddCspHeaderEvent;
use Opctim\CspBundle\EventSubscriber\CspHeaderEventSubscriber;
use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Opctim\CspBundle\Service\CspNonceService;
use Opctim\CspBundle\Tests\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CspHeaderEventSubscriberTest extends TestCase
{
    public function test(): void
    {
        $nonceService = new CspNonceService();

        $cspHeaderBuilderService = new CspHeaderBuilderService(
            $nonceService,
            [ 'alwaysThere' ],
            [
                'test1' => [
                    'origin1',
                    'origin2',
                    'nonce(test)',
                ],
                'test2' => [
                    'origin1',
                    'origin2',
                ]
            ]
        );

        $eventDispatcher = new EventDispatcher();

        $inlineSubscriber = new class implements EventSubscriberInterface {

            public bool $received = false;

            public static function getSubscribedEvents(): array
            {
                return [
                    AddCspHeaderEvent::NAME => 'onAdd'
                ];
            }

            public function onAdd(AddCspHeaderEvent $event): void
            {
                if ($this->received) {
                    $event->setCspHeaderValue('TEST-OVERRIDE');
                }

                $this->received = true;
            }
        };

        $eventDispatcher->addSubscriber($inlineSubscriber);

        $subscriber = new CspHeaderEventSubscriber($cspHeaderBuilderService, $eventDispatcher);

        $responseEvent = new ResponseEvent(
            new TestKernel('test', true),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response('')
        );

        $subscriber->onKernelResponse($responseEvent);

        self::assertTrue($inlineSubscriber->received);

        $headerKeys = [
            'Content-Security-Policy',
            'X-Content-Security-Policy',
            'X-WebKit-CSP'
        ];

        foreach ($headerKeys as $headerKey) {
            self::assertEquals(
                'test1 alwaysThere origin1 origin2 nonce-' . $nonceService->getNonce('test') . '; test2 alwaysThere origin1 origin2;',
                $responseEvent->getResponse()->headers->get($headerKey)
            );
        }

        $responseEvent = new ResponseEvent(
            new TestKernel('test', true),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response('')
        );

        $subscriber->onKernelResponse($responseEvent);

        $headerKeys = [
            'Content-Security-Policy',
            'X-Content-Security-Policy',
            'X-WebKit-CSP'
        ];

        foreach ($headerKeys as $headerKey) {
            self::assertEquals(
                'TEST-OVERRIDE',
                $responseEvent->getResponse()->headers->get($headerKey)
            );
        }
    }
}