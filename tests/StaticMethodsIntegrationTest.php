<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests;

use PHPUnit\Framework\TestCase;
use Vpap\DicePassphrase\PassphraseGenerator;

/**
 * Integration tests for static convenience methods.
 * 
 * Tests that all static methods work correctly and produce valid output
 * in real-world scenarios.
 */
class StaticMethodsIntegrationTest extends TestCase
{
    public function testQuickMethodIntegration(): void
    {
        // Test default word count
        $words = PassphraseGenerator::quick();
        $this->assertIsArray($words);
        $this->assertCount(6, $words); // Default is 6 words
        $this->assertContainsOnly('string', $words);
        
        // Verify each word is valid
        foreach ($words as $word) {
            $this->assertNotEmpty($word);
            $this->assertIsString($word);
        }
        
        // Test custom word count
        $shortWords = PassphraseGenerator::quick(3);
        $this->assertCount(3, $shortWords);
        $this->assertContainsOnly('string', $shortWords);
        
        $longWords = PassphraseGenerator::quick(10);
        $this->assertCount(10, $longWords);
        $this->assertContainsOnly('string', $longWords);
    }
    
    public function testQuickStringMethodIntegration(): void
    {
        // Test default parameters
        $passphrase = PassphraseGenerator::quickString();
        $this->assertIsString($passphrase);
        $this->assertNotEmpty($passphrase);
        $this->assertEquals(5, substr_count($passphrase, ' ')); // 6 words = 5 spaces
        
        // Test custom word count
        $shortPassphrase = PassphraseGenerator::quickString(4);
        $this->assertIsString($shortPassphrase);
        $this->assertEquals(3, substr_count($shortPassphrase, ' ')); // 4 words = 3 spaces
        
        // Test custom separator
        $hyphenated = PassphraseGenerator::quickString(5, '-');
        $this->assertStringContainsString('-', $hyphenated);
        $this->assertEquals(4, substr_count($hyphenated, '-')); // 5 words = 4 separators
        
        // Test no separator
        $concatenated = PassphraseGenerator::quickString(3, '');
        $this->assertIsString($concatenated);
        $this->assertStringNotContainsString(' ', $concatenated);
        $this->assertStringNotContainsString('-', $concatenated);
        
        // Test multi-character separator
        $multiSep = PassphraseGenerator::quickString(2, ' | ');
        $this->assertStringContainsString(' | ', $multiSep);
        $this->assertEquals(1, substr_count($multiSep, ' | '));
    }
    
    public function testQuickFromFileMethodIntegration(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test default word count
            $words = PassphraseGenerator::quickFromFile($filePath);
            $this->assertIsArray($words);
            $this->assertCount(6, $words); // Default is 6 words
            $this->assertContainsOnly('string', $words);
            
            // Verify words come from file
            foreach ($words as $word) {
                $this->assertStringStartsWith('fileword', $word);
            }
            
            // Test custom word count
            $shortWords = PassphraseGenerator::quickFromFile($filePath, 3);
            $this->assertCount(3, $shortWords);
            foreach ($shortWords as $word) {
                $this->assertStringStartsWith('fileword', $word);
            }
            
        } finally {
            unlink($filePath);
        }
    }
    
    public function testQuickStringFromFileMethodIntegration(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test default parameters
            $passphrase = PassphraseGenerator::quickStringFromFile($filePath);
            $this->assertIsString($passphrase);
            $this->assertNotEmpty($passphrase);
            $this->assertEquals(5, substr_count($passphrase, ' ')); // 6 words = 5 spaces
            
            // Verify words come from file
            $words = explode(' ', $passphrase);
            foreach ($words as $word) {
                $this->assertStringStartsWith('fileword', $word);
            }
            
            // Test custom word count and separator
            $customPassphrase = PassphraseGenerator::quickStringFromFile($filePath, 4, '_');
            $this->assertStringContainsString('_', $customPassphrase);
            $this->assertEquals(3, substr_count($customPassphrase, '_')); // 4 words = 3 separators
            
            $customWords = explode('_', $customPassphrase);
            foreach ($customWords as $word) {
                $this->assertStringStartsWith('fileword', $word);
            }
            
        } finally {
            unlink($filePath);
        }
    }
    
    public function testStaticMethodsRandomnessIntegration(): void
    {
        // Generate multiple passphrases to test randomness
        $passphrases = [];
        for ($i = 0; $i < 20; $i++) {
            $passphrases[] = PassphraseGenerator::quickString(4);
        }
        
        // All should be unique (extremely unlikely to be the same)
        $uniquePassphrases = array_unique($passphrases);
        $this->assertCount(20, $uniquePassphrases, 'All passphrases should be unique');
        
        // Test array method randomness
        $wordArrays = [];
        for ($i = 0; $i < 10; $i++) {
            $wordArrays[] = implode('|', PassphraseGenerator::quick(3));
        }
        
        $uniqueArrays = array_unique($wordArrays);
        $this->assertCount(10, $uniqueArrays, 'All word arrays should be unique');
    }
    
    public function testStaticMethodsErrorHandlingIntegration(): void
    {
        // Test invalid word count
        $this->expectException(\Vpap\DicePassphrase\Exception\InvalidWordCountException::class);
        PassphraseGenerator::quick(0);
    }
    
    public function testStaticMethodsStringErrorHandlingIntegration(): void
    {
        // Test invalid word count for string method
        $this->expectException(\Vpap\DicePassphrase\Exception\InvalidWordCountException::class);
        PassphraseGenerator::quickString(-1);
    }
    
    public function testStaticMethodsFileErrorHandlingIntegration(): void
    {
        // Test non-existent file
        $this->expectException(\Vpap\DicePassphrase\Exception\WordListNotFoundException::class);
        PassphraseGenerator::quickFromFile('/nonexistent/file.txt');
    }
    
    public function testStaticMethodsFileStringErrorHandlingIntegration(): void
    {
        // Test non-existent file for string method
        $this->expectException(\Vpap\DicePassphrase\Exception\WordListNotFoundException::class);
        PassphraseGenerator::quickStringFromFile('/nonexistent/file.txt');
    }
    
    public function testStaticMethodsPerformanceIntegration(): void
    {
        // Test that static methods perform reasonably well
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            PassphraseGenerator::quickString(5);
        }
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Should complete 100 generations in reasonable time (less than 1 second)
        $this->assertLessThan(1000, $duration, 'Static methods should be performant');
    }
    
    public function testStaticMethodsConsistencyIntegration(): void
    {
        // Test that static methods produce consistent output format
        for ($i = 0; $i < 5; $i++) {
            $passphrase = PassphraseGenerator::quickString(4);
            $this->assertIsString($passphrase);
            $this->assertEquals(3, substr_count($passphrase, ' '));
            
            $words = PassphraseGenerator::quick(3);
            $this->assertIsArray($words);
            $this->assertCount(3, $words);
            $this->assertContainsOnly('string', $words);
        }
    }
    
    public function testStaticMethodsWithVariousSeparatorsIntegration(): void
    {
        $separators = [' ', '-', '_', '', '|', ' | ', '::'];
        
        foreach ($separators as $separator) {
            $passphrase = PassphraseGenerator::quickString(3, $separator);
            $this->assertIsString($passphrase);
            
            if ($separator !== '') {
                $expectedCount = 2; // 3 words = 2 separators
                $actualCount = substr_count($passphrase, $separator);
                $this->assertEquals($expectedCount, $actualCount, "Separator '$separator' count mismatch");
            } else {
                // Empty separator should not contain spaces
                $this->assertStringNotContainsString(' ', $passphrase);
            }
        }
    }
    
    public function testStaticMethodsWithFileAndVariousParametersIntegration(): void
    {
        $filePath = $this->createValidWordListFile();
        
        try {
            // Test various word counts
            $wordCounts = [1, 3, 5, 8, 12];
            
            foreach ($wordCounts as $count) {
                $words = PassphraseGenerator::quickFromFile($filePath, $count);
                $this->assertCount($count, $words);
                
                $passphrase = PassphraseGenerator::quickStringFromFile($filePath, $count, '-');
                $expectedSeparators = $count - 1;
                $actualSeparators = substr_count($passphrase, '-');
                $this->assertEquals($expectedSeparators, $actualSeparators);
            }
            
        } finally {
            unlink($filePath);
        }
    }
    
    private function createValidWordListFile(): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'static_test_wordlist_');
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