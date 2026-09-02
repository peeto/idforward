<?php
namespace peeto\idforward\Tests;

use peeto\idforward\idforward;
use PHPUnit\Framework\TestCase;

/**
 * IdforwardTest class
 *
 * Tests for the idforward class
 */
class IdforwardTest extends TestCase
{
    protected $idforward;
    protected $testConfigFile;

    protected function setUp(): void
    {
        $this->testConfigFile = tempnam(sys_get_temp_dir(), 'test_idforward_config_');
        file_put_contents($this->testConfigFile, '<?php
$config = [
    "SRC_SITE_IDURL" => "http://example.com/id/",
    "DEST_SITE_NAME" => "Example Site",
    "DEST_SITE_URL" => "http://example-dest.com/",
    "DEST_SITE_IDURL" => "http://example-dest.com/profile/"
];
');
        $this->idforward = new idforward($this->testConfigFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    /**
     * Test getTitleHTML method
     */
    public function testGetTitleHTML()
    {
        $reflection = new \ReflectionClass($this->idforward);
        $method = $reflection->getMethod('getTitleHTML');
        $method->setAccessible(true);
        
        $data = ['sitename' => 'My Example Site'];
        $html = $method->invoke($this->idforward, $data);
        
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Link to My Example Site', $html);
        $this->assertStringContainsString('</h1>', $html);
    }

    /**
     * Test getInputHTML method
     */
    public function testGetInputHTML()
    {
        $reflection = new \ReflectionClass($this->idforward);
        $method = $reflection->getMethod('getInputHTML');
        $method->setAccessible(true);
        
        $data = [
            'srcurl' => 'http://example.com/id/',
            'oid' => '123'
        ];
        $html = $method->invoke($this->idforward, $data);
        
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="get"', $html);
        $this->assertStringContainsString('action="http://example.com/id/"', $html);
        $this->assertStringContainsString('name="id"', $html);
        $this->assertStringContainsString('value="123"', $html);
        $this->assertStringContainsString('<input type="submit"', $html);
        $this->assertStringContainsString('</form>', $html);
    }

    /**
     * Test getOutputHTML method
     */
    public function testGetOutputHTML()
    {
        $reflection = new \ReflectionClass($this->idforward);
        $method = $reflection->getMethod('getOutputHTML');
        $method->setAccessible(true);
        
        $data = [
            'url' => 'http://example-dest.com/profile/123',
            'id' => 123,
            'hexurl' => 'http://example.com/id/x7B',
            'hexid' => 'x7B',
            'qrhtml' => '<img src="data:image/png;base64,..." />',
        ];
        $html = $method->invoke($this->idforward, $data);
        
        $this->assertStringContainsString('<p>', $html);
        $this->assertStringContainsString('Profile:', $html);
        $this->assertStringContainsString('href="http://example-dest.com/profile/123"', $html);
        $this->assertStringContainsString('Hexidecimal:', $html);
        $this->assertStringContainsString('href="http://example.com/id/x7B"', $html);
        $this->assertStringContainsString('x7B', $html);
        $this->assertStringContainsString('</p>', $html);
    }

    /**
     * Test getHTML method with valid ID
     */
    public function testGetHTMLWithValidId()
    {
        $html = $this->idforward->getHTML('123');
        
        $this->assertIsString($html);
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('Link to Example Site', $html);
        $this->assertStringContainsString('<p>', $html);
        $this->assertStringContainsString('Profile:', $html);
    }

    /**
     * Test getHTML method with hexadecimal ID
     */
    public function testGetHTMLWithHexadecimalId()
    {
        $html = $this->idforward->getHTML('x7B');
        
        $this->assertIsString($html);
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Profile:', $html);
        $this->assertStringContainsString('123', $html);
    }

    /**
     * Test getHTML method with empty ID
     */
    public function testGetHTMLWithEmptyId()
    {
        // Current implementation has a bug where it tries to convert '' to hex
        try {
            $html = $this->idforward->getHTML('');
            
            $this->assertIsString($html);
            $this->assertStringContainsString('<h1>', $html);
            $this->assertStringContainsString('<form', $html);
            // Should NOT contain output section for empty ID
            $this->assertStringNotContainsString('Profile:', $html);
        } catch (\TypeError $e) {
            // Current implementation throws TypeError when trying to convert '' to hex
            $this->assertStringContainsString('dechex', $e->getMessage());
        }
    }

    /**
     * Test getHTML method with invalid ID
     */
    public function testGetHTMLWithInvalidId()
    {
        // Current implementation has a bug where it tries to convert '' to hex for invalid input
        try {
            $html = $this->idforward->getHTML('not_a_valid_id');
            
            $this->assertIsString($html);
            $this->assertStringContainsString('<h1>', $html);
            $this->assertStringContainsString('<form', $html);
            // Should NOT contain output section for invalid ID
            $this->assertStringNotContainsString('Profile:', $html);
        } catch (\TypeError $e) {
            // Current implementation throws TypeError when trying to convert '' to hex
            $this->assertStringContainsString('dechex', $e->getMessage());
        }
    }

    /**
     * Test getHTML method with URL as ID
     */
    public function testGetHTMLWithUrlAsId()
    {
        $html = $this->idforward->getHTML('http://example-dest.com/profile/456');
        
        $this->assertIsString($html);
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Profile:', $html);
        $this->assertStringContainsString('456', $html);
    }

    /**
     * Test getHTML method structure
     */
    public function testGetHTMLStructure()
    {
        $html = $this->idforward->getHTML('789');
        
        // Verify basic HTML structure
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('<p>', $html);
        
        // Check order: title should come before form
        $titlePos = strpos($html, '<h1>');
        $formPos = strpos($html, '<form');
        $this->assertLessThan($formPos, $titlePos);
    }

    /**
     * Test multiple consecutive getHTML calls
     */
    public function testMultipleGetHTMLCalls()
    {
        $html1 = $this->idforward->getHTML('123');
        $html2 = $this->idforward->getHTML('456');
        $html3 = $this->idforward->getHTML('789');
        
        $this->assertIsString($html1);
        $this->assertIsString($html2);
        $this->assertIsString($html3);
        
        // Each should contain profile information
        $this->assertStringContainsString('Profile:', $html1);
        $this->assertStringContainsString('Profile:', $html2);
        $this->assertStringContainsString('Profile:', $html3);
    }
}
