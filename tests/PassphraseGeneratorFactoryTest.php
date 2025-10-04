<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests;

use PHPUnit\Framework\TestCase;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\WordList\FileWordList;

class PassphraseGeneratorFactoryTest extends TestCase
{
    public function testCreateWithDefaultEnglishWordListReturnsGenerator(): void
    {
        // Test that the factory creates a working generator with the default English word list
        $generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that the generator actually works
        $passphrase = $generator->generateString(3);
        $this->assertIsString($passphrase);
        $this->assertNotEmpty($passphrase);
        
        // Verify it has the expected format (2 spaces for 3 words)
        $spaceCount = substr_count($passphrase, ' ');
        $this->assertEquals(2, $spaceCount);
        
        // Test array generation
        $words = $generator->generate(2);
        $this->assertIsArray($words);
        $this->assertCount(2, $words);
        $this->assertContainsOnly('string', $words);
    }
    
    public function testCreateWithEmbeddedWordListReturnsGenerator(): void
    {
        $wordList = $this->createValidWordList();
        
        $generator = PassphraseGeneratorFactory::createWithEmbeddedWordList($wordList);
        
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that the generator works
        $passphrase = $generator->generate(3);
        $this->assertCount(3, $passphrase);
        $this->assertContainsOnly('string', $passphrase);
    }
    
    public function testCreateWithFileWordListReturnsGenerator(): void
    {
        $filePath = $this->createValidWordListFile();
        
        $generator = PassphraseGeneratorFactory::createWithFileWordList($filePath);
        
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that the generator works
        $passphrase = $generator->generate(2);
        $this->assertCount(2, $passphrase);
        $this->assertContainsOnly('string', $passphrase);
        
        // Clean up
        unlink($filePath);
    }
    
    public function testCreateWithCustomDependenciesReturnsGenerator(): void
    {
        $wordList = new EmbeddedWordList($this->createValidWordList());
        
        $generator = PassphraseGeneratorFactory::createWithCustomDependencies($wordList);
        
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
        
        // Test that the generator works
        $passphrase = $generator->generate(4);
        $this->assertCount(4, $passphrase);
        $this->assertContainsOnly('string', $passphrase);
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
                            $words[$diceRoll] = "word{$diceRoll}";
                        }
                    }
                }
            }
        }
        
        return $words;
    }
    
    private function createValidWordListFile(): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'wordlist_test_');
        $content = '';
        
        // Generate all possible dice roll combinations (11111 to 66666)
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            $content .= "{$diceRoll} word{$diceRoll}\n";
                        }
                    }
                }
            }
        }
        
        file_put_contents($filePath, $content);
        
        return $filePath;
    }
}