<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests;

use Andante\NullableEmbeddableBundle\Tests\Functional\BaseFunctionalTest;
use Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures\NotNullColumnEmbeddable;
use Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures\TestEntityWithNotNullColumn;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;

/**
 * This test demonstrates WHY the PHPStan rule for nullable columns is important.
 *
 * When a NullableEmbeddable has a NOT NULL column and the embeddable becomes null,
 * Doctrine will try to set that column to NULL in the database, causing a MySQL error.
 */
class NotNullColumnTest extends BaseFunctionalTest
{
    protected static function getKernelClass(): string
    {
        return \Andante\NullableEmbeddableBundle\Tests\App\PHPStanRulesIssuesAppKernel::class;
    }

    /**
     * EXPLANATION OF THE PROBLEM:
     *
     * 1. You have a NullableEmbeddable with #[Column(nullable: false)]
     *
     * 2. When you set the embeddable to null, Doctrine tries to UPDATE
     *    the database, setting ALL embeddable columns to NULL
     *
     * 3. If any column is NOT NULL in the database, you get:
     *    "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'street' cannot be null"
     *
     * SOLUTION:
     * The PHPStan extension checks that all columns in a NullableEmbeddable are either:
     * - PHP nullable type (?string) - Doctrine infers nullable: true
     * - OR explicit nullable: true in #[Column]
     */
    public function testNotNullColumnCausesDatabaseErrorWhenEmbeddableBecomesNull(): void
    {
        $em = $this->getEntityManager();

        // Create entity with a valid address
        $entity = new TestEntityWithNotNullColumn();
        $address = new NotNullColumnEmbeddable('123 Main St');
        $entity->setAddress($address);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        // Reload entity
        $reloadedEntity = $em->find(TestEntityWithNotNullColumn::class, $entity->getId());
        $this->assertNotNull($reloadedEntity);
        $this->assertNotNull($reloadedEntity->getAddress());

        // Now set address to null directly
        // This will cause Doctrine to set ALL embeddable columns to NULL
        $reloadedEntity->setAddress(null);

        // This should throw a database constraint violation
        // because the 'street' column is NOT NULL in the database
        $this->expectException(NotNullConstraintViolationException::class);

        $em->flush();

        // The above will fail with:
        // SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'street' cannot be null
    }
}
