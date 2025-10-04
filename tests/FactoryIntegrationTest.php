<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests;

use PHPUnit\Framework\TestCase;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;

/**
 * Integration tests for PassphraseGeneratorFactory methods.
 * 
 * Tests that all factory methods create working generators and that
 * the generators produce valid output.
 */
class FactoryIntegrationTest extends TestCase
{
    public function testCreateWithDefaultEnglishWordListIntegration(): void
    {
        // Test factory creation
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that generator produces valid output
        $passphrase = $generator->generateString(4);
        $this->assertIsString($passphrase);
        $this->assertNotEmpty($passphrase);
        
        // Verify word count in string (3 spaces for 4 words)
        $spaceCount = substr_count($passphrase, ' ');
        $this->assertEquals(3, $spaceCount);
        
        // Test array generation
        $words = $generator->generate(5);
        $this->assertIsArray($words);
        $this->assertCount(5, $words);
        $this->assertContainsOnly('string', $words);
        
        // Verify each word is valid
        foreach ($words as $word) {
            $this->assertNotEmpty($word);
            $this->assertIsString($word);
        }
    }
    
    public function testCreateWithEmbeddedWordListIntegration(): void
    {
        $customWordList = $this->createValidWordList();
        
        // Test factory creation
        $generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($customWordList);
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test generation with custom word list
        $passphrase = $generator->generateString(3, '-');
        $this->assertIsString($passphrase);
        $this->assertStringContainsString('-', $passphrase);
        $this->assertEquals(2, substr_count($passphrase, '-'));
        
        // Verify words come from custom list
        $words = explode('-', $passphrase);
        foreach ($words as $word) {
            $this->assertStringStartsWith('custom', $word);
        }
        
        // Test array generation
        $wordsArray = $generator->generate(2);
        $this->assertCount(2, $wordsArray);
        foreach ($wordsArray as $word) {
            $this->assertStringStartsWith('custom', $word);
        }
    }
    
    public function testCreateWithFileWordListIntegration(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test factory creation
            $generator = PassphraseGeneratorFactory::createWithFileWordList($filePath);
            $this->assertInstanceOf(PassphraseGenerator::class, $generator);
            
            // Test generation with file-based word list
            $passphrase = $generator->generateString(4, '_');
            $this->assertIsString($passphrase);
            $this->assertStringContainsString('_', $passphrase);
            $this->assertEquals(3, substr_count($passphrase, '_'));
            
            // Verify words come from file list
            $words = explode('_', $passphrase);
            foreach ($words as $word) {
                $this->assertStringStartsWith('file', $word);
            }
            
            // Test array generation
            $wordsArray = $generator->generate(3);
            $this->assertCount(3, $wordsArray);
            foreach ($wordsArray as $word) {
                $this->assertStringStartsWith('file', $word);
            }
            
        } finally {
            unlink($filePath);
        }
    }
    
    public function testCreateWithCustomDependenciesIntegration(): void
    {
        $wordList = new EmbeddedWordList($this->createValidWordList());
        $randomGenerator = new SecureRandomGenerator();
        
        // Test factory creation with both dependencies
        $generator = PassphraseGeneratorFactory::createWithCustomDependencies($wordList, $randomGenerator);
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test generation
        $passphrase = $generator->generateString(5);
        $this->assertIsString($passphrase);
        $this->assertEquals(4, substr_count($passphrase, ' '));
        
        // Test with null random generator (should use default)
        $generator2 = PassphraseGeneratorFactory::createWithCustomDependencies($wordList, null);
        $this->assertInstanceOf(PassphraseGenerator::class, $generator2);
        
        $passphrase2 = $generator2->generateString(3);
        $this->assertIsString($passphrase2);
        $this->assertEquals(2, substr_count($passphrase2, ' '));
    }
    
    public function testFactoryMethodsProduceDifferentResults(): void
    {
        // Create generators with different configurations
        $defaultGenerator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        $customGenerator = PassphraseGeneratorFactory::createWithEmbeddedWordList($this->createValidWordList());
        
        // Generate passphrases
        $defaultPassphrase = $defaultGenerator->generateString(4);
        $customPassphrase = $customGenerator->generateString(4);
        
        // They should be different (custom uses "custom" prefix)
        $this->assertNotEquals($defaultPassphrase, $customPassphrase);
        
        // Custom passphrase should contain "custom" words
        $customWords = explode(' ', $customPassphrase);
        foreach ($customWords as $word) {
            $this->assertStringStartsWith('custom', $word);
        }
    }
    
    public function testFactoryMethodsWithMultipleGenerations(): void
    {
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        
        // Generate multiple passphrases to test consistency
        $passphrases = [];
        for ($i = 0; $i < 10; $i++) {
            $passphrases[] = $generator->generateString(5);
        }
        
        // All should be valid strings
        foreach ($passphrases as $passphrase) {
            $this->assertIsString($passphrase);
            $this->assertNotEmpty($passphrase);
            $this->assertEquals(4, substr_count($passphrase, ' '));
        }
        
        // All should be unique (extremely unlikely to be the same)
        $uniquePassphrases = array_unique($passphrases);
        $this->assertCount(10, $uniquePassphrases);
    }
    
    public function testFactoryErrorHandlingIntegration(): void
    {
        // Test with invalid embedded word list
        $invalidWordList = ['11111' => 'test']; // Incomplete
        
        $this->expectException(\Vpap\DicePassphrase\Exception\InvalidWordListException::class);
        PassphraseGeneratorFactory::createWithEmbeddedWordList($invalidWordList);
    }
    
    public function testFactoryFileErrorHandlingIntegration(): void
    {
        // Test with non-existent file
        $this->expectException(\Vpap\DicePassphrase\Exception\WordListNotFoundException::class);
        PassphraseGeneratorFactory::createWithFileWordList('/nonexistent/path/file.txt');
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
        $filePath = tempnam(sys_get_temp_dir(), 'factory_test_wordlist_');
        $content = '';
        
        // Generate all possible dice roll combinations (11111 to 66666)
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            $content .= "{$diceRoll} file{$diceRoll}\n";
                        }
                    }
                }
            }
        }
        
        file_put_contents($filePath, $content);
        
        return $filePath;
    }
}