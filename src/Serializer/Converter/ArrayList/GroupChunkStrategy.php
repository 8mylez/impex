<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\PropertyAccess\PropertyAccessor;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\TypeConversionException;
use Dustin\ImpEx\Util\Type;

class GroupChunkStrategy extends ChunkStrategy
{
    public const GROUP_KEY_NOT_FOUND_ERROR = 'IMPEX_CONVERSION__GROUP_KEY_NOT_FOUND_ERROR';

    private Path $groupKey;

    public function __construct(string|array|Path $groupKey)
    {
        if (!$groupKey instanceof Path) {
            $groupKey = new Path($groupKey);
        }

        $this->groupKey = $groupKey;
    }

    public function chunk(array $data, ConversionContext $context): array
    {
        $result = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $record) {
            $subContext = $context->subContext(new Path([$key]));

            try {
                $groupKey = PropertyAccessor::get($this->groupKey, $record);
            } catch (InvalidDataException|NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
                $exceptions->add(new AttributeConversionException($subContext->getPath(), $subContext->getRootData(), 'Group key {{ path }} could not be fetched from value of type {{ type }}.', ['path' => $this->groupKey, 'type' => Type::getDebugType($record)], self::GROUP_KEY_NOT_FOUND_ERROR));

                continue;
            }

            if (!Type::isStringConvertable(Type::getType($groupKey))) {
                $exceptions->add(TypeConversionException::string($groupKey, $subContext));

                continue;
            }

            $result[(string) $groupKey][] = $record;
        }

        $exceptions->throw();

        return $result;
    }
}
