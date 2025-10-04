<?php

declare(strict_types=1);

/**
 * Basic Usage Examples
 * 
 * Demonstrates the most common ways to use the Dice Passphrase Generator library.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

echo "=== Basic Usage Examples ===\n\n";

// Example 1: Quick passphrase generation (most common use case)
echo "1. Quick Passphrase Generation:\n";
$passphrase = PassphraseGenerator::quickString();
echo "Default (6 words): $passphrase\n";

$shortPassphrase = PassphraseGenerator::quickString(4);
echo "Short (4 words): $shortPassphrase\n";

$longPassphrase = PassphraseGenerator::quickString(8);
echo "Long (8 words): $longPassphrase\n\n";

// Example 2: Generate as array
echo "2. Generate as Array:\n";
$words = PassphraseGenerator::quick(5);
echo "Words array: " . json_encode($words) . "\n";
echo "Individual words:\n";
foreach ($words as $index => $word) {
    echo "  Word " . ($index + 1) . ": $word\n";
}
echo "\n";

// Example 3: Custom separators
echo "3. Custom Separators:\n";
$hyphenated = PassphraseGenerator::quickString(4, '-');
echo "Hyphenated: $hyphenated\n";

$underscored = PassphraseGenerator::quickString(4, '_');
echo "Underscored: $underscored\n";

$concatenated = PassphraseGenerator::quickString(4, '');
echo "Concatenated: $concatenated\n";

$spaced = PassphraseGenerator::quickString(3, ' | ');
echo "Custom separator: $spaced\n\n";

// Example 4: Using the factory
echo "4. Using Factory Methods:\n";
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

$factoryPassphrase = $generator->generateString(5);
echo "Factory generated: $factoryPassphrase\n";

$factoryArray = $generator->generate(3);
echo "Factory array: " . json_encode($factoryArray) . "\n\n";

// Example 5: Multiple passphrases
echo "5. Generate Multiple Passphrases:\n";
for ($i = 1; $i <= 5; $i++) {
    $passphrase = PassphraseGenerator::quickString(4);
    echo "Passphrase $i: $passphrase\n";
}
echo "\n";

// Example 6: Different security levels
echo "6. Different Security Levels:\n";
$low = PassphraseGenerator::quickString(3);
echo "Low security (3 words): $low\n";

$medium = PassphraseGenerator::quickString(5);
echo "Medium security (5 words): $medium\n";

$high = PassphraseGenerator::quickString(7);
echo "High security (7 words): $high\n";

$maximum = PassphraseGenerator::quickString(10);
echo "Maximum security (10 words): $maximum\n\n";

echo "=== Examples Complete ===\n";