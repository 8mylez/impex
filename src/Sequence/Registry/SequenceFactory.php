<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\Exception\NotASequenceClassException;

class SequenceFactory implements SequenceFactoryInterface
{
    public function build(SequenceConfig $sequenceConfig, array $handlers, array $subSequences, RecordHandlingRegistry $registry): AbstractSequence
    {
        $chain = $this->sortByPriority(array_merge($handlers, $subSequences));
        $handlingChain = $this->buildHandlingChain($chain, $registry);
        $sequenceClass = $this->getSequenceClass($sequenceConfig);

        return new $sequenceClass(...$handlingChain);
    }

    protected function sortByPriority(array $chain): array
    {
        usort($chain, function (PriorityInterface $config1, PriorityInterface $config2) {
            return $config1->getPriority() <=> $config2->getPriority();
        });

        return array_reverse($chain);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function buildHandlingChain(array $chain, RecordHandlingRegistry $registry): array
    {
        return array_map(function ($config) use ($registry) {
            if (\is_object($config) && $config instanceof HandlingConfig) {
                return $config->getHandling();
            }

            if (\is_object($config) && $config instanceof SequenceConfig) {
                return $registry->createRecordHandling($config->getName());
            }

            throw new \InvalidArgumentException(sprintf('Config must be %s or %s. Got %s', HandlingConfig::class, SequenceConfig::class, is_object($config) ? get_class($config) : gettype($config)));
        }, $chain);
    }

    /**
     * @throws NotASequenceClassException
     */
    protected function getSequenceClass(SequenceConfig $config): string
    {
        /** @var string $sequenceClass */
        $sequenceClass = $config->getClass();

        if (!is_subclass_of($sequenceClass, AbstractSequence::class)) {
            throw new NotASequenceClassException($sequenceClass);
        }

        return $sequenceClass;
    }
}
