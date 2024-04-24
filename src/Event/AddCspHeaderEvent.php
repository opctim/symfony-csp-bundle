<?php
declare(strict_types=1);

namespace Opctim\CspBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AddCspHeaderEvent extends Event
{
    public const NAME = 'opctim_csp_bundle.add_csp_header';

    private ?string $cspHeaderValue = null;

    private bool $isModified = false;


    public function getCspHeaderValue(): ?string
    {
        return $this->cspHeaderValue;
    }

    public function setCspHeaderValue(string $cspHeaderValue): void
    {
        $this->isModified = true;

        $this->cspHeaderValue = $cspHeaderValue;
    }

    public function isModified(): bool
    {
        return $this->isModified;
    }
}