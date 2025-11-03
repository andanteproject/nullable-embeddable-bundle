<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EntityWithInvalidProcessor
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Embedded(class: EmbeddableWithInvalidProcessor::class, columnPrefix: false)]
    private ?EmbeddableWithInvalidProcessor $embeddable = null;

    public function __construct()
    {
        $this->embeddable = new EmbeddableWithInvalidProcessor();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmbeddable(): ?EmbeddableWithInvalidProcessor
    {
        return $this->embeddable;
    }

    public function setEmbeddable(?EmbeddableWithInvalidProcessor $embeddable): self
    {
        $this->embeddable = $embeddable;

        return $this;
    }
}
