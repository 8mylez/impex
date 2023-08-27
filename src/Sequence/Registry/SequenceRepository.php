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

    private bool $isLoading = false;

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

    private function loadSequences(): void
    {
        if ($this->loaded === true || $this->isLoading === true) {
            return;
        }

        $this->isLoading = true;

        foreach ($this->loaders as $loader) {
            foreach ($loader->load() as $sequence) {
                $this->addSequence($sequence);
            }
        }

        $this->loaded = true;
        $this->isLoading = false;
    }
}
