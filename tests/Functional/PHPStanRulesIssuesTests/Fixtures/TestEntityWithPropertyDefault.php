<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TestEntityWithPropertyDefault
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Embedded(class: PropertyDefaultEmbeddable::class)]
    private ?PropertyDefaultEmbeddable $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?PropertyDefaultEmbeddable
    {
        return $this->address;
    }

    public function setAddress(?PropertyDefaultEmbeddable $address): void
    {
        $this->address = $address;
    }
}
