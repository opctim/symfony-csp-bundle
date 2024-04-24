<?php

namespace Opctim\CspBundle;

use Opctim\CspBundle\DependencyInjection\OpctimCspBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpctimCspBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new OpctimCspBundleExtension();
    }

    public function getPath(): string
    {
        return __DIR__;
    }
}
