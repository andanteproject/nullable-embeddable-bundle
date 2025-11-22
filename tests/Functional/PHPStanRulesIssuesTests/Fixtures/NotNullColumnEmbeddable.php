<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * This embeddable VIOLATES the nullable column rule
 * It has a NOT NULL column which will cause MySQL error when the embeddable is set to null.
 */
#[ORM\Embeddable]
#[NullableEmbeddable(processor: NotNullColumnProcessor::class)]
class NotNullColumnEmbeddable
{
    public function __construct(
        // PROBLEM: This column is NOT NULL in database but embeddable can be null
        // When embeddable becomes null, Doctrine will try to set this to NULL -> MySQL error
        #[ORM\Column(type: Types::STRING, nullable: false)]
        private string $street = '',
    ) {
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }
}
