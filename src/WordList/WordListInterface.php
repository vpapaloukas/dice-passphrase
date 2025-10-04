<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\WordList;

use Vpap\DicePassphrase\Exception\InvalidWordListException;

/**
 * Interface for word list implementations used in passphrase generation.
 * 
 * Word lists must contain exactly 7776 entries (6^5) with five-digit dice roll
 * keys ranging from 11111 to 66666, where each digit is between 1-6.
 */
interface WordListInterface
{
    /**
     * Get a word for the given dice roll.
     * 
     * @param string $diceRoll Five-digit string with digits 1-6 (e.g., "12345")
     * @return string The corresponding word from the word list
     * @throws InvalidWordListException if dice roll is not found
     */
    public function getWord(string $diceRoll): string;

    /**
     * Check if the word list is valid and complete.
     * 
     * @return bool True if the word list contains exactly 7776 valid entries
     */
    public function isValid(): bool;

    /**
     * Get the total number of words in the word list.
     * 
     * @return int Number of words in the list (should be 7776 for valid lists)
     */
    public function getWordCount(): int;
}
