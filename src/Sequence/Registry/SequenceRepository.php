<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\Loader\SequenceLoaderInterface;

class SequenceRepository implements SequenceRepositoryInterface
{
    /**
     * @var SequenceLoaderInterface[]
     */
    private $loaders = [];

    /**
     * @var ObjectMapping
     */
    private $sequences;

    private bool $loaded = false;

    public function __construct(iterable $loaders)
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }

        $this->sequences = ObjectMapping::create(SequenceDefinition::class);
    }

    public function addLoader(SequenceLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    public function addSequence(SequenceDefinition $definition): void
    {
        $this->loadSequences();

        $existingDefinition = $this->getSequence($definition->getId());

        if ($existingDefinition !== null) {
            $definition = $this->mergeDefinitions($existingDefinition, $definition);
        }

        $this->sequences->set($definition->getId(), $definition);
    }

    public function getSequence(string $id): ?SequenceDefinition
    {
        $this->loadSequences();

        return $this->sequences->get($id);
    }

    public function hasSequence(string $id): bool
    {
        $this->loadSequences();

        return $this->sequences->has($id);
    }

    protected function mergeDefinitions(SequenceDefinition $existing, SequenceDefinition $new): SequenceDefinition
    {
        $existing->setList($new->getList(['id', 'class']));

        foreach ($new->getSections() as $section) {
            $existing->getSections()->add($section);
        }

        return $existing;
    }

    private function loadSequences(): void
    {
        if ($this->loaded === true) {
            return;
        }

        foreach ($this->loaders as $loader) {
            foreach ($loader->load() as $sequence) {
                $this->addSequence($sequence);
            }
        }

        $this->loaded = true;
    }
}
