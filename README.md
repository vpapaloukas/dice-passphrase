# Dice Passphrase Generator

A PHP library for generating secure, memorable passphrases based on [EFF Dice-Generated Passphrases](https://www.eff.org/dice).

## Features

- **Secure Random Generation**: Uses PHP's `random_int()` for cryptographically secure randomness
- **Multiple Word Lists**: Support for embedded English word list and custom file-based word lists
- **Flexible Output**: Generate passphrases as arrays or formatted strings
- **Easy Integration**: Simple factory methods and static convenience functions
- **Full Validation**: Comprehensive word list validation and error handling
- **PHP 8.0+ Compatible**: Modern PHP with strict typing and best practices

## Installation

Install via Composer:

```bash
composer require vpapaloukas/dice-passphrase
```

## Quick Start

### Basic Usage

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Generate a 6-word passphrase (default)
$passphrase = PassphraseGenerator::quickString();
echo $passphrase; // "correct horse battery staple example word"

// Generate a 4-word passphrase
$passphrase = PassphraseGenerator::quickString(4);
echo $passphrase; // "secure random example phrase"

// Generate as array
$words = PassphraseGenerator::quick(5);
print_r($words); // ["secure", "random", "example", "phrase", "generator"]
```

### Using the Factory

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Create generator with default English word list
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

// Generate passphrases
$passphrase = $generator->generateString(6);
$words = $generator->generate(4);
```

## Advanced Usage

### Custom Separators

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Use custom separator
$passphrase = PassphraseGenerator::quickString(4, '-');
echo $passphrase; // "correct-horse-battery-staple"

// No separator (concatenated)
$passphrase = PassphraseGenerator::quickString(3, '');
echo $passphrase; // "correcthorsebattery"
```

### File-Based Word Lists

```php
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Using static methods
$passphrase = PassphraseGenerator::quickStringFromFile('/path/to/wordlist.txt', 5);

// Using factory
$generator = PassphraseGeneratorFactory::createWithFileWordList('/path/to/wordlist.txt');
$passphrase = $generator->generateString(6);
```

### Custom Word Lists

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Create custom word list (must have exactly 7776 entries)
$customWords = [
    '11111' => 'apple',
    '11112' => 'banana',
    // ... all combinations from 11111 to 66666
    '66666' => 'zebra'
];

$generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWords);
$passphrase = $generator->generateString(4);
```

### Manual Component Wiring

```php
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

// Manual dependency injection
$wordList = new EmbeddedWordList();
$randomGenerator = new SecureRandomGenerator();
$generator = new PassphraseGenerator($wordList, $randomGenerator);

$passphrase = $generator->generateString(5);
```

## Word List Format

Word lists must follow the standard Diceware format:

```
11111 word1
11112 word2
11113 word3
...
66666 word7776
```

### Requirements

- Exactly 7776 entries (6^5 possible dice roll combinations)
- Each line: five digits (1-6) + space + word
- All dice roll combinations from 11111 to 66666 must be present
- Empty lines and comments (starting with #) are ignored

### Example Word List File

```
# English Diceware Word List
# Comments are ignored

11111 abacus
11112 abdomen
11113 abdominal
# ... more entries
66664 zoom
66665 zoology
66666 zucchini
```

## API Reference

### PassphraseGenerator

#### Constructor

```php
public function __construct(
    WordListInterface $wordList,
    SecureRandomGenerator $randomGenerator
)
```

#### Methods

```php
// Generate passphrase as array
public function generate(int $wordCount = 6): array

// Generate passphrase as string
public function generateString(int $wordCount = 6, string $separator = ' '): string

// Static convenience methods
public static function quick(int $wordCount = 6): array
public static function quickString(int $wordCount = 6, string $separator = ' '): string
public static function quickFromFile(string $filePath, int $wordCount = 6): array
public static function quickStringFromFile(string $filePath, int $wordCount = 6, string $separator = ' '): string
```

### PassphraseGeneratorFactory

```php
// Create with default English word list
public static function createWithDefaultEnglishWordList(): PassphraseGenerator

// Create with custom embedded word list
public static function createWithEmbeddedWordList(array $wordList): PassphraseGenerator

// Create with file-based word list
public static function createWithFileWordList(string $filePath): PassphraseGenerator

// Create with custom dependencies
public static function createWithCustomDependencies(
    WordListInterface $wordList,
    ?SecureRandomGenerator $randomGenerator = null
): PassphraseGenerator
```

## Error Handling

The library throws specific exceptions for different error conditions:

```php
use Vpap\DicePassphrase\Exception\InvalidWordCountException;
use Vpap\DicePassphrase\Exception\InvalidWordListException;
use Vpap\DicePassphrase\Exception\WordListNotFoundException;

try {
    $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    $passphrase = $generator->generate(0); // Invalid word count
} catch (InvalidWordCountException $e) {
    echo "Invalid word count: " . $e->getMessage();
} catch (InvalidWordListException $e) {
    echo "Invalid word list: " . $e->getMessage();
} catch (WordListNotFoundException $e) {
    echo "Word list file not found: " . $e->getMessage();
}
```

## Security Considerations

- Uses `random_int()` for cryptographically secure random number generation
- Each dice roll is equivalent to rolling five physical dice
- Word selection is truly random and unpredictable
- No entropy is lost in the generation process
- Suitable for generating passwords and security tokens

## Performance

- **Memory Efficient**: Word lists are loaded once and cached
- **Fast Lookup**: O(1) word lookup using associative arrays
- **Minimal Overhead**: No unnecessary object creation during generation
- **Scalable**: Can generate thousands of passphrases efficiently

## Requirements

- PHP 8.0 or higher
- No external dependencies for core functionality
- PHPUnit 10+ for development and testing

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Based on the Diceware method created by Arnold Reinhold
- Uses the standard English Diceware word list
- Inspired by the need for secure, memorable passphrases

## Examples

### Generate Multiple Passphrases

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

// Generate 10 different passphrases
for ($i = 0; $i < 10; $i++) {
    echo $generator->generateString(5) . "\n";
}
```

### Password Strength Comparison

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Different security levels
$weak = PassphraseGenerator::quickString(3);      // ~77 bits of entropy
$strong = PassphraseGenerator::quickString(5);    // ~129 bits of entropy  
$very_strong = PassphraseGenerator::quickString(7); // ~180 bits of entropy

echo "Weak (3 words): $weak\n";
echo "Strong (5 words): $strong\n";
echo "Very Strong (7 words): $very_strong\n";
```

### Integration with Authentication Systems

```php
use Vpap\DicePassphrase\PassphraseGenerator;

class UserRegistration 
{
    public function generateTemporaryPassword(): string
    {
        // Generate a secure temporary password
        return PassphraseGenerator::quickString(4, '-');
    }
    
    public function generateRecoveryCode(): string
    {
        // Generate a recovery code without spaces
        return PassphraseGenerator::quickString(6, '');
    }
}
```