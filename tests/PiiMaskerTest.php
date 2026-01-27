<?php

namespace Devcbh\LaravelAiProvider\Tests;

use Orchestra\Testbench\TestCase;
use Devcbh\LaravelAiProvider\PiiMasker\DefaultPiiMasker;
use Devcbh\LaravelAiProvider\AiServiceProvider;

class PiiMaskerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AiServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:3095867304958673049586730495867304958673049=');
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }

    /** @test */
    public function it_masks_emails()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Contact me at john.doe@example.com";
        $masked = $masker->mask($text);
        
        $this->assertStringNotContainsString("john.doe@example.com", $masked);
        $this->assertStringContainsString("[MASKED_EMAIL_", $masked);
    }

    /** @test */
    public function it_masks_phone_numbers()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Call 123-456-7890";
        $masked = $masker->mask($text);
        
        $this->assertStringNotContainsString("123-456-7890", $masked);
        $this->assertStringContainsString("[MASKED_PHONE_", $masked);
    }

    /** @test */
    public function it_masks_ssn()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "My SSN is 123-45-6789";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("My SSN is [MASKED_SSN_", $masked);
    }

    /** @test */
    public function it_masks_credit_cards()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Visa: 1234-5678-1234-5678";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Visa: [MASKED_CREDIT_CARD_", $masked);
    }

    /** @test */
    public function it_masks_ipv4()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Server IP is 192.168.1.1";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Server IP is [MASKED_IPV4_", $masked);
    }

    /** @test */
    public function it_masks_ipv6()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "The address is 2001:0db8:85a3:0000:0000:8a2e:0370:7334";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("The address is [MASKED_IPV6_", $masked);
    }

    /** @test */
    public function it_masks_mac_address()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "MAC: 01:23:45:67:89:ab";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("MAC: [MASKED_MAC_ADDRESS_", $masked);
    }

    /** @test */
    public function it_masks_iban()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Transfer to DE89370400440532013000";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Transfer to [MASKED_IBAN_", $masked);
    }

    /** @test */
    public function it_masks_passport_number()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Passport A12345678";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Passport [MASKED_PASSPORT_NUMBER_", $masked);
    }

    /** @test */
    public function it_masks_api_keys()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Keys: sk_1234567890abcdef1234567890abcdef12345678";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Keys: [MASKED_API_KEY_", $masked);
    }

    /** @test */
    public function it_masks_jwt()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoyNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Token: [MASKED_JWT_", $masked);
    }

    /** @test */
    public function it_masks_aws_keys()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Access: AKIAIOSFODNN7EXAMPLE";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Access: [MASKED_AWS_ACCESS_KEY_", $masked);
    }

    /** @test */
    public function it_masks_private_keys()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "Here is my key:\n-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEA75...\n-----END RSA PRIVATE KEY-----\nKeep it safe.";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Here is my key:\n[MASKED_PRIVATE_KEY_", $masked);
    }

    /** @test */
    public function it_uses_custom_replacements_as_placeholder_prefix()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $piiConfig = $config['pii_masking'];
        $piiConfig['replacements'] = ['email' => 'REDACTED_EMAIL'];
        
        $masker = new DefaultPiiMasker($piiConfig);
        $text = "Email: test@test.com";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("Email: [MASKED_EMAIL_", $masked);
    }

    /** @test */
    public function it_supports_custom_patterns_via_constructor()
    {
        $masker = new DefaultPiiMasker([
            'patterns' => [
                'secret' => '/SECRET-\d+/'
            ]
        ]);
        $text = "The code is SECRET-12345";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("The code is [MASKED_SECRET_", $masked);
    }

    /** @test */
    public function it_supports_custom_patterns_via_extend()
    {
        $masker = new DefaultPiiMasker();
        $masker->extend([
            'custom' => '/CUSTOM-\d+/'
        ]);
        $text = "The code is CUSTOM-12345";
        $masked = $masker->mask($text);
        
        $this->assertStringContainsString("The code is [MASKED_CUSTOM_", $masked);
    }

    /** @test */
    public function it_can_unmask_data()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "My email is john.doe@example.com and phone is 123-456-7890";
        $masked = $masker->mask($text);
        
        $this->assertStringNotContainsString("john.doe@example.com", $masked);
        $this->assertStringNotContainsString("123-456-7890", $masked);
        
        $unmasked = $masker->unmask("Response with " . $masked);
        
        $this->assertStringContainsString("john.doe@example.com", $unmasked);
        $this->assertStringContainsString("123-456-7890", $unmasked);
    }

    /** @test */
    public function it_can_scrub_data_irreversibly()
    {
        $config = require __DIR__ . '/../config/ai.php';
        $masker = new DefaultPiiMasker($config['pii_masking']);
        $text = "My email is john.doe@example.com";
        $scrubbed = $masker->scrub($text);
        
        $this->assertStringNotContainsString("john.doe@example.com", $scrubbed);
        $this->assertStringContainsString("[REDACTED EMAIL]", $scrubbed);
        
        // Ensure unmask doesn't bring it back
        $unmasked = $masker->unmask($scrubbed);
        $this->assertStringNotContainsString("john.doe@example.com", $unmasked);
    }
}
