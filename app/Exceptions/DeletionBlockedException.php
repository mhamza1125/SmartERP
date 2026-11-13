<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown inside a repository delete when a check made under lock fails (e.g. stock that would go
 * negative). The message is shown to the user and the whole delete is rolled back.
 */
class DeletionBlockedException extends RuntimeException
{
}
