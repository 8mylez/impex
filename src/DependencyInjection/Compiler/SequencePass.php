<?php

namespace Dustin\ImpEx\DependencyInjection\Compiler;

use Dustin\ImpEx\DependencyInjection\Exception\EmptyTagAttributeException;
use Dustin\ImpEx\Sequence\Registry\SequenceRegistry;
use Dustin\ImpEx\Util\Value;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SequencePass implements CompilerPassInterface
{
    public const TAG_SECTION = 'impex.sequence.section';

    public const TAG_SEQUENCE_FACTORY = 'impex.sequence.factory';

    public function process(ContainerBuilder $container)
    {
        /** @var Definition $registryDefinition */
        $registryDefinition = $container->findDefinition(SequenceRegistry::class);

        $this->processSections($container, $registryDefinition);
        $this->processSequenceFactories($container, $registryDefinition);
    }

    /**
     * Process all services tagged with 'impex.sequence.section'.
     */
    public function processSections(ContainerBuilder $container, Definition $registryDefinition): void
    {
        /** @var array $taggedRecordHandlers */
        $taggedRecordHandlers = $container->findTaggedServiceIds(self::TAG_SECTION);

        foreach ($taggedRecordHandlers as $recordHandlerId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence', 'priority', 'id']);

                $priority = intval($config['priority']);
                $sequence = trim((string) $config['sequence']);
                $id = trim((string) $config['id']);

                $registryDefinition->addMethodCall('registerSection', [
                    $id,
                    $sequence,
                    $priority,
                    new Reference($recordHandlerId),
                ]);
            }
        }
    }

    /**
     * Process sequence factories tagged with 'impex.sequence.factory'.
     */
    public function processSequenceFactories(ContainerBuilder $container, Definition $registryDefinition): void
    {
        /** @var array $taggedFactories */
        $taggedFactories = $container->findTaggedServiceIds(self::TAG_SEQUENCE_FACTORY);

        foreach ($taggedFactories as $factoryId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence']);

                $sequence = trim((string) $config['sequence']);

                $registryDefinition->addMethodCall('setFactory', [
                    $sequence,
                    new Reference($factoryId),
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
            if (Value::isEmpty($config[$attribute] ?? null)) {
                throw new EmptyTagAttributeException(self::TAG_SECTION, $attribute);
            }
        }
    }
}
