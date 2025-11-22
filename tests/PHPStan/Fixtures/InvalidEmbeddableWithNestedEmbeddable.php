<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\PHPStan\Fixtures;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: TestProcessor::class)]
class InvalidEmbeddableWithNestedEmbeddable
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $street = null;

    // ERROR: Embedded property must be nullable
    #[ORM\Embedded(class: ValidEmbeddable::class)]
    private ValidEmbeddable $country;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCountry(): ValidEmbeddable
    {
        return $this->country;
    }
}
