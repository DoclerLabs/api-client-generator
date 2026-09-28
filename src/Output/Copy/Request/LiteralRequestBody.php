<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request;

use DateTimeInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;

/**
 * Request body whose schema is not an object: a string, number, boolean, or an array of those.
 * The JSON serializers encode it as the literal value itself, the same way a literal response is decoded.
 */
class LiteralRequestBody implements SerializableInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [ContentTypeSerializerInterface::LITERAL_VALUE_KEY => $this->normalize($this->value)];
    }

    private function normalize($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_RFC3339);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalize($item);
            }
        }

        return $value;
    }
}
