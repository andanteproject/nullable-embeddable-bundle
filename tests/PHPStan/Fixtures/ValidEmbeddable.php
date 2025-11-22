<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\PHPStan\Fixtures;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: TestProcessor::class)]
class ValidEmbeddable
{
    // CORRECT: Column is nullable
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $city = null;

    // CORRECT: Default value in constructor
    public function __construct(
        #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
        private bool $isPrimary = false,
    ) {
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }
}
