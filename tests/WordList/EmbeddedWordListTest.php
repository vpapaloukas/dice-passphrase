<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Tests\WordList;

use PHPUnit\Framework\TestCase;
use Vpap\DicePassphrase\WordList\EmbeddedWordList;
use Vpap\DicePassphrase\Exception\InvalidWordListException;

class EmbeddedWordListTest extends TestCase
{
    public function testConstructorWithValidCustomWordList(): void
    {
        $customWordList = $this->createValidWordList();
        
        $embeddedWordList = new EmbeddedWordList($customWordList);
        
        $this->assertTrue($embeddedWordList->isValid());
        $this->assertEquals(7776, $embeddedWordList->getWordCount());
        $this->assertEquals('word1', $embeddedWordList->getWord('11111'));
        $this->assertEquals('word2', $embeddedWordList->getWord('11112'));
    }
    
    public function testConstructorWithDefaultWordListWorks(): void
    {
        // Test that the default English word list is properly loaded
        $wordList = new EmbeddedWordList();
        
        $this->assertTrue($wordList->isValid());
        $this->assertEquals(7776, $wordList->getWordCount());
        
        // Test that we can get words from the list
        $word = $wordList->getWord('11111');
        $this->assertIsString($word);
        $this->assertNotEmpty($word);
        
        // Test another dice roll
        $word2 = $wordList->getWord('66666');
        $this->assertIsString($word2);
        $this->assertNotEmpty($word2);
        
        // Words should be different
        $this->assertNotEquals($word, $word2);
    }
    
    public function testConstructorWithIncompleteWordListThrowsException(): void
    {
        $incompleteWordList = [
            '11111' => 'word1',
            '11112' => 'word2',
            '11113' => 'word3'
        ];
        
        $this->expectException(InvalidWordListException::class);
        $this->expectExceptionMessage('Embedded word list must contain exactly 7776 entries, found: 3');
        
        new EmbeddedWordList($incompleteWordList);
    }
    
    public function testConstructorWithInvalidDiceRollKeysThrowsException(): void
    {
        $invalidWordList = $this->createValidWordList();
        $invalidWordList['invalid'] = 'badword';
        unset($invalidWordList['66666']); // Keep count at 7776
        
        $this->expectException(InvalidWordListException::class);
        $this->expectExceptionMessage('Invalid dice roll key in embedded word list: invalid');
        
        new EmbeddedWordList($invalidWordList);
    }
    
    public function testConstructorWithMissingDiceRollThrowsException(): void
    {
        $incompleteWordList = $this->createValidWordList();
        unset($incompleteWordList['33333']); // Remove one entry
        
        $this->expectException(InvalidWordListException::class);
        $this->expectExceptionMessage('Embedded word list must contain exactly 7776 entries, found: 7775');
        
        new EmbeddedWordList($incompleteWordList);
    }
    
    public function testGetWordReturnsCorrectWord(): void
    {
        $customWordList = $this->createValidWordList();
        $embeddedWordList = new EmbeddedWordList($customWordList);
        
        $this->assertEquals('word1', $embeddedWordList->getWord('11111'));
        $this->assertEquals('word7776', $embeddedWordList->getWord('66666'));
    }
    
    public function testGetWordThrowsExceptionForMissingDiceRoll(): void
    {
        $customWordList = $this->createValidWordList();
        $embeddedWordList = new EmbeddedWordList($customWordList);
        
        $this->expectException(InvalidWordListException::class);
        $this->expectExceptionMessage('Word not found for dice roll: 99999');
        
        $embeddedWordList->getWord('99999');
    }
    
    public function testIsValidReturnsTrueForValidWordList(): void
    {
        $customWordList = $this->createValidWordList();
        $embeddedWordList = new EmbeddedWordList($customWordList);
        
        $this->assertTrue($embeddedWordList->isValid());
    }
    
    public function testGetWordCountReturnsCorrectCount(): void
    {
        $customWordList = $this->createValidWordList();
        $embeddedWordList = new EmbeddedWordList($customWordList);
        
        $this->assertEquals(7776, $embeddedWordList->getWordCount());
    }
    
    private function createValidWordList(): array
    {
        $words = [];
        $counter = 1;
        
        for ($d1 = 1; $d1 <= 6; $d1++) {
            for ($d2 = 1; $d2 <= 6; $d2++) {
                for ($d3 = 1; $d3 <= 6; $d3++) {
                    for ($d4 = 1; $d4 <= 6; $d4++) {
                        for ($d5 = 1; $d5 <= 6; $d5++) {
                            $diceRoll = "{$d1}{$d2}{$d3}{$d4}{$d5}";
                            $words[$diceRoll] = "word{$counter}";
                            $counter++;
                        }
                    }
                }
            }
        }
        
        return $words;
    }
}