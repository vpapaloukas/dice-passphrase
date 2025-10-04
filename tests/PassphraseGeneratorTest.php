<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\WordList\WordListInterface;
use Vpap\DicePassphrase\Random\SecureRandomGenerator;
use Vpap\DicePassphrase\Exception\InvalidWordCountException;

class PassphraseGeneratorTest extends TestCase
{
    private WordListInterface|MockObject $wordListMock;
    private SecureRandomGenerator|MockObject $randomGeneratorMock;
    private PassphraseGenerator $generator;

    protected function setUp(): void
    {
        $this->wordListMock = $this->createMock(WordListInterface::class);
        $this->randomGeneratorMock = $this->createMock(SecureRandomGenerator::class);
        $this->generator = new PassphraseGenerator($this->wordListMock, $this->randomGeneratorMock);
    }

    public function testConstructorAcceptsDependencies(): void
    {
        $generator = new PassphraseGenerator($this->wordListMock, $this->randomGeneratorMock);
        $this->assertInstanceOf(PassphraseGenerator::class, $generator);
    }

    public function testGenerateReturnsArrayOfWords(): void
    {
        // Setup mock expectations
        $this->randomGeneratorMock
            ->expects($this->exactly(3))
            ->method('generateDiceRoll')
            ->willReturnOnConsecutiveCalls('12345', '23456', '34567');

        $this->wordListMock
            ->expects($this->exactly(3))
            ->method('getWord')
            ->willReturnCallback(function ($diceRoll) {
                return match ($diceRoll) {
                    '12345' => 'word1',
                    '23456' => 'word2',
                    '34567' => 'word3',
                    default => 'unknown'
                };
            });

        $result = $this->generator->generate(3);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(['word1', 'word2', 'word3'], $result);
    }

    public function testGenerateWithDefaultWordCount(): void
    {
        // Setup mock expectations for default 6 words
        $this->randomGeneratorMock
            ->expects($this->exactly(6))
            ->method('generateDiceRoll')
            ->willReturn('12345');

        $this->wordListMock
            ->expects($this->exactly(6))
            ->method('getWord')
            ->with('12345')
            ->willReturn('testword');

        $result = $this->generator->generate();

        $this->assertCount(6, $result);
        $this->assertEquals(['testword', 'testword', 'testword', 'testword', 'testword', 'testword'], $result);
    }

    public function testGenerateWithSingleWord(): void
    {
        $this->randomGeneratorMock
            ->expects($this->once())
            ->method('generateDiceRoll')
            ->willReturn('11111');

        $this->wordListMock
            ->expects($this->once())
            ->method('getWord')
            ->with('11111')
            ->willReturn('single');

        $result = $this->generator->generate(1);

        $this->assertCount(1, $result);
        $this->assertEquals(['single'], $result);
    }

    public function testGenerateThrowsExceptionForZeroWordCount(): void
    {
        $this->expectException(InvalidWordCountException::class);
        $this->expectExceptionMessage('Word count must be at least 1, got: 0');

        $this->generator->generate(0);
    }

    public function testGenerateThrowsExceptionForNegativeWordCount(): void
    {
        $this->expectException(InvalidWordCountException::class);
        $this->expectExceptionMessage('Word count must be at least 1, got: -5');

        $this->generator->generate(-5);
    }

    public function testGenerateStringReturnsSpaceSeparatedWords(): void
    {
        // Setup mock expectations
        $this->randomGeneratorMock
            ->expects($this->exactly(4))
            ->method('generateDiceRoll')
            ->willReturnOnConsecutiveCalls('11111', '22222', '33333', '44444');

        $this->wordListMock
            ->expects($this->exactly(4))
            ->method('getWord')
            ->willReturnCallback(function ($diceRoll) {
                return match ($diceRoll) {
                    '11111' => 'hello',
                    '22222' => 'world',
                    '33333' => 'test',
                    '44444' => 'passphrase',
                    default => 'unknown'
                };
            });

        $result = $this->generator->generateString(4);

        $this->assertIsString($result);
        $this->assertEquals('hello world test passphrase', $result);
    }

    public function testGenerateStringWithDefaultWordCount(): void
    {
        // Setup mock expectations for default 6 words
        $this->randomGeneratorMock
            ->expects($this->exactly(6))
            ->method('generateDiceRoll')
            ->willReturn('12345');

        $this->wordListMock
            ->expects($this->exactly(6))
            ->method('getWord')
            ->with('12345')
            ->willReturn('word');

        $result = $this->generator->generateString();

        $this->assertEquals('word word word word word word', $result);
    }

    public function testGenerateStringWithSingleWord(): void
    {
        $this->randomGeneratorMock
            ->expects($this->once())
            ->method('generateDiceRoll')
            ->willReturn('66666');

        $this->wordListMock
            ->expects($this->once())
            ->method('getWord')
            ->with('66666')
            ->willReturn('alone');

        $result = $this->generator->generateString(1);

        $this->assertEquals('alone', $result);
    }

    public function testGenerateStringThrowsExceptionForInvalidWordCount(): void
    {
        $this->expectException(InvalidWordCountException::class);
        $this->expectExceptionMessage('Word count must be at least 1, got: -1');

        $this->generator->generateString(-1);
    }

    public function testGenerateUsesInjectedDependencies(): void
    {
        // Verify that the generator actually uses the injected dependencies
        $this->randomGeneratorMock
            ->expects($this->once())
            ->method('generateDiceRoll')
            ->willReturn('55555');

        $this->wordListMock
            ->expects($this->once())
            ->method('getWord')
            ->with('55555')
            ->willReturn('dependency');

        $result = $this->generator->generate(1);

        $this->assertEquals(['dependency'], $result);
    }

    public function testGenerateStringWithCustomSeparator(): void
    {
        // Setup mock expectations
        $this->randomGeneratorMock
            ->expects($this->exactly(3))
            ->method('generateDiceRoll')
            ->willReturnOnConsecutiveCalls('11111', '22222', '33333');

        $this->wordListMock
            ->expects($this->exactly(3))
            ->method('getWord')
            ->willReturnCallback(function ($diceRoll) {
                return match ($diceRoll) {
                    '11111' => 'hello',
                    '22222' => 'world',
                    '33333' => 'test',
                    default => 'unknown'
                };
            });

        $result = $this->generator->generateString(3, '-');

        $this->assertIsString($result);
        $this->assertEquals('hello-world-test', $result);
    }

    public function testGenerateStringWithEmptySeparator(): void
    {
        // Setup mock expectations
        $this->randomGeneratorMock
            ->expects($this->exactly(2))
            ->method('generateDiceRoll')
            ->willReturnOnConsecutiveCalls('11111', '22222');

        $this->wordListMock
            ->expects($this->exactly(2))
            ->method('getWord')
            ->willReturnCallback(function ($diceRoll) {
                return match ($diceRoll) {
                    '11111' => 'hello',
                    '22222' => 'world',
                    default => 'unknown'
                };
            });

        $result = $this->generator->generateString(2, '');

        $this->assertIsString($result);
        $this->assertEquals('helloworld', $result);
    }

    public function testGenerateStringWithMultiCharacterSeparator(): void
    {
        // Setup mock expectations
        $this->randomGeneratorMock
            ->expects($this->exactly(2))
            ->method('generateDiceRoll')
            ->willReturnOnConsecutiveCalls('11111', '22222');

        $this->wordListMock
            ->expects($this->exactly(2))
            ->method('getWord')
            ->willReturnCallback(function ($diceRoll) {
                return match ($diceRoll) {
                    '11111' => 'secure',
                    '22222' => 'passphrase',
                    default => 'unknown'
                };
            });

        $result = $this->generator->generateString(2, ' - ');

        $this->assertIsString($result);
        $this->assertEquals('secure - passphrase', $result);
    }
    
    public function testQuickMethodWorksWithDefaultWordList(): void
    {
        // Test that the quick method works with the default English word list
        $result = PassphraseGenerator::quick(3);
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnly('string', $result);
        
        // Verify each word is valid
        foreach ($result as $word) {
            $this->assertNotEmpty($word);
            $this->assertIsString($word);
        }
    }
    
    public function testQuickStringMethodWorksWithDefaultWordList(): void
    {
        // Test that the quickString method works with the default English word list
        $result = PassphraseGenerator::quickString(4);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        
        // Verify it has the expected number of spaces (word count - 1)
        $spaceCount = substr_count($result, ' ');
        $this->assertEquals(3, $spaceCount);
        
        // Verify words are separated properly
        $words = explode(' ', $result);
        $this->assertCount(4, $words);
        foreach ($words as $word) {
            $this->assertNotEmpty($word);
            $this->assertIsString($word);
        }
    }
    
    public function testQuickFromFileMethodWorksWithValidFile(): void
    {
        $filePath = $this->createValidWordListFile();
        
        $result = PassphraseGenerator::quickFromFile($filePath, 2);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnly('string', $result);
        
        // Clean up
        unlink($filePath);
    }
    
    public function testQuickStringFromFileMethodWorksWithValidFile(): void
    {
        $filePath = $this->createValidWordListFile();
        
        $result = PassphraseGenerator::quickStringFromFile($filePath, 3, '-');
        
        $this->assertIsString($result);
        $this->assertStringContainsString('-', $result);
        
        // Verify it has the expected number of separators (word count - 1)
        $separatorCount = substr_count($result, '-');
        $this->assertEquals(2, $separatorCount);
        
        // Clean up
        unlink($filePath);
    }
    
    public function testQuickFromFileMethodThrowsExceptionForInvalidWordCount(): void
    {
        $filePath = $this->createValidWordListFile();
        
        $this->expectException(InvalidWordCountException::class);
        
        try {
            PassphraseGenerator::quickFromFile($filePath, 0);
        } finally {
            // Clean up even if test fails
            unlink($filePath);
        }
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