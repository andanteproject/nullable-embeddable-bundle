<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\PHPStan\Fixtures;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: TestProcessor::class)]
class InvalidEmbeddable
{
    // ERROR: Property has non-null default outside constructor
    private bool $isPrimary = false;

    // ERROR: Column is not nullable (neither explicit nullable=true nor PHP nullable type)
    #[ORM\Column(type: Types::STRING)]
    private string $street;

    // CORRECT: Column is nullable via PHP type (Doctrine infers nullable: true)
    #[ORM\Column(type: Types::STRING)]
    private ?string $city = null;

    // ALSO CORRECT: Column explicitly nullable
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private string $postalCode;
}
