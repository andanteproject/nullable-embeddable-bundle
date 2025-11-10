<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity\Processor\AddressEmbeddableProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: AddressEmbeddableProcessor::class)]
class Address
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $city = null;

    #[ORM\Embedded(class: Country::class, columnPrefix: 'country_')]
    private ?Country $country = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }
}
