<?php
namespace peeto\idforward\Tests;

use peeto\idforward\Codec;
use PHPUnit\Framework\TestCase;

/**
 * CodecTest class
 *
 * Tests for the Codec class
 */
class CodecTest extends TestCase
{
    protected $codec;
    protected $testConfigFile;

    protected function setUp(): void
    {
        $this->testConfigFile = tempnam(sys_get_temp_dir(), 'test_codec_config_');
        file_put_contents($this->testConfigFile, '<?php
$config = [
    "SRC_SITE_IDURL" => "http://example.com/id/",
    "DEST_SITE_NAME" => "Example Site",
    "DEST_SITE_URL" => "http://example-dest.com/",
    "DEST_SITE_IDURL" => "http://example-dest.com/profile/"
];
');
        $this->codec = new Codec($this->testConfigFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    /**
     * Test that codec input methods require string arguments
     */
    public function testCodecInputMethodsRequireStrings()
    {
        $reflection = new \ReflectionClass($this->codec);

        foreach (['decode', 'encode'] as $methodName) {
            $parameter = $reflection->getMethod($methodName)->getParameters()[0];

            $this->assertSame('string', (string) $parameter->getType());
        }
    }

    /**
     * Test that translateSize accepts nullable strings
     */
    public function testTranslateSizeAcceptsNullableString()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('translateSize');
        $parameter = $method->getParameters()[0];

        $this->assertSame('?string', (string) $parameter->getType());
        $this->assertSame('?int', (string) $method->getReturnType());
        $this->assertTrue($parameter->getType()->allowsNull());
        $this->assertNull($method->invoke($this->codec, null));
        $this->assertSame(60, $method->invoke($this->codec, '60'));
        $this->assertSame(-8, $method->invoke($this->codec, '800%'));
        $this->assertNull($method->invoke($this->codec, '0'));
    }

    /**
     * Test decoding numeric ID
     */
    public function testDecodeNumericId()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, '123');
        
        $this->assertEquals(123, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/123', $result['url']);
        $this->assertEquals('x7B', $result['hexid']);
        $this->assertEquals('http://example.com/id/x7B', $result['hexurl']);
    }

    /**
     * Test decoding hexadecimal ID
     */
    public function testDecodeHexadecimalId()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, 'x7B');
        
        $this->assertEquals(123, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/123', $result['url']);
        $this->assertEquals('x7B', $result['hexid']);
    }

    /**
     * Test decoding destination URL
     */
    public function testDecodeDestinationUrl()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, 'http://example-dest.com/profile/456');
        
        $this->assertEquals(456, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/456', $result['url']);
    }

    /**
     * Test decoding source (current site) URL
     */
    public function testDecodeSourceUrl()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, 'http://example.com/id/789');
        
        $this->assertEquals(789, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/789', $result['url']);
    }

    /**
     * Test decoding with zero ID
     */
    public function testDecodeZeroId()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, '0');
        
        $this->assertEquals(0, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/0', $result['url']);
        $this->assertEquals('x0', $result['hexid']);
    }

    /**
     * Test decoding with large ID
     */
    public function testDecodeLargeId()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, '999999');
        
        $this->assertEquals(999999, $result['id']);
        $this->assertEquals('http://example-dest.com/profile/999999', $result['url']);
        $this->assertEquals('xF423F', $result['hexid']);
    }

    /**
     * Test decoding with invalid input (non-numeric, non-URL)
     */
    public function testDecodeInvalidInput()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('dechex()');

        $method->invoke($this->codec, 'invalid_string');
    }

    /**
     * Test hexadecimal conversion in decode
     */
    public function testHexadecimalConversion()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('decode');
        $method->setAccessible(true);
        
        // Test various conversions
        $testCases = [
            ['1', 'x1'],
            ['10', 'xA'],
            ['15', 'xF'],
            ['16', 'x10'],
            ['255', 'xFF'],
            ['256', 'x100'],
        ];
        
        foreach ($testCases as $case) {
            $result = $method->invoke($this->codec, $case[0]);
            $this->assertEquals($case[1], $result['hexid']);
        }
    }

    /**
     * Test encode method returns array with correct keys
     */
    public function testEncodeReturnsCorrectStructure()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);

        $this->assertSame('array', (string) $method->getReturnType());
        
        $result = $method->invoke($this->codec, '123');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('oid', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('hexid', $result);
        $this->assertArrayHasKey('hexurl', $result);
        $this->assertArrayHasKey('sitename', $result);
        $this->assertArrayHasKey('siteurl', $result);
        $this->assertArrayHasKey('srcurl', $result);
        $this->assertArrayHasKey('qrhtml', $result);
        $this->assertArrayHasKey('bchtml', $result);
    }

    /**
     * Test encode with empty ID (invalid ID)
     */
    public function testEncodeWithEmptyId()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('dechex()');

        $method->invoke($this->codec, 'invalid');
    }

    /**
     * Test encode with valid ID generates QR code
     */
    public function testEncodeGeneratesQrCode()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, '123');
        
        $this->assertNotEmpty($result['qrhtml']);
        $this->assertStringContainsString('<img', $result['qrhtml']);
        $this->assertStringContainsString('data:', $result['qrhtml']);
    }

    /**
     * Test encode with valid ID generates barcode
     */
    public function testEncodeGeneratesBarcode()
    {
        $reflection = new \ReflectionClass($this->codec);
        $method = $reflection->getMethod('encode');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->codec, '123');
        
        $this->assertNotEmpty($result['bchtml']);
        $this->assertStringContainsString('<img', $result['bchtml']);
        $this->assertStringContainsString('data:', $result['bchtml']);
    }
}
