<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle;

enum Result: string
{
    case SHOULD_BE_NULL = 'should_be_null';
    case KEEP_INITIALIZED = 'keep_initialized';
}
