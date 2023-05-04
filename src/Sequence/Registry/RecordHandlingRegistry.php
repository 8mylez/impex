<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\Exception\MultipleFactoryException;
use Dustin\ImpEx\Sequence\RecordHandling;
use Dustin\ImpEx\Sequence\Sequence;

class RecordHandlingRegistry
{
    /**
     * @var array
     */
    private $recordHandlers = [];

    /**
     * @var array
     */
    private $sequences = [];

    /**
     * @var array
     */
    private $factories = [];

    /**
     * @var SequenceFactory
     */
    private $factory;

    public function __construct()
    {
        $this->factory = new SequenceFactory();
    }

    public function addRecordHandling(RecordHandling $recordHandler, string $sequence, int $priority): void
    {
        $this->recordHandlers[$sequence][] = new HandlingConfig($recordHandler, $sequence, $priority);
    }

    public function addSequence(string $class, string $name, int $priority, string $parent): void
    {
        $this->sequences[$name] = new SequenceConfig(
            $class, $name, $priority, $parent
        );
    }

    /**
     * @throws MultipleFactoryException
     */
    public function addFactory(SequenceFactoryInterface $factory, string $sequence): void
    {
        if (isset($this->factories[$sequence])) {
            throw new MultipleFactoryException($sequence, get_class($factory), get_class($this->factories[$sequence]));
        }

        $this->factories[$sequence] = $factory;
    }

    public function createRecordHandling(string $name): AbstractSequence
    {
        $handlers = isset($this->recordHandlers[$name]) ? $this->recordHandlers[$name] : [];
        $subSequences = $this->getSubSequences($name);
        $config = isset($this->sequences[$name]) ? $this->sequences[$name] : new SequenceConfig(
            Sequence::class, $name, 0, null
        );

        return $this->getFactory($name)->build(
            $config, $handlers, $subSequences, $this
        );
    }

    public function getFactory(string $sequence): SequenceFactoryInterface
    {
        return isset($this->factories[$sequence]) ? $this->factories[$sequence] : $this->factory;
    }

    private function getSubSequences(string $parent): array
    {
        return array_filter($this->sequences, function (SequenceConfig $config) use ($parent) {
            return $config->getParent() === $parent;
        });
    }
}
