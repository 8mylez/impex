<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\Event\SequenceCreateEvent;
use Dustin\ImpEx\Sequence\RecordHandling;
use Dustin\ImpEx\Sequence\Sequence;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RecordHandlingRegistry
{
    /**
     * @var ObjectMapping
     */
    private $recordHandlers;

    /**
     * @var ObjectMapping
     */
    private $sequences;

    /**
     * @var ObjectMapping
     */
    private $factories;

    /**
     * @var SequenceFactory
     */
    private $defaultFactory;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {
        $this->recordHandlers = ObjectMapping::create(HandlingConfigContainer::class);
        $this->sequences = ObjectMapping::create(SequenceConfig::class);
        $this->factories = ObjectMapping::create(SequenceFactoryInterface::class);
        $this->defaultFactory = new SequenceFactory($this);
    }

    public function addRecordHandling(RecordHandling $recordHandler, string $sequence, int $priority): void
    {
        if (!$this->recordHandlers->has($sequence)) {
            $this->recordHandlers->set($sequence, new HandlingConfigContainer());
        }

        $this->recordHandlers->get($sequence)->add(new HandlingConfig($recordHandler, $sequence, $priority));
    }

    public function addSequence(string $class, string $name, int $priority, ?string $parent): void
    {
        $this->sequences->set($name, new SequenceConfig(
            $class, $name, $priority, $parent
        ));
    }

    public function setFactory(string $sequence, SequenceFactoryInterface $factory): void
    {
        $this->factories->set($sequence, $factory);
    }

    public function createSequence(string $name): AbstractSequence
    {
        $handlers = $this->getRecordHandlers($name);
        $subSequences = $this->getSubSequences($name);
        $config = $this->sequences->has($name) ? clone $this->sequences->get($name) : new SequenceConfig(
            Sequence::class, $name, 0, null
        );

        $event = new SequenceCreateEvent(
            $handlers,
            $subSequences,
            $config
        );

        $this->eventDispatcher->dispatch($event);

        return $this->getFactory($config->getName())->build(
            $config,
            $event->getRecordHandlers(),
            $event->getSubSequences(),
            $this
        );
    }

    public function getFactory(string $sequence): SequenceFactoryInterface
    {
        return $this->factories->get($sequence) ?? $this->defaultFactory;
    }

    private function getSubSequences(string $parent): SequenceConfigContainer
    {
        return (new SequenceConfigContainer($this->sequences->toArray()))
            ->filter(function (SequenceConfig $config) use ($parent) {
                return $config->getParent() === $parent;
            })
            ->map(function (SequenceConfig $config) {
                return clone $config;
            });
    }

    private function getRecordHandlers(string $sequence): HandlingConfigContainer
    {
        if (!$this->recordHandlers->has($sequence)) {
            return new HandlingConfigContainer();
        }

        return $this->recordHandlers->get($sequence)->map(function (HandlingConfig $config) {
            return clone $config;
        });
    }
}
