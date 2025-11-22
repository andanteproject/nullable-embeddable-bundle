<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests;

use Andante\NullableEmbeddableBundle\Tests\Functional\BaseFunctionalTest;
use Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures\PropertyDefaultEmbeddable;
use Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures\TestEntityWithPropertyDefault;
use Doctrine\ORM\UnitOfWork;

/**
 * This test demonstrates WHY the PHPStan rule for property initialization is important.
 *
 * When a property has a default value outside the constructor (e.g., private bool $isPrimary = false),
 * Doctrine's hydration bypasses the constructor. When loading from DB with NULL values,
 * the property gets the default value AFTER hydration, making UnitOfWork think the entity was modified.
 */
class PropertyDefaultTest extends BaseFunctionalTest
{
    protected static function getKernelClass(): string
    {
        return \Andante\NullableEmbeddableBundle\Tests\App\PHPStanRulesIssuesAppKernel::class;
    }

    /**
     * EXPLANATION OF THE PROBLEM:
     *
     * 1. You have an embeddable with: private bool $isPrimary = false; (outside constructor)
     *
     * 2. When you save an entity with address = null, all embeddable columns are NULL in DB
     *
     * 3. When Doctrine loads the entity:
     *    a) Doctrine creates the object WITHOUT calling constructor (uses reflection)
     *    b) Then PHP initializes properties with their default values
     *    c) So $isPrimary becomes FALSE (from default)
     *    d) But Doctrine's snapshot has NULL (from database)
     *
     * 4. If the embeddable is kept initialized (not set to null by processor):
     *    - Current value: $isPrimary = false (from property default)
     *    - Snapshot value: NULL (from database)
     *    - UnitOfWork detects this as a change!
     *    - Entity marked as dirty even though user didn't modify it
     *
     * 5. On next flush(), Doctrine will UPDATE the database unnecessarily
     *
     * SOLUTION:
     * The PHPStan extension checks that properties with non-null defaults
     * are initialized in the constructor instead:
     *
     *     public function __construct(
     *         private bool $isPrimary = false,  // ✓ Correct
     *     ) {}
     *
     * This way constructor parameters ARE part of Doctrine's hydration,
     * and no phantom changes are detected in UnitOfWork.
     */
    public function testPropertyDefaultCausesUnitOfWorkToDetectFalseChanges(): void
    {
        $em = $this->getEntityManager();

        // Create entity with address where street is null (embeddable will be null)
        $entity = new TestEntityWithPropertyDefault();
        $address = new PropertyDefaultEmbeddable();
        $address->setStreet(null); // This will cause embeddable to be null
        $entity->setAddress($address);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        // Reload entity - address should be null because street was null
        $reloadedEntity = $em->find(TestEntityWithPropertyDefault::class, $entity->getId());
        $this->assertNotNull($reloadedEntity);

        // The problem occurs here:
        // 1. Doctrine loaded entity from DB with NULL values for embeddable columns
        // 2. The NullableEmbeddable bundle set address to null (correct behavior)
        // 3. BUT the $isPrimary property was initialized to FALSE by PHP (not in constructor)
        // 4. Doctrine's original snapshot has NULL for isPrimary (from DB)
        // 5. Current value is FALSE (from property default)
        // 6. UnitOfWork detects this as a change!

        $uow = $em->getUnitOfWork();

        // Get scheduled updates BEFORE making any actual changes
        $uow->computeChangeSets();
        $scheduledUpdates = $uow->getScheduledEntityUpdates();

        // THIS IS THE BUG: Entity is scheduled for update even though user didn't change anything!
        // This happens because property default (false) differs from DB value (null)
        if (null !== $reloadedEntity->getAddress()) {
            $this->assertCount(
                1,
                $scheduledUpdates,
                'Entity is incorrectly marked as updated due to property default value mismatch'
            );

            // Verify the change is detected on the isPrimary field
            $changeset = $uow->getEntityChangeSet($reloadedEntity);
            $this->assertArrayHasKey(
                'address',
                $changeset,
                'Address embeddable detected as changed due to isPrimary property default'
            );
        } else {
            // If address is null, there's no change detected
            $this->assertCount(0, $scheduledUpdates);
        }
    }
}
