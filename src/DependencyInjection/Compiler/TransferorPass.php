<?php

namespace Dustin\ImpEx\DependencyInjection\Compiler;

use Dustin\ImpEx\DependencyInjection\Exception\EmptyTagAttributeException;
use Dustin\ImpEx\Sequence\Registry\TransferorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TransferorPass implements CompilerPassInterface
{
    public const TAG_TRANSFEROR = 'impex.handling.transferor';

    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->findDefinition(TransferorRegistry::class);

        $this->processTransferors($container, $registryDefinition);
    }

    /**
     * Process alls services tagged with 'impex.handling.transferor'.
     */
    public function processTransferors(ContainerBuilder $container, Definition $registryDefinition): void
    {
        $taggedTransferors = $container->findTaggedServiceIds(self::TAG_TRANSFEROR);

        foreach ($taggedTransferors as $transferorId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence']);

                $sequence = trim((string) $config['sequence']);

                $registryDefinition->addMethodCall('addTransferor', [
                    new Reference($transferorId),
                    $sequence,
                ]);
            }
        }
    }

    /**
     * @throws EmptyTagAttributeException
     */
    protected function validateTagAttributes(array $config, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (empty($config[$attribute]) || empty(trim((string) $config[$attribute]))) {
                throw new EmptyTagAttributeException(self::TAG_TRANSFEROR, $attribute);
            }
        }
    }
}
