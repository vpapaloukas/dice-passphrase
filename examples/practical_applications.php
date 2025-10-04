<?php

declare(strict_types=1);

/**
 * Practical Applications Examples
 * 
 * Demonstrates real-world use cases for the Dice Passphrase Generator library
 * in various application scenarios.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Vpap\DicePassphrase\PassphraseGenerator;
use Vpap\DicePassphrase\PassphraseGeneratorFactory;

echo "=== Practical Applications Examples ===\n\n";

// Example 1: User Registration System
echo "1. User Registration System:\n";

class UserRegistration
{
    private $generator;
    
    public function __construct()
    {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateTemporaryPassword(): string
    {
        // Generate a 4-word temporary password with hyphens
        return $this->generator->generateString(4, '-');
    }
    
    public function generateRecoveryCode(): string
    {
        // Generate a 6-word recovery code without separators
        return strtoupper($this->generator->generateString(6, ''));
    }
    
    public function generateApiKey(): string
    {
        // Generate a 8-word API key with underscores
        return $this->generator->generateString(8, '_');
    }
}

$userReg = new UserRegistration();
echo "Temporary password: " . $userReg->generateTemporaryPassword() . "\n";
echo "Recovery code: " . $userReg->generateRecoveryCode() . "\n";
echo "API key: " . $userReg->generateApiKey() . "\n\n";

// Example 2: Password Policy Compliance
echo "2. Password Policy Compliance:\n";

class PasswordPolicyGenerator
{
    private $generator;
    
    public function __construct()
    {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateForPolicy(string $policy): string
    {
        switch ($policy) {
            case 'basic':
                // Basic security: 4 words
                return $this->generator->generateString(4);
                
            case 'corporate':
                // Corporate security: 5 words with numbers
                $passphrase = $this->generator->generateString(5);
                return $passphrase . rand(10, 99);
                
            case 'high_security':
                // High security: 7 words with special characters
                $passphrase = $this->generator->generateString(7, '-');
                return $passphrase . '!' . rand(100, 999);
                
            case 'banking':
                // Banking security: 8 words, mixed case, numbers
                $words = $this->generator->generate(8);
                $formatted = [];
                foreach ($words as $word) {
                    $formatted[] = ucfirst($word);
                }
                return implode('', $formatted) . rand(1000, 9999);
                
            default:
                return $this->generator->generateString(6);
        }
    }
}

$policyGen = new PasswordPolicyGenerator();
echo "Basic policy: " . $policyGen->generateForPolicy('basic') . "\n";
echo "Corporate policy: " . $policyGen->generateForPolicy('corporate') . "\n";
echo "High security policy: " . $policyGen->generateForPolicy('high_security') . "\n";
echo "Banking policy: " . $policyGen->generateForPolicy('banking') . "\n\n";

// Example 3: Multi-tenant Application
echo "3. Multi-tenant Application:\n";

class TenantPasswordManager
{
    private $generators = [];
    
    public function getGeneratorForTenant(string $tenantId): PassphraseGenerator
    {
        if (!isset($this->generators[$tenantId])) {
            // Each tenant gets their own generator instance
            $this->generators[$tenantId] = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
        }
        return $this->generators[$tenantId];
    }
    
    public function generateTenantPassword(string $tenantId, int $wordCount = 5): string
    {
        $generator = $this->getGeneratorForTenant($tenantId);
        return $generator->generateString($wordCount);
    }
    
    public function generateTenantApiToken(string $tenantId): string
    {
        $generator = $this->getGeneratorForTenant($tenantId);
        $passphrase = $generator->generateString(6, '');
        return base64_encode($tenantId . ':' . $passphrase);
    }
}

$tenantManager = new TenantPasswordManager();
echo "Tenant A password: " . $tenantManager->generateTenantPassword('tenant_a') . "\n";
echo "Tenant B password: " . $tenantManager->generateTenantPassword('tenant_b') . "\n";
echo "Tenant A API token: " . $tenantManager->generateTenantApiToken('tenant_a') . "\n\n";

// Example 4: Backup and Recovery System
echo "4. Backup and Recovery System:\n";

class BackupRecoverySystem
{
    private $generator;
    
    public function __construct()
    {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateBackupEncryptionKey(): string
    {
        // 10-word key for maximum security
        return $this->generator->generateString(10, '-');
    }
    
    public function generateRecoveryPhrase(): array
    {
        // 12-word recovery phrase (common in crypto)
        return $this->generator->generate(12);
    }
    
    public function generateBackupFileName(): string
    {
        // 3-word filename with timestamp
        $words = $this->generator->generate(3);
        $timestamp = date('Y-m-d-H-i-s');
        return implode('-', $words) . '-' . $timestamp . '.backup';
    }
}

$backupSystem = new BackupRecoverySystem();
echo "Backup encryption key: " . $backupSystem->generateBackupEncryptionKey() . "\n";
echo "Recovery phrase: " . implode(' ', $backupSystem->generateRecoveryPhrase()) . "\n";
echo "Backup filename: " . $backupSystem->generateBackupFileName() . "\n\n";

// Example 5: Testing and Development
echo "5. Testing and Development:\n";

class TestDataGenerator
{
    private $generator;
    
    public function __construct()
    {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateTestUsernames(int $count): array
    {
        $usernames = [];
        for ($i = 0; $i < $count; $i++) {
            $words = $this->generator->generate(2);
            $usernames[] = strtolower($words[0] . '_' . $words[1] . rand(10, 99));
        }
        return $usernames;
    }
    
    public function generateTestPasswords(int $count): array
    {
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = $this->generator->generateString(4, '-');
        }
        return $passwords;
    }
    
    public function generateTestData(): array
    {
        return [
            'usernames' => $this->generateTestUsernames(5),
            'passwords' => $this->generateTestPasswords(5),
            'session_ids' => array_map(function() {
                return $this->generator->generateString(8, '');
            }, range(1, 5))
        ];
    }
}

$testGen = new TestDataGenerator();
$testData = $testGen->generateTestData();

echo "Test usernames:\n";
foreach ($testData['usernames'] as $username) {
    echo "  $username\n";
}

echo "Test passwords:\n";
foreach ($testData['passwords'] as $password) {
    echo "  $password\n";
}

echo "Test session IDs:\n";
foreach ($testData['session_ids'] as $sessionId) {
    echo "  $sessionId\n";
}
echo "\n";

// Example 6: Configuration Management
echo "6. Configuration Management:\n";

class ConfigurationManager
{
    private $generator;
    
    public function __construct()
    {
        $this->generator = PassphraseGeneratorFactory::createWithDefaultEnglishWordList();
    }
    
    public function generateSecrets(): array
    {
        return [
            'jwt_secret' => $this->generator->generateString(8, ''),
            'encryption_key' => $this->generator->generateString(12, '-'),
            'api_secret' => $this->generator->generateString(6, '_'),
            'webhook_secret' => $this->generator->generateString(5, ''),
            'session_secret' => $this->generator->generateString(7, '-')
        ];
    }
    
    public function generateEnvironmentConfig(): array
    {
        $secrets = $this->generateSecrets();
        
        return [
            'APP_KEY' => 'base64:' . base64_encode($secrets['encryption_key']),
            'JWT_SECRET' => $secrets['jwt_secret'],
            'API_SECRET' => $secrets['api_secret'],
            'WEBHOOK_SECRET' => $secrets['webhook_secret'],
            'SESSION_SECRET' => $secrets['session_secret']
        ];
    }
}

$configManager = new ConfigurationManager();
$envConfig = $configManager->generateEnvironmentConfig();

echo "Environment configuration:\n";
foreach ($envConfig as $key => $value) {
    echo "  $key=$value\n";
}
echo "\n";

echo "=== Practical Applications Complete ===\n";