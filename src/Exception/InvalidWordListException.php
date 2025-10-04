<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Exception;

use Exception;

/**
 * Exception thrown when a word list is invalid or malformed.
 *
 * This includes cases where:
 * - Word list doesn't contain exactly 7776 entries
 * - Word list has invalid format (missing dice roll numbers, invalid digits)
 * - Word list is missing required entries
 * - Word list contains duplicate entries
 */
class InvalidWordListException extends Exception
{
}
