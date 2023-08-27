<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

class PlaceholderSequenceDefinition extends SequenceDefinition
{
    public function isConfigured(): bool
    {
        if ($this->get('id') === null || $this->get('class') === null) {
            return false;
        }

        return true;
    }
}
