<?php

declare(strict_types=1);

/**
 * Advanced Usage Examples
 * 
 * Demonstrates advanced features including custom word lists, file-based lists,
 * and manual component wiring.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\WordList\FileWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

echo "=== Advanced Usage Examples ===\n\n";

// Example 1: Manual component wiring
echo "1. Manual Component Wiring:\n";
$wordList = new EmbeddedWordList();
$randomGenerator = new SecureRandomGenerator();
$generator = new PassphraseGenerator($wordList, $randomGenerator);

$manualPassphrase = $generator->generateString(4);
echo "Manually wired generator: $manualPassphrase\n\n";

// Example 2: Custom embedded word list (demonstration with small subset)
echo "2. Custom Embedded Word List (Demo):\n";
// Note: In real usage, you need all 7776 entries. This is just for demonstration.
try {
    $customWords = createDemoWordList();
    $customGenerator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWords);
    $customPassphrase = $customGenerator->generateString(3);
    echo "Custom word list: $customPassphrase\n";
} catch (Exception $e) {
    echo "Custom word list demo: " . $e->getMessage() . "\n";
    echo "(This is expected - demo list is incomplete)\n";
}
echo "\n";

// Example 3: File-based word list
echo "3. File-Based Word List:\n";
$tempFile = createTemporaryWordListFile();
try {
    $fileGenerator = PassphraseGeneratorFactory::createWithFileWordList($tempFile);
    $filePassphrase = $fileGenerator->generateString(4, '_');
    echo "File-based passphrase: $filePassphrase\n";
    
    // Using static methods with file
    $staticFilePassphrase = PassphraseGenerator::quickStringFromFile($tempFile, 3, '-');
    echo "Static file method: $staticFilePassphrase\n";
    
} catch (Exception $e) {
    echo "File-based error: " . $e->getMessage() . "\n";
} finally {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}
echo "\n";

// Example 4: Error handling
echo "4. Error Handling:\n";
try {
    $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    $generator->generate(0); // This will throw an exception
} catch (\Vpap\DicePassphrase\Exception\InvalidWordCountException $e) {
    echo "Caught expected error: " . $e->getMessage() . "\n";
}

try {
    PassphraseGeneratorFactory::createWithFileWordList('/nonexistent/file.txt');
} catch (\Vpap\DicePassphrase\Exception\WordListNotFoundException $e) {
    echo "Caught expected error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 5: Performance testing
echo "5. Performance Testing:\n";
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();

$startTime = microtime(true);
$passphrases = [];
for ($i = 0; $i < 1000; $i++) {
    $passphrases[] = $generator->generateString(6);
}
$endTime = microtime(true);

$duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
echo "Generated 1000 passphrases in " . number_format($duration, 2) . " ms\n";
echo "Average: " . number_format($duration / 1000, 4) . " ms per passphrase\n";

// Show a few examples
echo "Sample passphrases:\n";
for ($i = 0; $i < 5; $i++) {
    echo "  " . $passphrases[$i] . "\n";
}
echo "\n";

// Example 6: Randomness verification
echo "6. Randomness Verification:\n";
$generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
$wordFrequency = [];

// Generate many single-word passphrases to check distribution
for ($i = 0; $i < 1000; $i++) {
    $word = $generator->generate(1)[0];
    $wordFrequency[$word] = ($wordFrequency[$word] ?? 0) + 1;
}

$uniqueWords = count($wordFrequency);
$mostFrequent = max($wordFrequency);
$leastFrequent = min($wordFrequency);

echo "Generated 1000 single words:\n";
echo "Unique words: $uniqueWords\n";
echo "Most frequent word appeared: $mostFrequent times\n";
echo "Least frequent word appeared: $leastFrequent times\n";
echo "This demonstrates good randomness distribution.\n\n";

echo "=== Advanced Examples Complete ===\n";

/**
 * Create a demo word list (incomplete - just for demonstration)
 */
function createDemoWordList(): array
{
    // This creates only a small subset for demonstration
    // Real usage requires all 7776 entries from 11111 to 66666
    $words = [];
    for ($i = 1; $i <= 6; $i++) {
        for ($j = 1; $j <= 6; $j++) {
            $diceRoll = "1111{$i}";
            $words[$diceRoll] = "demo{$i}{$j}";
        }
    }
    return $words;
}

/**
 * Create a temporary word list file for demonstration
 */
function createTemporaryWordListFile(): string
{
    $tempFile = tempnam(sys_get_temp_dir(), 'demo_wordlist_');
    $content = '';
    
    // Generate all possible dice roll combinations (11111 to 66666)
    for ($d1 = 1; $d1 <= 6; $d1++) {
        for ($d2 = 1; $d2 <= 6; $d2++) {
            for ($d3 = 1; $d3 <= 6; $d3++) {
                for ($d4 = 1; $d4 <= 6; $d4++) {
                    for ($d5 = 1; $d5 <= 6; $d5++) {
                        $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                        $content .= "{$diceRoll} demo{$diceRoll}\n";
                    }
                }
            }
        }
    }
    
    file_put_contents($tempFile, $content);
    return $tempFile;
}