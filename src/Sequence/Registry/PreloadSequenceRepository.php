<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Registry\Loader\SequenceLoaderInterface;

class PreloadSequenceRepository implements SequenceRepositoryInterface
{
    /**
     * @var SequenceLoaderInterface[]
     */
    private $loaders = [];

    private bool $loaded = false;

    private bool $isLoading = false;

    public function __construct(
        private SequenceRepositoryInterface $sequenceRepository,
        iterable $loaders
    ) {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    public function addSequence(SequenceDefinition $definition): void
    {
        $this->loadSequences();

        $this->sequenceRepository->addSequence($definition);
    }

    public function getSequence(string $id): ?SequenceDefinition
    {
        $this->loadSequences();

        return $this->sequenceRepository->getSequence($id);
    }

    public function hasSequence(string $id): bool
    {
        $this->loadSequences();

        return $this->sequenceRepository->hasSequence($id);
    }

    protected function addLoader(SequenceLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
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
