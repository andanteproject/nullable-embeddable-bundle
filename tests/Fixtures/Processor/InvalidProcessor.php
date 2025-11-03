<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\Processor;

// This class intentionally does NOT implement ProcessorInterface
class InvalidProcessor
{
    public function process(object $embeddable): ?object
    {
        return $embeddable;
    }
}
