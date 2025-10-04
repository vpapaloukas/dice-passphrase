<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase;


use Vpap\DicePassphrase\Exception\InvalidWordListException;
use Vpap\DicePassphrase\WordList\WordListInterface;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;
use Vpap\DicePassphrase\Exception\InvalidWordCountException;

/**
 * Main passphrase generator class implementing the Diceware method.
 * 
 * Generates secure, memorable passphrases by using cryptographically secure
 * random number generation to select words from predefined word lists.
 */
class PassphraseGenerator
{
    private WordListInterface $wordList;
    private SecureRandomGenerator $randomGenerator;

    /**
     * Create a new passphrase generator.
     * 
     * @param WordListInterface $wordList Word list implementation to use for word selection
     * @param SecureRandomGenerator $randomGenerator Random number generator for dice rolls
     */
    public function __construct(
        WordListInterface $wordList,
        SecureRandomGenerator $randomGenerator
    ) {
        $this->wordList = $wordList;
        $this->randomGenerator = $randomGenerator;
    }

    /**
     * Generate a passphrase as an array of words.
     * 
     * Uses cryptographically secure random number generation to simulate dice rolls
     * and select words from the configured word list.
     * 
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @return array Array of words selected from the word list
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public function generate(int $wordCount = 6): array
    {
        if ($wordCount < 1) {
            throw new InvalidWordCountException("Word count must be at least 1, got: {$wordCount}");
        }

        $words = [];
        
        for ($i = 0; $i < $wordCount; $i++) {
            $diceRoll = $this->randomGenerator->generateDiceRoll();
            $words[] = $this->wordList->getWord($diceRoll);
        }
        
        return $words;
    }

    /**
     * Generate a passphrase as a separated string.
     * 
     * Uses the same generation logic as generate() but returns the words
     * joined with the specified separator for convenient string output.
     * 
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @param string $separator Character(s) to use for joining words (default: ' ')
     * @return string Separated string of words
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public function generateString(int $wordCount = 6, string $separator = ' '): string
    {
        $words = $this->generate($wordCount);
        return implode($separator, $words);
    }
    
    /**
     * Static convenience method to quickly generate a passphrase with default settings.
     * 
     * Creates a generator with the default English word list and generates a passphrase
     * with the specified number of words. This is the most convenient method for
     * one-off passphrase generation.
     * 
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @return array Array of words selected from the default English word list
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public static function quick(int $wordCount = 6): array
    {
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        return $generator->generate($wordCount);
    }
    
    /**
     * Static convenience method to quickly generate a passphrase string with default settings.
     * 
     * Creates a generator with the default English word list and generates a passphrase
     * string with the specified number of words and separator.
     * 
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @param string $separator Character(s) to use for joining words (default: ' ')
     * @return string Space-separated string of words from the default English word list
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public static function quickString(int $wordCount = 6, string $separator = ' '): string
    {
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        return $generator->generateString($wordCount, $separator);
    }
    
    /**
     * Static convenience method to generate a passphrase from a file-based word list.
     * 
     * Creates a generator with the specified file-based word list and generates
     * a passphrase with the specified number of words.
     * 
     * @param string $filePath Path to the word list file
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @return array Array of words selected from the file-based word list
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public static function quickFromFile(string $filePath, int $wordCount = 6): array
    {
        $generator = PassphraseGeneratorFactory::createWithFileWordList($filePath);
        return $generator->generate($wordCount);
    }
    
    /**
     * Static convenience method to generate a passphrase string from a file-based word list.
     * 
     * Creates a generator with the specified file-based word list and generates
     * a passphrase string with the specified number of words and separator.
     * 
     * @param string $filePath Path to the word list file
     * @param int $wordCount Number of words to include in the passphrase (default: 6)
     * @param string $separator Character(s) to use for joining words (default: ' ')
     * @return string Separated string of words from the file-based word list
     * @throws InvalidWordCountException if word count is less than 1
     * @throws \Exception if secure random number generation fails
     * @throws InvalidWordListException if dice roll is not found
     */
    public static function quickStringFromFile(string $filePath, int $wordCount = 6, string $separator = ' '): string
    {
        $generator = PassphraseGeneratorFactory::createWithFileWordList($filePath);
        return $generator->generateString($wordCount, $separator);
    }
}
