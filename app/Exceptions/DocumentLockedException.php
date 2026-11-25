<?php

namespace App\Exceptions;

/**
 * A save was refused because it would change something a later document depends on
 * (D4: delivered order lines, received purchase lines, returned / used receipt lines) or
 * would break a quantity rule (D2: receiving above the ordered quantity). Nothing is saved.
 * The message is written for the end user and is safe to flash.
 */
class DocumentLockedException extends \RuntimeException
{
    /** Appended to lock refusals: there is no reversal feature, an admin corrects such records. */
    public const ASK_ADMIN = ' To correct it, ask an admin.';
}
