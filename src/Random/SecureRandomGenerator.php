<?php

declare(strict_types=1);

namespace Vpap\DicePassphrase\Random;

/**
 * Generates cryptographically secure random numbers for dice roll simulation.
 *
 * Uses PHP's random_int() function to generate secure random numbers
 * equivalent to rolling five physical dice.
 */
class SecureRandomGenerator
{
    /**
     * Generate a five-digit dice roll string.
     *
     * Each digit represents a single die roll (1-6), equivalent to rolling
     * five physical dice. Uses cryptographically secure random number generation.
     *
     * @return string Five-digit string with each digit between 1-6 (e.g., "34521")
     * @throws \Exception if secure random number generation fails
     */
    public function generateDiceRoll(): string
    {
        $diceRoll = '';

        for ($i = 0; $i < 5; $i++) {
            $diceRoll .= random_int(1, 6);
        }

        return $diceRoll;
    }
}
