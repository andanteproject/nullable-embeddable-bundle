<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClosureProcessorEntity;

use Andante\NullableEmbeddableBundle\Attribute\NullableEmbeddable;
use Andante\NullableEmbeddableBundle\Exception\UnexpectedEmbeddableClassException;
use Andante\NullableEmbeddableBundle\PropertyAccess\PropertyAccessor;
use Andante\NullableEmbeddableBundle\Result;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
#[NullableEmbeddable(processor: static function (PropertyAccessor $propertyAccessor, object $embeddableObject,
): Result {
    if (!$embeddableObject instanceof Address) {
        throw UnexpectedEmbeddableClassException::create(Address::class, $embeddableObject);
    }
    if (
        null === $propertyAccessor->getValue($embeddableObject, 'street')
        && null === $propertyAccessor->getValue($embeddableObject, 'city')
        && null === $propertyAccessor->getValue($embeddableObject, 'country')
    ) {
        return Result::SHOULD_BE_NULL;
    }

    return Result::KEEP_INITIALIZED;
})]
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
