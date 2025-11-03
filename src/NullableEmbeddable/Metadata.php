<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\NullableEmbeddable;

use Andante\NullableEmbeddableBundle\NullableEmbeddable\Metadata\Embedded;

class Metadata
{
    /**
     * @param class-string   $entityClassFqcn
     * @param list<Embedded> $embeds
     */
    public function __construct(
        /** @var class-string */
        private string $entityClassFqcn,
        /** @var list<Embedded> */
        private array $embeds,
    ) {
    }

    public function getEntityClassFqcn(): string
    {
        return $this->entityClassFqcn;
    }

    /**
     * @return list<Embedded>
     */
    public function getEmbeds(): array
    {
        return $this->embeds;
    }
}
