<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\WordList;

use Vpap\DicePassphrase\Exception\InvalidWordListException;

/**
 * Embedded word list implementation with built-in English word list.
 *
 * Uses a pre-defined associative array for O(1) word lookup performance.
 * The word list is stored in memory and doesn't require file I/O operations.
 */
class EmbeddedWordList implements WordListInterface
{
    private array $words;
    private bool $isValid;

    /**
     * Create a new embedded word list.
     *
     * @param array|null $wordList Optional custom word list array. If null, uses default English list.
     * @throws InvalidWordListException if provided word list is invalid
     */
    public function __construct(?array $wordList = null)
    {
        $this->words = $wordList ?? $this->getDefaultEnglishWordList();
        $this->validateWordList();
    }

    /**
     * {@inheritdoc}
     */
    public function getWord(string $diceRoll): string
    {
        if (!isset($this->words[$diceRoll])) {
            throw new InvalidWordListException("Word not found for dice roll: {$diceRoll}");
        }

        return $this->words[$diceRoll];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getWordCount(): int
    {
        return count($this->words);
    }

    /**
     * Get the default English word list.
     *
     * @return array Associative array with dice roll keys and word values
     */
    private function getDefaultEnglishWordList(): array
    {
        return EnglishWordListData::getWordList();
    }

    /**
     * Validate that the embedded word list is complete and correct.
     *
     * @throws InvalidWordListException if word list is invalid
     */
    private function validateWordList(): void
    {
        $expectedCount = 7776; // 6^5
        $actualCount = count($this->words);

        if ($actualCount !== $expectedCount) {
            throw new InvalidWordListException(
                "Embedded word list must contain exactly {$expectedCount} entries, found: {$actualCount}"
            );
        }

        // Verify all dice roll keys are valid and words are clean
        foreach ($this->words as $diceRoll => $word) {
            $diceRollStr = (string)$diceRoll;

            // Validate dice roll format
            if (!preg_match('/^[1-6]{5}$/', $diceRollStr)) {
                throw new InvalidWordListException(
                    "Invalid dice roll key in embedded word list: {$diceRoll}"
                );
            }

            // Validate word content
            if (!is_string($word) || trim($word) === '') {
                throw new InvalidWordListException(
                    "Invalid word for dice roll {$diceRoll}: word must be a non-empty string"
                );
            }

            // Check for potentially problematic characters in words
            if ($this->containsProblematicCharacters($word)) {
                throw new InvalidWordListException(
                    "Word for dice roll {$diceRoll} contains invalid characters"
                );
            }
        }

        // Verify all possible dice roll combinations are present
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            if (!isset($this->words[$diceRoll])) {
                                throw new InvalidWordListException(
                                    "Missing entry for dice roll in embedded word list: {$diceRoll}"
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->isValid = true;
    }

    /**
     * Check if a word contains characters that might cause issues across platforms.
     *
     * @param string $word The word to check
     * @return bool True if the word contains problematic characters
     */
    private function containsProblematicCharacters(string $word): bool
    {
        // Check for control characters that might cause issues across different platforms
        return preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $word) === 1;
    }
}
