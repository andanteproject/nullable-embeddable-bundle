<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity\Address;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity\Country;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity\Order;

class NullableEmbeddableTest extends BaseFunctionalTest
{
    public function testBillingAddressIsSetToNullWhenAllPropertiesAreNull(): void
    {
        $shippingAddress = (new Address())->setStreet('Shipping Street');
        $order = new Order($shippingAddress);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $retrievedOrder = $this->getEntityManager()->find(Order::class, $order->getId());

        $this->assertNotNull($retrievedOrder);
        $this->assertNull($retrievedOrder->getBillingAddress());
    }

    public function testBillingAddressIsSetToNullWhenAllPropertiesIncludingCountryAreNull(): void
    {
        $shippingAddress = (new Address())->setStreet('Shipping Street');
        $order = new Order($shippingAddress);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $retrievedOrder = $this->getEntityManager()->find(Order::class, $order->getId());

        $this->assertNotNull($retrievedOrder);
        $this->assertNull($retrievedOrder->getBillingAddress());
    }

    public function testBillingAddressIsKeptWhenCountryCodeIsNotNull(): void
    {
        $this->assertNotNull($this->getEntityManager()); // PHPStan fix
        $shippingAddress = (new Address())->setStreet('Shipping Street');
        $order = new Order($shippingAddress);
        $billingAddress = new Address();
        $billingAddress->setCountry(new Country('US'));
        $order->setBillingAddress($billingAddress);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $retrievedOrder = $this->getEntityManager()->find(Order::class, $order->getId());

        $this->assertNotNull($retrievedOrder);
        $this->assertNotNull($retrievedOrder->getBillingAddress());
        $this->assertNotNull($retrievedOrder->getBillingAddress()->getCountry());
        $this->assertSame('US', $retrievedOrder->getBillingAddress()->getCountry()->getCode());
    }

    public function testShippingAddressIsAlwaysKeptAsItIsRequired(): void
    {
        $this->assertNotNull($this->getEntityManager()); // PHPStan fix
        $shippingAddress = (new Address())->setStreet('Required Shipping Street');
        $order = new Order($shippingAddress);
        $order->setBillingAddress(new Address()); // Should be nullified

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $retrievedOrder = $this->getEntityManager()->find(Order::class, $order->getId());

        $this->assertNotNull($retrievedOrder);
        $this->assertNull($retrievedOrder->getBillingAddress());
        $this->assertNotNull($retrievedOrder->getShippingAddress());
        $this->assertSame('Required Shipping Street', $retrievedOrder->getShippingAddress()->getStreet());
    }
}
