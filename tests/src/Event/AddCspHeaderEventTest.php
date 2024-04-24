<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Tests\Event;

use Opctim\CspBundle\Event\AddCspHeaderEvent;
use PHPUnit\Framework\TestCase;

class AddCspHeaderEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new AddCspHeaderEvent();

        self::assertFalse($event->isModified());
        self::assertNull($event->getCspHeaderValue());

        $event->setCspHeaderValue('test');

        self::assertTrue($event->isModified());
        self::assertEquals('test', $event->getCspHeaderValue());
    }
}