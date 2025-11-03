<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Unit\Util;

use Andante\NullableEmbeddableBundle\Util\EmbeddablePropertySorter;
use PHPUnit\Framework\TestCase;

class EmbeddablePropertySorterTest extends TestCase
{
    public function testSortDeepestFirst(): void
    {
        $embeddableClasses = [
            'address' => ['class' => 'App\Embeddable\Address'],
            'address.street' => ['class' => 'App\Embeddable\Street'],
            'address.street.number' => ['class' => 'App\Embeddable\Number'],
            'contact.email' => ['class' => 'App\Embeddable\Email'],
            'contact' => ['class' => 'App\Embeddable\Contact'],
        ];

        $expectedOrder = [
            'address.street.number',
            'address.street',
            'contact.email',
            'address',
            'contact',
        ];

        $sortedEmbeddableClasses = EmbeddablePropertySorter::sortDeepestFirst($embeddableClasses);
        $this->assertSame(\array_keys($sortedEmbeddableClasses), $expectedOrder);
    }
}
