<?php

namespace Dustin\ImpEx\Serializer;

interface ContextProviderInterface
{
    public function getContext(): array;
}
