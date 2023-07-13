<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\Event\SequenceCreateEvent;
use Dustin\ImpEx\Sequence\Exception\SectionNotFoundException;
use Dustin\ImpEx\Sequence\Exception\SequenceDefinitionNotFoundException;
use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Factory\SequenceFactory;
use Dustin\ImpEx\Sequence\Registry\Factory\SequenceFactoryInterface;
use Dustin\ImpEx\Sequence\Section;
use Dustin\ImpEx\Sequence\SectionSequence;
use Dustin\ImpEx\Sequence\Sequence;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SequenceRegistry
{
    /**
     * @var ObjectMapping
     */
    private $factories;

    /**
     * @var SequenceFactory
     */
    private $defaultFactory;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private SequenceRepositoryInterface $sequenceRepository,
        private SectionRepositoryInterface $sectionRepository
    ) {
        $this->factories = ObjectMapping::create(SequenceFactoryInterface::class);
        $this->defaultFactory = new SequenceFactory($this);
    }

    public function createSequence(string $id): Sequence
    {
        $definition = $this->sequenceRepository->getSequence($id);

        if ($definition === null) {
            $definition = new SequenceDefinition(['id' => $id, 'class' => SectionSequence::class]);
        }

        $this->eventDispatcher->dispatch(new SequenceCreateEvent($definition));

        return $this->getFactory($definition->getId())->build($definition);
    }

    public function registerSection(string $id, string $sequenceId, int $priority, Section $section): void
    {
        if (!$this->sequenceRepository->hasSequence($sequenceId)) {
            $this->sequenceRepository->addSequence(new SequenceDefinition(['id' => $sequenceId]));
        }

        $this->sequenceRepository->getSequence($sequenceId)->getSections()->add(new SectionDefinition([
            'id' => $id,
            'priority' => $priority,
        ]));

        $this->sectionRepository->addSection($id, $section);
    }

    public function setFactory(string $sequence, SequenceFactoryInterface $factory): void
    {
        $this->factories->set($sequence, $factory);
    }

    public function getFactory(string $sequence): SequenceFactoryInterface
    {
        return $this->factories->get($sequence) ?? $this->defaultFactory;
    }

    public function hasCustomFactory(string $sequence): bool
    {
        return $this->factories->has($sequence);
    }

    public function addSection(string $id, Section $section): void
    {
        $this->sectionRepository->addSection($id, $section);
    }

    public function getSection(string $id): Section
    {
        $section = $this->sectionRepository->getSection($id);

        if ($section === null) {
            throw new SectionNotFoundException($id);
        }

        return $section;
    }

    public function hasSection(string $id): bool
    {
        return $this->sectionRepository->hasSection($id);
    }

    public function addSequence(string $id, string $class): void
    {
        $this->sequenceRepository->addSequence(new SequenceDefinition(['id' => $id, 'class' => $class]));
    }

    public function getSequence(string $id): SequenceDefinition
    {
        $definition = $this->sequenceRepository->getSequence($id);

        if ($definition === null) {
            throw new SequenceDefinitionNotFoundException($id);
        }

        return $definition;
    }

    public function hasSequence(string $id): bool
    {
        return $this->sequenceRepository->hasSequence($id);
    }
}
