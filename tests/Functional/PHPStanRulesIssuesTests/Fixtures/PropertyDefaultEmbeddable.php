<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * This embeddable VIOLATES the property initialization rule
 * It has a property with default value outside constructor.
 */
#[ORM\Embeddable]
#[NullableEmbeddable(processor: PropertyDefaultProcessor::class)]
class PropertyDefaultEmbeddable
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $street = null;

    // PROBLEM: Property initialized outside constructor
    // When loaded from DB with NULL, Doctrine will get NULL but property shows true
    // This makes UnitOfWork think entity was modified
    private bool $isPrimary = false;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }
}
