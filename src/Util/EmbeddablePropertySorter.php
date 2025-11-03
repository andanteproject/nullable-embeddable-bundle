<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Util;

class EmbeddablePropertySorter
{
    /**
     * Sorts embeddable property paths from deepest to highest.
     * Deeper entities first, parents last.
     * The more "dots" in the path, the deeper the embeddable.
     *
     * @param array<string, mixed> $embeddableClasses
     *
     * @return array<string, mixed>
     */
    public static function sortDeepestFirst(array $embeddableClasses): array
    {
        \uksort($embeddableClasses, function (string $embeddablePropertyPath1, string $embeddablePropertyPath2): int {
            $dots1 = \substr_count($embeddablePropertyPath1, '.');
            $dots2 = \substr_count($embeddablePropertyPath2, '.');

            return $dots2 <=> $dots1;
        });

        return $embeddableClasses;
    }
}
