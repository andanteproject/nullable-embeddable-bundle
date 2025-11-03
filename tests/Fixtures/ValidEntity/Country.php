<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\Processor\CountryEmbeddableProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: CountryEmbeddableProcessor::class)]
class Country
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 2, nullable: true)]
        private string $code,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
