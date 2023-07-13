<?php

namespace Dustin\ImpEx;

use Dustin\ImpEx\DependencyInjection\Compiler\SequencePass;
use Dustin\ImpEx\DependencyInjection\Compiler\TransferorPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ImpExBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SequencePass());
        $container->addCompilerPass(new TransferorPass());

        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load($this->getPath().'/DependencyInjection/services.xml');
    }
}
