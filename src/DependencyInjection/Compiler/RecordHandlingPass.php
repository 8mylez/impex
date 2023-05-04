<?php

namespace Dustin\ImpEx\DependencyInjection\Compiler;

use Dustin\ImpEx\DependencyInjection\Exception\EmptyTagAttributeException;
use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\Exception\NotASequenceClassException;
use Dustin\ImpEx\Sequence\Registry\RecordHandlingRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RecordHandlingPass implements CompilerPassInterface
{
    public const TAG_HANDLING = 'impex.handling';

    public const TAG_HANDLING_SEQUENCE = 'impex.handling.sequence';

    public const TAG_HANDLING_FACTORY = 'impex.handling.factory';

    public function process(ContainerBuilder $container)
    {
        /** @var Definition $registryDefinition */
        $registryDefinition = $container->findDefinition(RecordHandlingRegistry::class);

        $this->processHandlings($container, $registryDefinition);
        $this->processHandlingSequences($container, $registryDefinition);
        $this->processHandlingFactories($container, $registryDefinition);
    }

    /**
     * Process all services tagged with 'impex.handling'.
     */
    public function processHandlings(ContainerBuilder $container, Definition $registryDefinition): void
    {
        /** @var array $taggedRecordHandlers */
        $taggedRecordHandlers = $container->findTaggedServiceIds(self::TAG_HANDLING);

        foreach ($taggedRecordHandlers as $recordHandlerId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence', 'priority']);

                $priority = intval($config['priority']);
                $sequence = trim((string) $config['sequence']);

                $registryDefinition->addMethodCall('addRecordHandling', [
                    new Reference($recordHandlerId),
                    $sequence,
                    $priority,
                ]);
            }
        }
    }

    /**
     * Process sequences tagged with 'impex.handling.sequence'.
     *
     * @throws NotASequenceClassException
     */
    public function processHandlingSequences(ContainerBuilder $container, Definition $registryDefinition): void
    {
        /** @var array $taggedSequences */
        $taggedSequences = $container->findTaggedServiceIds(self::TAG_HANDLING_SEQUENCE);

        foreach ($taggedSequences as $sequenceId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence', 'parent', 'priority', 'class']);

                $sequence = trim((string) $config['sequence']);
                $priority = intval($config['priority']);
                $parent = trim((string) $config['parent']);
                $class = trim((string) $config['class']);

                if ($parent == $sequence) {
                    throw new \LogicException(sprintf("Sequence name and parent cannot be the same! '%s' given.", $sequence));
                }

                if (!is_subclass_of($class, AbstractSequence::class)) {
                    throw new NotASequenceClassException($class);
                }

                $registryDefinition->addMethodCall('addSequence', [
                    $class, $sequence, $priority, $parent,
                 ]);
            }

            $container->removeDefinition($sequenceId);
        }
    }

    /**
     * Process sequence factories tagged with 'impex.handling.factory'.
     */
    public function processHandlingFactories(ContainerBuilder $container, Definition $registryDefinition): void
    {
        /** @var array $taggedFactories */
        $taggedFactories = $container->findTaggedServiceIds(self::TAG_HANDLING_FACTORY);

        foreach ($taggedFactories as $factoryId => $tags) {
            foreach ((array) $tags as $config) {
                $config = (array) $config;

                $this->validateTagAttributes($config, ['sequence']);

                $sequence = trim((string) $config['sequence']);

                $registryDefinition->addMethodCall('addFactory', [
                    new Reference($factoryId),
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
                throw new EmptyTagAttributeException(self::TAG_HANDLING, $attribute);
            }
        }
    }
}
