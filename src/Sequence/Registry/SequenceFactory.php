<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\Exception\NotASequenceClassException;

class SequenceFactory implements SequenceFactoryInterface
{
    public function __construct(private RecordHandlingRegistry $registry)
    {
    }

    public function build(SequenceConfig $sequenceConfig, HandlingConfigContainer $handlers, SequenceConfigContainer $subSequences): AbstractSequence
    {
        /** @var PriorityContainer $chain */
        $chain = PriorityContainer::merge($handlers, $subSequences);
        $chain->sortByPriority();

        $handlingChain = $this->buildHandlingChain($chain);
        $sequenceClass = $this->getSequenceClass($sequenceConfig);

        return new $sequenceClass(...$handlingChain->toArray());
    }

    protected function buildHandlingChain(Container $chain): Container
    {
        return Container::merge($chain)->map(function (HandlingConfig|SequenceConfig $config) {
            return $config instanceof HandlingConfig ? $config->getHandling() : $this->registry->createSequence($config->getName());
        });
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
