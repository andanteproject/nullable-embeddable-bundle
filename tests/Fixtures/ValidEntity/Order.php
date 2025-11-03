<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'billing_address_')]
    private ?Address $billingAddress = null;

    public function __construct(
        #[ORM\Embedded(class: Address::class, columnPrefix: 'shipping_address_')]
        private Address $shippingAddress,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getShippingAddress(): Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(Address $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }
}
