<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\PropertyAccess\PropertyAccessor;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\GroupKeyNotFoundException;
use Dustin\ImpEx\Util\Type;

class Grouper extends BidirectionalConverter
{
    private Path $groupKey;

    public function __construct(string|array|Path $groupKey, string ...$flags)
    {
        if (!$groupKey instanceof Path) {
            $groupKey = new Path($groupKey);
        }

        $this->groupKey = $groupKey;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $data, ConversionContext $context): null|array
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        $this->validateType($data, Type::ITERABLE, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->merge($data, $context);
        }

        return $this->group($data, $context);
    }

    public function denormalize(mixed $data, ConversionContext $context): null|array
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        $this->validateType($data, Type::ITERABLE, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->group($data, $context);
        }

        return $this->merge($data, $context);
    }

    public function group(iterable $data, ConversionContext $context): array
    {
        $result = [];

        foreach ($data as $key => $record) {
            $subContext = $context->subContext(new Path([$key]));

            try {
                $groupKey = PropertyAccessor::get($this->groupKey, $record);
            } catch (InvalidDataException|NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
                throw new GroupKeyNotFoundException($subContext->getPath(), $subContext->getRootData(), $this->groupKey, Type::getDebugType($record));
            }

            $this->validateStringConvertable($groupKey, $subContext);

            $result[(string) $groupKey][] = $record;
        }

        return $result;
    }

    public function merge(iterable $data, ConversionContext $context): array
    {
        $result = [];

        foreach ($data as $key => $group) {
            $subContext = $context->subContext(new Path([$key]));

            $this->validateType($group, Type::ITERABLE, $subContext);

            foreach ($group as $record) {
                $result[] = $record;
            }
        }

        return $result;
    }
}
