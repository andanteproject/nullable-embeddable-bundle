<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TestEntityWithNotNullColumn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Embedded(class: NotNullColumnEmbeddable::class)]
    private ?NotNullColumnEmbeddable $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?NotNullColumnEmbeddable
    {
        return $this->address;
    }

    public function setAddress(?NotNullColumnEmbeddable $address): void
    {
        $this->address = $address;
    }
}
