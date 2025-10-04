<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase;

use Vpap\DicePassphrase\Exception\InvalidWordListException;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\WordList\FileWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;
use Vpap\DicePassphrase\WordList\WordListInterface;

/**
 * Factory class for creating PassphraseGenerator instances with common configurations.
 *
 * Provides convenient methods for creating generators with default settings,
 * embedded word lists, or custom file-based word lists.
 */
class PassphraseGeneratorFactory
{
    /**
     * Create a passphrase generator with the default English word list.
     *
     * Uses the built-in English word list and secure random number generation.
     * This is the most convenient method for typical use cases.
     *
     * @return PassphraseGenerator Generator configured with default English word list
     */
    public static function createWithDefaultEnglishWordList(): PassphraseGenerator
    {
        $wordList = new EmbeddedWordList();
        $randomGenerator = new SecureRandomGenerator();

        return new PassphraseGenerator($wordList, $randomGenerator);
    }

    /**
     * Create a passphrase generator with a custom embedded word list.
     *
     * Allows using a custom word list array while still benefiting from
     * the performance of in-memory word storage.
     *
     * @param array $wordList Associative array with dice roll keys (11111-66666) and word values
     * @return PassphraseGenerator Generator configured with the provided word list
     * @throws InvalidWordListException
     */
    public static function createWithEmbeddedWordList(array $wordList): PassphraseGenerator
    {
        $embeddedWordList = new EmbeddedWordList($wordList);
        $randomGenerator = new SecureRandomGenerator();

        return new PassphraseGenerator($embeddedWordList, $randomGenerator);
    }

    /**
     * Create a passphrase generator with a file-based word list.
     *
     * Loads a word list from an external file in standard Diceware format.
     * Useful for custom languages or specialized word lists.
     *
     * @param string $filePath Path to the word list file
     * @return PassphraseGenerator Generator configured with the file-based word list
     */
    public static function createWithFileWordList(string $filePath): PassphraseGenerator
    {
        $wordList = new FileWordList($filePath);
        $randomGenerator = new SecureRandomGenerator();

        return new PassphraseGenerator($wordList, $randomGenerator);
    }

    /**
     * Create a passphrase generator with custom dependencies.
     *
     * Provides full control over the word list and random generator implementations.
     * Useful for testing or advanced customization scenarios.
     *
     * @param WordListInterface $wordList Word list implementation
     * @param SecureRandomGenerator|null $randomGenerator Random generator (uses default if null)
     * @return PassphraseGenerator Generator configured with the provided dependencies
     */
    public static function createWithCustomDependencies(
        WordListInterface $wordList,
        ?SecureRandomGenerator $randomGenerator = null
    ): PassphraseGenerator {
        $randomGenerator = $randomGenerator ?? new SecureRandomGenerator();

        return new PassphraseGenerator($wordList, $randomGenerator);
    }
}
