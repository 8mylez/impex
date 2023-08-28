<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\Event\RegisterSectionEvent;
use Dustin\ImpEx\Sequence\Event\SequenceCreateEvent;
use Dustin\ImpEx\Sequence\Exception\SectionNotFoundException;
use Dustin\ImpEx\Sequence\Exception\SequenceDefinitionNotFoundException;
use Dustin\ImpEx\Sequence\Registry\Config\PlaceholderSequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\Factory\SequenceFactory;
use Dustin\ImpEx\Sequence\Registry\Factory\SequenceFactoryInterface;
use Dustin\ImpEx\Sequence\Section;
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
            $definition = new PlaceholderSequenceDefinition(['id' => $id]);
        }

        $this->eventDispatcher->dispatch(new SequenceCreateEvent($definition));

        $definition = $this->updateSequenceDefinition($definition, $id);

        return $this->getSequenceFactory($definition->getId())->build($definition);
    }

    public function registerSection(string $id, string $sequenceId, int $priority, Section $section): void
    {
        $definition = $this->sequenceRepository->getSequence($sequenceId);

        if ($definition === null) {
            $definition = new PlaceholderSequenceDefinition(['id' => $sequenceId]);
        }

        $event = new RegisterSectionEvent($id, $priority, $section, $definition);
        $this->eventDispatcher->dispatch($event);

        $definition->getSections()->add(new SectionDefinition([
            'id' => $event->getSectionId(),
            'section' => $event->getSection(),
            'type' => SectionDefinition::TYPE_SECTION,
            'priority' => $event->getPriority(),
        ]));

        try {
            $definition = $this->updateSequenceDefinition($definition, $sequenceId);
        } catch (SequenceDefinitionNotFoundException $e) {
        }

        $this->sequenceRepository->addSequence($definition);
        $this->sectionRepository->addSection($event->getSectionId(), $event->getSection());
    }

    public function setSequenceFactory(string $sequence, SequenceFactoryInterface $factory): void
    {
        $this->factories->set($sequence, $factory);
    }

    public function getSequenceFactory(string $sequence): SequenceFactoryInterface
    {
        return $this->factories->get($sequence) ?? $this->defaultFactory;
    }

    public function hasCustomSequenceFactory(string $sequence): bool
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

    public function registerSequence(string $id, string $class): void
    {
        $definition = new SequenceDefinition(['id' => $id, 'class' => $class]);
        $existingDefinition = $this->sequenceRepository->getSequence($id);

        if ($existingDefinition !== null) {
            $definition->getSections()->add(...$existingDefinition->getSections()->toArray());
        }

        $this->sequenceRepository->addSequence($definition);
    }

    public function getSequenceDefinition(string $id): SequenceDefinition
    {
        $definition = $this->sequenceRepository->getSequence($id);

        if ($definition === null || $definition instanceof PlaceholderSequenceDefinition) {
            throw new SequenceDefinitionNotFoundException($id);
        }

        return $definition;
    }

    public function hasSequenceDefinition(string $id): bool
    {
        $definition = $this->sequenceRepository->getSequence($id);

        return $definition !== null && !($definition instanceof PlaceholderSequenceDefinition);
    }

    private function updateSequenceDefinition(SequenceDefinition $definition, string $requestedId): SequenceDefinition
    {
        if (!$definition instanceof PlaceholderSequenceDefinition) {
            return $definition;
        }

        if (!$definition->isConfigured()) {
            throw new SequenceDefinitionNotFoundException($requestedId);
        }

        $this->registerSequence($definition->getId(), $definition->getClass());

        $newDefinition = $this->getSequenceDefinition($definition->getId());
        $newDefinition->setSections($definition->getSections());

        return $newDefinition;
    }
}
