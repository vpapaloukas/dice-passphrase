<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\WordList;

use Vpap\DicePassphrase\Exception\InvalidWordListException;
use Vpap\DicePassphrase\Exception\WordListNotFoundException;

/**
 * File-based word list implementation that loads word lists from external files.
 *
 * Supports the standard Diceware format:
 * - Each line: "NNNNN word" where N is digit 1-6
 * - Comments (lines starting with #) are ignored
 * - Empty lines are ignored
 * - Must contain exactly 7776 entries (6^5)
 * - Handles both Unix and Windows line endings
 */
class FileWordList implements WordListInterface
{
    private array $words = [];
    private bool $isValid = false;

    /**
     * Create a new file-based word list.
     *
     * @param string $filePath Path to the word list file
     * @throws WordListNotFoundException if the file doesn't exist or isn't readable
     * @throws InvalidWordListException if the word list format is invalid
     */
    public function __construct(string $filePath)
    {
        $this->loadWordList($filePath);
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
     * Load word list from a file.
     *
     * @param string $filePath Path to the word list file
     * @throws WordListNotFoundException if the file doesn't exist or isn't readable
     * @throws InvalidWordListException if the word list format is invalid
     */
    private function loadWordList(string $filePath): void
    {
        // Normalize the file path for cross-platform compatibility
        $normalizedPath = $this->normalizePath($filePath);

        if (!file_exists($normalizedPath)) {
            throw new WordListNotFoundException("Word list file not found: {$normalizedPath}");
        }

        if (is_dir($normalizedPath)) {
            throw new WordListNotFoundException("Word list path is a directory, not a file: {$normalizedPath}");
        }

        if (!is_readable($normalizedPath)) {
            throw new WordListNotFoundException("Word list file is not readable: {$normalizedPath}");
        }

        // Use more robust file reading with error context
        $content = $this->readFileContent($normalizedPath);

        $this->parseWordList($content);
    }

    /**
     * Normalize a file path for cross-platform compatibility.
     *
     * @param string $filePath The file path to normalize
     * @return string Normalized file path
     */
    private function normalizePath(string $filePath): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        $realPath = realpath($normalized);
        return $realPath !== false ? $realPath : $normalized;
    }

    /**
     * Read file content with robust error handling.
     *
     * @param string $filePath The file path to read
     * @return string File content
     * @throws WordListNotFoundException if file cannot be read
     */
    private function readFileContent(string $filePath): string
    {
        $errorMessage = '';
        set_error_handler(function ($severity, $message) use (&$errorMessage) {
            $errorMessage = $message;
        });

        $content = file_get_contents($filePath);

        restore_error_handler();

        if ($content === false) {
            $error = $errorMessage ?: 'Unknown error occurred while reading file';
            throw new WordListNotFoundException("Failed to read word list file '{$filePath}': {$error}");
        }

        return $this->removeBom($content);
    }

    /**
     * Remove Byte Order Mark (BOM) from content if present.
     *
     * @param string $content File content
     * @return string Content without BOM
     */
    private function removeBom(string $content): string
    {
        // UTF-8 BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }

        // UTF-16 BE BOM
        if (substr($content, 0, 2) === "\xFE\xFF") {
            return substr($content, 2);
        }

        // UTF-16 LE BOM
        if (substr($content, 0, 2) === "\xFF\xFE") {
            return substr($content, 2);
        }

        return $content;
    }

    /**
     * Parse word list content from string.
     *
     * @param string $content Raw file content
     * @throws InvalidWordListException if the word list format is invalid
     */
    private function parseWordList(string $content): void
    {
        // Normalize line endings for cross-platform compatibility
        // Handle Unix (\n), Windows (\r\n), and old Mac (\r) line endings
        $normalizedContent = $this->normalizeLineEndings($content);
        $lines = explode("\n", $normalizedContent);

        $lineNumber = 0;

        foreach ($lines as $line) {
            $lineNumber++;
            $originalLine = $line;
            $trimmedLine = trim($line);

            // Skip empty lines and comments
            if ($trimmedLine === '' || $this->isCommentLine($trimmedLine)) {
                continue;
            }

            $this->parseLine($originalLine, $lineNumber);
        }
    }

    /**
     * Normalize line endings to Unix format (\n).
     *
     * @param string $content Content with mixed line endings
     * @return string Content with normalized line endings
     */
    private function normalizeLineEndings(string $content): string
    {
        // Convert Windows line endings (\r\n) to Unix (\n)
        $content = str_replace("\r\n", "\n", $content);
        // Convert old Mac line endings (\r) to Unix (\n)
        return str_replace("\r", "\n", $content);
    }

    /**
     * Check if a line is a comment line.
     *
     * @param string $line Trimmed line content
     * @return bool True if the line is a comment
     */
    private function isCommentLine(string $line): bool
    {
        // Support multiple comment styles for flexibility
        return str_starts_with($line, '#') || str_starts_with($line, '//') || str_starts_with($line, ';');
    }

    /**
     * Parse a single line from the word list.
     *
     * @param string $line The line to parse
     * @param int $lineNumber Line number for error reporting
     * @throws InvalidWordListException if the line format is invalid
     */
    private function parseLine(string $line, int $lineNumber): void
    {
        // Expected format: "NNNNN word" where N is digit 1-6
        if (!preg_match('/^([1-6]{5})\s+(.*)$/', $line, $matches)) {
            $displayLine = $this->sanitizeLineForDisplay($line);
            throw new InvalidWordListException(
                "Invalid word list format at line {$lineNumber}: expected 'NNNNN word' where N is digit 1-6, got: {$displayLine}"
            );
        }

        $diceRoll = $matches[1];
        $word = trim($matches[2]);

        if ($word === '') {
            throw new InvalidWordListException("Empty word at line {$lineNumber} for dice roll: {$diceRoll}");
        }

        // Validate that the word doesn't contain problematic characters
        if ($this->containsProblematicCharacters($word)) {
            $sanitizedWord = $this->sanitizeLineForDisplay($word);
            throw new InvalidWordListException(
                "Word contains invalid characters at line {$lineNumber} for dice roll {$diceRoll}: {$sanitizedWord}"
            );
        }

        if (isset($this->words[$diceRoll])) {
            throw new InvalidWordListException("Duplicate dice roll '{$diceRoll}' found at line {$lineNumber}");
        }

        $this->words[$diceRoll] = $word;
    }

    /**
     * Sanitize a line for safe display in error messages.
     *
     * @param string $line The line to sanitize
     * @return string Sanitized line safe for display
     */
    private function sanitizeLineForDisplay(string $line): string
    {
        // Remove or replace potentially problematic characters for cross-platform display
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '?', $line);

        // Limit length to prevent extremely long error messages
        if (strlen($sanitized) > 100) {
            $sanitized = substr($sanitized, 0, 97) . '...';
        }

        return $sanitized;
    }

    /**
     * Check if a word contains characters that might cause issues.
     *
     * @param string $word The word to check
     * @return bool True if the word contains problematic characters
     */
    private function containsProblematicCharacters(string $word): bool
    {
        // Check for control characters that might cause issues
        return preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $word) === 1;
    }

    /**
     * Validate that the word list is complete and correct.
     *
     * @throws InvalidWordListException if word list is invalid
     */
    private function validateWordList(): void
    {
        $expectedCount = 7776; // 6^5
        $actualCount = count($this->words);

        if ($actualCount !== $expectedCount) {
            throw new InvalidWordListException(
                "Word list must contain exactly {$expectedCount} entries, found: {$actualCount}"
            );
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
                                    "Missing entry for dice roll: {$diceRoll}"
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->isValid = true;
    }
}
