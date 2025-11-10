<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional;

use Andante\NullableEmbeddableBundle\Exception\InvalidProcessorException;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\MetadataFactory;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Andante\NullableEmbeddableBundle\Tests\App\InvalidProcessorAppKernel;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity\EmbeddableWithInvalidProcessor;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity\EntityWithInvalidProcessor;
use Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity\Processor\InvalidProcessor;

class InvalidClassStringProcessorTest extends BaseFunctionalTest
{
    protected static function getKernelClass(): string
    {
        return InvalidProcessorAppKernel::class;
    }

    public function testInvalidProcessorThrowsException(): void
    {
        $this->expectException(InvalidProcessorException::class);
        $this->expectExceptionMessage(\sprintf('Processor "%s" for embeddable "%s" must implement "%s".', InvalidProcessor::class, EmbeddableWithInvalidProcessor::class, ProcessorInterface::class));

        /** @var MetadataFactory $metadataFactory */
        $metadataFactory = self::getContainer()->get(MetadataFactory::class);
        $metadataFactory->create(EntityWithInvalidProcessor::class);
    }
}
