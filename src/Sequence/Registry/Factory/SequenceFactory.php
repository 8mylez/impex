<?php

namespace Dustin\ImpEx\Sequence\Registry\Factory;

use Dustin\ImpEx\Sequence\Exception\SequenceBuildException;
use Dustin\ImpEx\Sequence\Registry\Config\SectionDefinition;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\SequenceRegistry;
use Dustin\ImpEx\Sequence\Registry\Validation\SequenceClassValidator;
use Dustin\ImpEx\Sequence\Sequence;

class SequenceFactory implements SequenceFactoryInterface
{
    public function __construct(private SequenceRegistry $registry)
    {
    }

    public function build(SequenceDefinition $sequenceDefinition): Sequence
    {
        $sections = $sequenceDefinition->getSections()->sortByPriority();
        $chain = [];

        /** @var SectionDefinition $sectionDefinition */
        foreach ($sections as $sectionDefinition) {
            $type = $sectionDefinition->getType();

            if ($type === SectionDefinition::TYPE_SEQUENCE) {
                $chain[] = $this->registry->createSequence($sectionDefinition->getId());

                continue;
            }

            if ($type === SectionDefinition::TYPE_SECTION) {
                $chain[] = $this->registry->getSection($sectionDefinition->getId());

                continue;
            }

            throw SequenceBuildException::unknownSectionType($type);
        }

        $class = $this->getSequenceClass($sequenceDefinition);

        return new $class(...$chain);
    }

    protected function getSequenceClass(SequenceDefinition $definition): string
    {
        $class = $definition->getClass();

        if (!SequenceClassValidator::isSequenceClass($class)) {
            throw SequenceBuildException::invalidSequenceClass($class);
        }

        return $class;
    }
}
