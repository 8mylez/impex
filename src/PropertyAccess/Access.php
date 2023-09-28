<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

class Access
{
    public function __construct(
        private \Closure $callback
    ) {
    }

    public function access(string|array|int|Path $path, mixed &$data, mixed $value = null, AccessContext $context): mixed
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $callback = $this->callback;

        return $callback($path, $data, $value, $context);
    }
}
