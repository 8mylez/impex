<?php

namespace Dustin\ImpEx\Encapsulation;

use Dustin\Encapsulation\ArrayEncapsulation;
use Dustin\ImpEx\Util\ArrayUtil;

class Header extends ArrayEncapsulation
{
    public function flag(string $flag): void
    {
        $flags = $this->getCurrentFlags();

        $flags[$flag] = $flag;

        $this->set('flags', $flags);
    }

    public function isFlag(string $flag): bool
    {
        $flags = $this->getCurrentFlags();

        return isset($flags[$flag]);
    }

    public function removeFlag(string $flag): void
    {
        $flags = $this->getCurrentFlags();
        unset($flags[$flag]);

        $this->set('flags', $flags);
    }

    public function getFlags(): array
    {
        return array_keys($this->getCurrentFlags());
    }

    private function getCurrentFlags(): array
    {
        return ArrayUtil::cast($this->get('flags'));
    }
}
