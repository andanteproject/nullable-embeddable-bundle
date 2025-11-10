<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\Tests\App\ClosureProcessorAppKernel;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClosureProcessorEntity\Address;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClosureProcessorEntity\Order;

/**
 * @requires PHP >= 8.5
 */
class ClosureProcessorFunctionalTest extends BaseFunctionalTest
{
    protected static function getKernelClass(): string
    {
        return ClosureProcessorAppKernel::class;
    }

    public function testBillingAddressIsSetToNullWhenAllPropertiesAreNullWithClosureProcessor(): void
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

    public function testBillingAddressIsKeptWhenStreetIsNotNullWithClosureProcessor(): void
    {
        $shippingAddress = (new Address())->setStreet('Shipping Street');
        $order = new Order($shippingAddress);
        $billingAddress = (new Address())->setStreet('Billing Street');
        $order->setBillingAddress($billingAddress);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $retrievedOrder = $this->getEntityManager()->find(Order::class, $order->getId());

        $this->assertNotNull($retrievedOrder);
        $this->assertNotNull($retrievedOrder->getBillingAddress());
        $this->assertSame('Billing Street', $retrievedOrder->getBillingAddress()->getStreet());
    }
}
