# API Documentation

This document provides comprehensive API documentation for the Dice Passphrase Generator library.

## Table of Contents

- [PassphraseGenerator](#passphrasegenerator)
- [PassphraseGeneratorFactory](#passphrasegeneratorfactory)
- [WordListInterface](#wordlistinterface)
- [EmbeddedWordList](#embeddedwordlist)
- [FileWordList](#filewordlist)
- [SecureRandomGenerator](#securerandomgenerator)
- [Exceptions](#exceptions)

## PassphraseGenerator

The main class for generating passphrases using the Diceware method.

### Constructor

```php
public function __construct(
    WordListInterface $wordList,
    SecureRandomGenerator $randomGenerator
)
```

Creates a new passphrase generator with the specified dependencies.

**Parameters:**
- `$wordList` - Word list implementation to use for word selection
- `$randomGenerator` - Random number generator for dice rolls

### Instance Methods

#### generate()

```php
public function generate(int $wordCount = 6): array
```

Generate a passphrase as an array of words.

**Parameters:**
- `$wordCount` - Number of words to include in the passphrase (default: 6)

**Returns:** Array of words selected from the word list

**Throws:** `InvalidWordCountException` if word count is less than 1

**Example:**
```php
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
$words = $generator->generate(5);
// Returns: ["correct", "horse", "battery", "staple", "example"]
```

#### generateString()

```php
public function generateString(int $wordCount = 6, string $separator = ' '): string
```

Generate a passphrase as a separated string.

**Parameters:**
- `$wordCount` - Number of words to include in the passphrase (default: 6)
- `$separator` - Character(s) to use for joining words (default: ' ')

**Returns:** String of words joined with the specified separator

**Throws:** `InvalidWordCountException` if word count is less than 1

**Example:**
```php
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
$passphrase = $generator->generateString(4, '-');
// Returns: "correct-horse-battery-staple"
```

### Static Methods

#### quick()

```php
public static function quick(int $wordCount = 6): array
```

Static convenience method to quickly generate a passphrase with default settings.

**Parameters:**
- `$wordCount` - Number of words to include in the passphrase (default: 6)

**Returns:** Array of words selected from the default English word list

**Throws:** `InvalidWordCountException` if word count is less than 1

**Example:**
```php
$words = PassphraseGenerator::quick(4);
// Returns: ["secure", "random", "example", "phrase"]
```

#### quickString()

```php
public static function quickString(int $wordCount = 6, string $separator = ' '): string
```

Static convenience method to quickly generate a passphrase string with default settings.

**Parameters:**
- `$wordCount` - Number of words to include in the passphrase (default: 6)
- `$separator` - Character(s) to use for joining words (default: ' ')

**Returns:** String of words from the default English word list

**Throws:** `InvalidWordCountException` if word count is less than 1

**Example:**
```php
$passphrase = PassphraseGenerator::quickString(3, '_');
// Returns: "secure_random_phrase"
```

#### quickFromFile()

```php
public static function quickFromFile(string $filePath, int $wordCount = 6): array
```

Static convenience method to generate a passphrase from a file-based word list.

**Parameters:**
- `$filePath` - Path to the word list file
- `$wordCount` - Number of words to include in the passphrase (default: 6)

**Returns:** Array of words selected from the file-based word list

**Throws:** 
- `InvalidWordCountException` if word count is less than 1
- `WordListNotFoundException` if file is not found
- `InvalidWordListException` if file format is invalid

**Example:**
```php
$words = PassphraseGenerator::quickFromFile('/path/to/wordlist.txt', 5);
// Returns: ["word1", "word2", "word3", "word4", "word5"]
```

#### quickStringFromFile()

```php
public static function quickStringFromFile(
    string $filePath, 
    int $wordCount = 6, 
    string $separator = ' '
): string
```

Static convenience method to generate a passphrase string from a file-based word list.

**Parameters:**
- `$filePath` - Path to the word list file
- `$wordCount` - Number of words to include in the passphrase (default: 6)
- `$separator` - Character(s) to use for joining words (default: ' ')

**Returns:** String of words from the file-based word list

**Throws:** 
- `InvalidWordCountException` if word count is less than 1
- `WordListNotFoundException` if file is not found
- `InvalidWordListException` if file format is invalid

**Example:**
```php
$passphrase = PassphraseGenerator::quickStringFromFile('/path/to/wordlist.txt', 4, '-');
// Returns: "word1-word2-word3-word4"
```

## PassphraseGeneratorFactory

Factory class for creating PassphraseGenerator instances with common configurations.

### Static Methods

#### createWithDefaultEnglishWordList()

```php
public static function createWithDefaultEnglishWordList(): PassphraseGenerator
```

Create a passphrase generator with the default English word list.

**Returns:** Generator configured with default English word list

**Example:**
```php
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
```

#### createWithEmbeddedWordList()

```php
public static function createWithEmbeddedWordList(array $wordList): PassphraseGenerator
```

Create a passphrase generator with a custom embedded word list.

**Parameters:**
- `$wordList` - Associative array with dice roll keys (11111-66666) and word values

**Returns:** Generator configured with the provided word list

**Throws:** `InvalidWordListException` if word list is invalid

**Example:**
```php
$customWords = [
    '11111' => 'apple',
    '11112' => 'banana',
    // ... all 7776 entries
    '66666' => 'zebra'
];
$generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWords);
```

#### createWithFileWordList()

```php
public static function createWithFileWordList(string $filePath): PassphraseGenerator
```

Create a passphrase generator with a file-based word list.

**Parameters:**
- `$filePath` - Path to the word list file

**Returns:** Generator configured with the file-based word list

**Throws:** 
- `WordListNotFoundException` if file is not found
- `InvalidWordListException` if file format is invalid

**Example:**
```php
$generator = PassphraseGeneratorFactory::createWithFileWordList('/path/to/wordlist.txt');
```

#### createWithCustomDependencies()

```php
public static function createWithCustomDependencies(
    WordListInterface $wordList,
    ?SecureRandomGenerator $randomGenerator = null
): PassphraseGenerator
```

Create a passphrase generator with custom dependencies.

**Parameters:**
- `$wordList` - Word list implementation
- `$randomGenerator` - Random generator (uses default if null)

**Returns:** Generator configured with the provided dependencies

**Example:**
```php
$wordList = new EmbeddedWordList();
$randomGenerator = new SecureRandomGenerator();
$generator = PassphraseGeneratorFactory::createWithCustomDependencies($wordList, $randomGenerator);
```

## WordListInterface

Interface for word list implementations used in passphrase generation.

### Methods

#### getWord()

```php
public function getWord(string $diceRoll): string
```

Get a word for the given dice roll.

**Parameters:**
- `$diceRoll` - Five-digit string with digits 1-6 (e.g., "12345")

**Returns:** The corresponding word from the word list

**Throws:** `InvalidWordListException` if dice roll is not found

#### isValid()

```php
public function isValid(): bool
```

Check if the word list is valid and complete.

**Returns:** True if word list contains exactly 7776 valid entries

#### getWordCount()

```php
public function getWordCount(): int
```

Get the total number of words in the word list.

**Returns:** Number of words in the list (should be 7776 for valid lists)

## EmbeddedWordList

Embedded word list implementation with built-in English word list.

### Constructor

```php
public function __construct(?array $wordList = null)
```

Create a new embedded word list.

**Parameters:**
- `$wordList` - Optional custom word list array. If null, uses default English list.

**Throws:** `InvalidWordListException` if provided word list is invalid

**Example:**
```php
// Use default English word list
$wordList = new EmbeddedWordList();

// Use custom word list
$customWords = [...]; // 7776 entries
$wordList = new EmbeddedWordList($customWords);
```

## FileWordList

File-based word list implementation that loads word lists from external files.

### Constructor

```php
public function __construct(string $filePath)
```

Create a new file-based word list.

**Parameters:**
- `$filePath` - Path to the word list file

**Throws:** 
- `WordListNotFoundException` if file is not found
- `InvalidWordListException` if file format is invalid

**Example:**
```php
$wordList = new FileWordList('/path/to/wordlist.txt');
```

## SecureRandomGenerator

Generates cryptographically secure random numbers for dice roll simulation.

### Methods

#### generateDiceRoll()

```php
public function generateDiceRoll(): string
```

Generate a five-digit dice roll string.

**Returns:** Five-digit string with each digit between 1-6 (e.g., "34521")

**Throws:** `\Exception` if secure random number generation fails

**Example:**
```php
$generator = new SecureRandomGenerator();
$diceRoll = $generator->generateDiceRoll();
// Returns: "45231" (example)
```

## Exceptions

### InvalidWordCountException

Thrown when an invalid word count is provided (less than 1).

```php
namespace Vpap\DicePassphrase\Exception;

class InvalidWordCountException extends \InvalidArgumentException
```

### InvalidWordListException

Thrown when a word list is invalid or incomplete.

```php
namespace Vpap\DicePassphrase\Exception;

class InvalidWordListException extends \InvalidArgumentException
```

### WordListNotFoundException

Thrown when a word list file cannot be found.

```php
namespace Vpap\DicePassphrase\Exception;

class WordListNotFoundException extends \RuntimeException
```

## Usage Patterns

### Basic Usage

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Quick generation
$passphrase = PassphraseGenerator::quickString(5);
```

### Factory Usage

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
$passphrase = $generator->generateString(6);
```

### Custom Configuration

```php
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

$wordList = new EmbeddedWordList();
$randomGenerator = new SecureRandomGenerator();
$generator = new PassphraseGenerator($wordList, $randomGenerator);

$passphrase = $generator->generateString(4, '-');
```

### Error Handling

```php
use Vpap\DicePassphrase\Exception\InvalidWordCountException;
use Vpap\DicePassphrase\Exception\InvalidWordListException;
use Vpap\DicePassphrase\Exception\WordListNotFoundException;

try {
    $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    $passphrase = $generator->generate(5);
} catch (InvalidWordCountException $e) {
    // Handle invalid word count
} catch (InvalidWordListException $e) {
    // Handle invalid word list
} catch (WordListNotFoundException $e) {
    // Handle missing word list file
}
```