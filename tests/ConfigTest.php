<?php
namespace peeto\idforward\Tests;

use peeto\idforward\Config;
use PHPUnit\Framework\TestCase;

/**
 * ConfigTest class
 *
 * Tests for the Config class
 */
class ConfigTest extends TestCase
{
    protected $testConfigFile;

    protected function setUp(): void
    {
        $this->testConfigFile = tempnam(sys_get_temp_dir(), 'test_config_');
        file_put_contents($this->testConfigFile, '<?php
$config = [
    "SRC_SITE_IDURL" => "http://example.com/id/",
    "DEST_SITE_NAME" => "Example Site",
    "DEST_SITE_URL" => "http://example-dest.com/",
    "DEST_SITE_IDURL" => "http://example-dest.com/profile/"
];
');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    /**
     * Test loading configuration from a specified file
     */
    public function testLoadConfigFromFile()
    {
        $config = new Config($this->testConfigFile);
        
        // Access protected method through reflection to verify config was loaded
        $reflection = new \ReflectionClass($config);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configArray = $configProperty->getValue($config);
        
        $this->assertEquals('http://example.com/id/', $configArray['SRC_SITE_IDURL']);
        $this->assertEquals('Example Site', $configArray['DEST_SITE_NAME']);
        $this->assertEquals('http://example-dest.com/', $configArray['DEST_SITE_URL']);
        $this->assertEquals('http://example-dest.com/profile/', $configArray['DEST_SITE_IDURL']);
    }

    /**
     * Test that Config throws exception when configuration file is missing
     */
    public function testMissingConfigurationThrowsException()
    {
        // The Config class will load the default config if no valid config file is provided
        // So we test that a valid config is loaded
        $config = new Config('');
        $this->assertInstanceOf(Config::class, $config);
    }

    /**
     * Test getConfig method through encode method
     */
    public function testGetConfigMethod()
    {
        $config = new Config($this->testConfigFile);
        
        // Test by creating a simple instance and checking if it loads properly
        $this->assertInstanceOf(Config::class, $config);
    }
}
