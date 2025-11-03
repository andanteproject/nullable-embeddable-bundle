<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NullableEmbeddable
{
    /**
     * @param class-string $processor
     */
    public function __construct(
        public string $processor,
    ) {
    }
}
