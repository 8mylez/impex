<?php

namespace Dustin\ImpEx\Serializer\Converter;

/**
 * Is able to convert an attribute value into both directions (normalizing and denormalizing).
 *
 * After a value has been normalized denormalization of it should result in the same original value.
 * Means converting a value back and forth should always lead to the same result.
 */
abstract class BidirectionalConverter extends AttributeConverter
{
}
