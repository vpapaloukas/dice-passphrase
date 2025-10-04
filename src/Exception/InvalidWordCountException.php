<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Exception;

use Exception;

/**
 * Exception thrown when an invalid word count is specified for passphrase generation.
 *
 * This includes cases where:
 * - Word count is less than 1
 * - Word count is not a positive integer
 */
class InvalidWordCountException extends Exception
{
}
