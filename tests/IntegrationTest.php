<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests;

use PHPUnit\Framework\TestCase;
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\WordList\FileWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

/**
 * Integration tests for complete library functionality.
 * 
 * Tests end-to-end workflows and component integration to ensure
 * all parts work together seamlessly.
 */
class IntegrationTest extends TestCase
{
    public function testCompleteWorkflowWithDefaultEnglishWordList(): void
    {
        // Test factory creation with default English word list
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test array generation
        $passphraseArray = $generator->generate(5);
        $this->assertIsArray($passphraseArray);
        $this->assertCount(5, $passphraseArray);
        $this->assertContainsOnly('string', $passphraseArray);
        
        // Verify each word is non-empty
        foreach ($passphraseArray as $word) {
            $this->assertNotEmpty($word);
            $this->assertIsString($word);
        }
        
        // Test string generation
        $passphraseString = $generator->generateString(4);
        $this->assertIsString($passphraseString);
        $this->assertNotEmpty($passphraseString);
        
        // Verify string has correct number of words (3 spaces for 4 words)
        $wordCount = substr_count($passphraseString, ' ') + 1;
        $this->assertEquals(4, $wordCount);
    }
    
    public function testCompleteWorkflowWithFileWordList(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test factory creation with file word list
            $generator = PassphraseGeneratorFactory::createWithFileWordList($filePath);
            $this->assertInstanceOf(PassphraseGenerator::class, $generator);
            
            // Test generation
            $passphrase = $generator->generate(3);
            $this->assertCount(3, $passphrase);
            $this->assertContainsOnly('string', $passphrase);
            
            // Test string generation with custom separator
            $passphraseString = $generator->generateString(2, '-');
            $this->assertStringContainsString('-', $passphraseString);
            $this->assertEquals(1, substr_count($passphraseString, '-'));
            
        } finally {
            unlink($filePath);
        }
    }
    
    public function testCompleteWorkflowWithCustomEmbeddedWordList(): void
    {
        $customWordList = $this->createValidWordList();
        
        // Test factory creation with custom embedded word list
        $generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWordList);
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test generation
        $passphrase = $generator->generate(6);
        $this->assertCount(6, $passphrase);
        $this->assertContainsOnly('string', $passphrase);
        
        // Verify words come from our custom list
        foreach ($passphrase as $word) {
            $this->assertStringStartsWith('custom', $word);
        }
    }
    
    public function testStaticConvenienceMethods(): void
    {
        // Test quick method
        $quickPassphrase = PassphraseGenerator::quick(4);
        $this->assertIsArray($quickPassphrase);
        $this->assertCount(4, $quickPassphrase);
        $this->assertContainsOnly('string', $quickPassphrase);
        
        // Test quickString method
        $quickString = PassphraseGenerator::quickString(3);
        $this->assertIsString($quickString);
        $this->assertNotEmpty($quickString);
        $this->assertEquals(2, substr_count($quickString, ' '));
        
        // Test quickString with custom separator
        $quickStringCustom = PassphraseGenerator::quickString(2, '|');
        $this->assertStringContainsString('|', $quickStringCustom);
        $this->assertEquals(1, substr_count($quickStringCustom, '|'));
    }
    
    public function testStaticConvenienceMethodsWithFile(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test quickFromFile method
            $quickFromFile = PassphraseGenerator::quickFromFile($filePath, 3);
            $this->assertIsArray($quickFromFile);
            $this->assertCount(3, $quickFromFile);
            $this->assertContainsOnly('string', $quickFromFile);
            
            // Test quickStringFromFile method
            $quickStringFromFile = PassphraseGenerator::quickStringFromFile($filePath, 2, '_');
            $this->assertIsString($quickStringFromFile);
            $this->assertStringContainsString('_', $quickStringFromFile);
            $this->assertEquals(1, substr_count($quickStringFromFile, '_'));
            
        } finally {
            unlink($filePath);
        }
    }
    
    public function testRandomnessAndUniqueness(): void
    {
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        
        // Generate multiple passphrases to test randomness
        $passphrases = [];
        for ($i = 0; $i < 10; $i++) {
            $passphrases[] = $generator->generateString(6);
        }
        
        // Verify all passphrases are different (extremely unlikely to be the same)
        $uniquePassphrases = array_unique($passphrases);
        $this->assertCount(10, $uniquePassphrases, 'Generated passphrases should be unique');
        
        // Verify each passphrase has the expected format
        foreach ($passphrases as $passphrase) {
            $this->assertIsString($passphrase);
            $this->assertNotEmpty($passphrase);
            $this->assertEquals(5, substr_count($passphrase, ' '), 'Should have 5 spaces for 6 words');
        }
    }
    
    public function testComponentIntegration(): void
    {
        // Test manual component wiring
        $wordList = new EmbeddedWordList();
        $randomGenerator = new SecureRandomGenerator();
        $generator = new PassphraseGenerator($wordList, $randomGenerator);
        
        // Verify components work together
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that random generator produces valid dice rolls
        $diceRoll = $randomGenerator->generateDiceRoll();
        $this->assertMatchesRegularExpression('/^[1-6]{5}$/', $diceRoll);
        
        // Test that word list can resolve the dice roll
        $word = $wordList->getWord($diceRoll);
        $this->assertIsString($word);
        $this->assertNotEmpty($word);
        
        // Test complete generation workflow
        $passphrase = $generator->generate(1);
        $this->assertCount(1, $passphrase);
        $this->assertIsString($passphrase[0]);
        $this->assertNotEmpty($passphrase[0]);
    }
    
    public function testErrorHandlingIntegration(): void
    {
        // Test that invalid word counts are handled properly throughout the system
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        
        $this->expectException(\Vpap\DicePassphrase\Exception\InvalidWordCountException::class);
        $generator->generate(0);
    }
    
    public function testWordListValidationIntegration(): void
    {
        // Test that word list validation works with the complete system
        $invalidWordList = ['11111' => 'test']; // Incomplete word list
        
        $this->expectException(\Vpap\DicePassphrase\Exception\InvalidWordListException::class);
        PassphraseGeneratorFactory::createWithEmbeddedWordList($invalidWordList);
    }
    
    private function createValidWordList(): array
    {
        $words = [];
        
        // Generate all possible dice roll combinations (11111 to 66666)
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            $words[$diceRoll] = "custom{$diceRoll}";
                        }
                    }
                }
            }
        }
        
        return $words;
    }
    
    private function createValidWordListFile(): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'integration_test_wordlist_');
        $content = '';
        
        // Generate all possible dice roll combinations (11111 to 66666)
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            $content .= "{$diceRoll} fileword{$diceRoll}\n";
                        }
                    }
                }
            }
        }
        
        file_put_contents($filePath, $content);
        
        return $filePath;
    }
}