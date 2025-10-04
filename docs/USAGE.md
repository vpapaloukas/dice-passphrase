# Usage Guide

This guide provides comprehensive instructions for using the Dice Passphrase Generator library in various scenarios.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
- [Word Lists](#word-lists)
- [Security Considerations](#security-considerations)
- [Performance Tips](#performance-tips)
- [Common Patterns](#common-patterns)
- [Troubleshooting](#troubleshooting)

## Installation

Install the library using Composer:

```bash
composer require vpapaloukas/dice-passphrase
```

## Quick Start

The fastest way to generate a passphrase:

```php
<?php
require_once 'vendor/autoload.php';

use Vpap\DicePassphrase\PassphraseGenerator;

// Generate a 6-word passphrase (default)
$passphrase = PassphraseGenerator::quickString();
echo $passphrase; // "correct horse battery staple example word"
```

## Basic Usage

### Generating Passphrases

#### As String (Most Common)

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Default 6 words with spaces
$passphrase = PassphraseGenerator::quickString();
echo $passphrase; // "word1 word2 word3 word4 word5 word6"

// Custom word count
$short = PassphraseGenerator::quickString(4);
echo $short; // "word1 word2 word3 word4"

// Custom separator
$hyphenated = PassphraseGenerator::quickString(5, '-');
echo $hyphenated; // "word1-word2-word3-word4-word5"
```

#### As Array

```php
use Vpap\DicePassphrase\PassphraseGenerator;

// Generate as array of words
$words = PassphraseGenerator::quick(4);
print_r($words); // ["word1", "word2", "word3", "word4"]

// Process individual words
foreach ($words as $index => $word) {
    echo "Word " . ($index + 1) . ": $word\n";
}
```

### Using the Factory

For more control, use the factory pattern:

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Create generator instance
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

// Generate multiple passphrases
$passphrase1 = $generator->generateString(5);
$passphrase2 = $generator->generateString(7, '_');
$words = $generator->generate(4);
```

## Advanced Usage

### Custom Word Lists

#### Embedded Custom Word List

```php
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Create custom word list (must have exactly 7776 entries)
$customWords = [];
for ($d1 = 1; $d1 <= 6; $d1++) {
    for ($d2 = 1; $d2 <= 6; $d2++) {
        for ($d3 = 1; $d3 <= 6; $d3++) {
            for ($d4 = 1; $d4 <= 6; $d4++) {
                for ($d5 = 1; $d5 <= 6; $d5++) {
                    $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                    $customWords[$diceRoll] = "custom{$diceRoll}";
                }
            }
        }
    }
}

$generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWords);
$passphrase = $generator->generateString(4);
```

#### File-Based Word List

```php
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

// Using static methods
$passphrase = PassphraseGenerator::quickStringFromFile('/path/to/wordlist.txt', 5);

// Using factory
$generator = PassphraseGeneratorFactory::createWithFileWordList('/path/to/wordlist.txt');
$passphrase = $generator->generateString(6);
```

### Manual Component Wiring

For maximum control:

```php
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

// Create components manually
$wordList = new EmbeddedWordList();
$randomGenerator = new SecureRandomGenerator();
$generator = new PassphraseGenerator($wordList, $randomGenerator);

// Generate passphrases
$passphrase = $generator->generateString(5, '-');
```

## Word Lists

### Format Requirements

Word lists must follow the Diceware format:

```
11111 word1
11112 word2
11113 word3
...
66666 word7776
```

### Creating Custom Word Lists

#### File Format

```
# Comments start with # and are ignored
# Empty lines are also ignored

11111 apple
11112 banana
11113 cherry
# ... continue for all 7776 combinations
66664 yellow
66665 zebra
66666 zucchini
```

#### Validation Rules

- Exactly 7776 entries (6^5 combinations)
- Each line: five digits (1-6) + space + word
- All combinations from 11111 to 66666 must be present
- Words should not contain problematic characters
- Both Unix and Windows line endings are supported

### Loading Word Lists

```php
use Vpap\DicePassphrase\WordList\FileWordList;

try {
    $wordList = new FileWordList('/path/to/wordlist.txt');
    echo "Word list loaded successfully\n";
    echo "Word count: " . $wordList->getWordCount() . "\n";
    echo "Is valid: " . ($wordList->isValid() ? 'Yes' : 'No') . "\n";
} catch (\Vpap\DicePassphrase\Exception\WordListNotFoundException $e) {
    echo "File not found: " . $e->getMessage() . "\n";
} catch (\Vpap\DicePassphrase\Exception\InvalidWordListException $e) {
    echo "Invalid word list: " . $e->getMessage() . "\n";
}
```

## Security Considerations

### Randomness Quality

The library uses PHP's `random_int()` function, which provides cryptographically secure random numbers:

```php
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

$generator = new SecureRandomGenerator();
$diceRoll = $generator->generateDiceRoll();
// Each digit is truly random (1-6)
```

### Entropy Calculation

- Each word adds ~12.9 bits of entropy (log2(7776))
- 4 words ≈ 52 bits (adequate for most uses)
- 5 words ≈ 65 bits (strong)
- 6 words ≈ 78 bits (very strong)
- 8+ words ≈ 103+ bits (maximum security)

### Best Practices

```php
// For different security levels
$basic = PassphraseGenerator::quickString(4);      // ~52 bits
$strong = PassphraseGenerator::quickString(6);     // ~78 bits
$maximum = PassphraseGenerator::quickString(8);    // ~103 bits

// For high-security applications
$highSecurity = PassphraseGenerator::quickString(10, '-');
```

## Performance Tips

### Reuse Generator Instances

```php
// Good: Reuse generator
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
for ($i = 0; $i < 1000; $i++) {
    $passphrases[] = $generator->generateString(6);
}

// Avoid: Creating new generator each time
for ($i = 0; $i < 1000; $i++) {
    $passphrases[] = PassphraseGenerator::quickString(6); // Less efficient
}
```

### Memory Usage

```php
// Word lists are loaded once and cached
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

// Multiple generations use the same cached word list
$passphrase1 = $generator->generateString(5);
$passphrase2 = $generator->generateString(7);
$passphrase3 = $generator->generateString(4);
```

### Batch Generation

```php
function generateBatch(int $count, int $wordCount = 6): array {
    $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    $passphrases = [];
    
    for ($i = 0; $i < $count; $i++) {
        $passphrases[] = $generator->generateString($wordCount);
    }
    
    return $passphrases;
}

$batch = generateBatch(100, 5); // Generate 100 passphrases efficiently
```

## Common Patterns

### User Registration

```php
class UserService {
    private $generator;
    
    public function __construct() {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateTemporaryPassword(): string {
        return $this->generator->generateString(4, '-');
    }
    
    public function generateRecoveryCode(): string {
        return strtoupper($this->generator->generateString(6, ''));
    }
}
```

### API Key Generation

```php
class ApiKeyGenerator {
    public function generateApiKey(): string {
        $passphrase = PassphraseGenerator::quickString(8, '');
        return base64_encode($passphrase);
    }
    
    public function generateSecretKey(): string {
        return PassphraseGenerator::quickString(12, '-');
    }
}
```

### Configuration Secrets

```php
class ConfigGenerator {
    public function generateSecrets(): array {
        return [
            'APP_KEY' => PassphraseGenerator::quickString(8, ''),
            'JWT_SECRET' => PassphraseGenerator::quickString(10, '-'),
            'ENCRYPTION_KEY' => PassphraseGenerator::quickString(12, ''),
        ];
    }
}
```

### Testing Data

```php
class TestDataGenerator {
    public function generateTestUsers(int $count): array {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $words = PassphraseGenerator::quick(2);
            $users[] = [
                'username' => strtolower($words[0] . '_' . $words[1]),
                'password' => PassphraseGenerator::quickString(4, '-'),
                'email' => strtolower($words[0] . '.' . $words[1] . '@example.com')
            ];
        }
        return $users;
    }
}
```

## Troubleshooting

### Common Issues

#### Invalid Word Count

```php
try {
    $passphrase = PassphraseGenerator::quickString(0);
} catch (\Vpap\DicePassphrase\Exception\InvalidWordCountException $e) {
    echo "Error: " . $e->getMessage(); // "Word count must be at least 1, got: 0"
}
```

#### File Not Found

```php
try {
    $generator = PassphraseGeneratorFactory::createWithFileWordList('/nonexistent/file.txt');
} catch (\Vpap\DicePassphrase\Exception\WordListNotFoundException $e) {
    echo "Error: " . $e->getMessage(); // File path and error details
}
```

#### Invalid Word List Format

```php
try {
    $invalidWords = ['11111' => 'test']; // Incomplete
    $generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($invalidWords);
} catch (\Vpap\DicePassphrase\Exception\InvalidWordListException $e) {
    echo "Error: " . $e->getMessage(); // Details about what's wrong
}
```

### Debugging

#### Check Word List Validity

```php
use Vpap\DicePassphrase\WordList\FileWordList;

$wordList = new FileWordList('/path/to/wordlist.txt');
echo "Valid: " . ($wordList->isValid() ? 'Yes' : 'No') . "\n";
echo "Word count: " . $wordList->getWordCount() . "\n";

// Test specific dice roll
try {
    $word = $wordList->getWord('12345');
    echo "Word for 12345: $word\n";
} catch (Exception $e) {
    echo "Error getting word: " . $e->getMessage() . "\n";
}
```

#### Verify Randomness

```php
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

// Generate multiple passphrases to check uniqueness
$passphrases = [];
for ($i = 0; $i < 10; $i++) {
    $passphrases[] = $generator->generateString(4);
}

$unique = array_unique($passphrases);
echo "Generated: " . count($passphrases) . "\n";
echo "Unique: " . count($unique) . "\n";
echo "All unique: " . (count($passphrases) === count($unique) ? 'Yes' : 'No') . "\n";
```

### Performance Monitoring

```php
function benchmarkGeneration(int $count): void {
    $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    
    $start = microtime(true);
    for ($i = 0; $i < $count; $i++) {
        $generator->generateString(6);
    }
    $end = microtime(true);
    
    $duration = ($end - $start) * 1000; // Convert to milliseconds
    echo "Generated $count passphrases in " . number_format($duration, 2) . " ms\n";
    echo "Average: " . number_format($duration / $count, 4) . " ms per passphrase\n";
}

benchmarkGeneration(1000);
```

## Best Practices Summary

1. **Reuse generator instances** for better performance
2. **Use appropriate word counts** for your security requirements
3. **Handle exceptions** properly in production code
4. **Validate custom word lists** before using them
5. **Consider separators** based on your use case
6. **Test randomness** in critical applications
7. **Monitor performance** for high-volume usage
8. **Keep word lists secure** if using custom ones