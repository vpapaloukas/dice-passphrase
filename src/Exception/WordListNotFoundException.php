<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Exception;

use Exception;

/**
 * Exception thrown when a word list file cannot be found or accessed.
 * 
 * This includes cases where:
 * - Word list file path does not exist
 * - Word list file is not readable
 * - Word list file path is a directory instead of a file
 */
class WordListNotFoundException extends Exception
{
}
