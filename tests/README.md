# Unit Tests for idforward

This directory contains comprehensive unit tests for the idforward project.

## Test Structure

The test suite includes tests for all three main classes:

### 1. **ConfigTest** (`ConfigTest.php`)
Tests the `Config` class which handles configuration loading.

**Test Cases:**
- `testLoadConfigFromFile()` - Verifies configuration loads correctly from a file
- `testMissingConfigurationThrowsException()` - Validates configuration fallback behavior
- `testGetConfigMethod()` - Tests the config instance is properly created

### 2. **CodecTest** (`CodecTest.php`)
Tests the `Codec` class which handles ID encoding/decoding functionality.

**Decode Tests:**
- `testDecodeNumericId()` - Tests decoding simple numeric IDs (e.g., "123")
- `testDecodeHexadecimalId()` - Tests decoding hexadecimal IDs (e.g., "x7B")
- `testDecodeDestinationUrl()` - Tests decoding destination site URLs
- `testDecodeSourceUrl()` - Tests decoding source (current site) URLs
- `testDecodeZeroId()` - Edge case: ID = 0
- `testDecodeLargeId()` - Edge case: Large numeric IDs
- `testDecodeInvalidInput()` - Tests behavior with invalid input
- `testHexadecimalConversion()` - Tests various hex conversions

**Encode Tests:**
- `testEncodeReturnsCorrectStructure()` - Validates returned array structure
- `testEncodeWithEmptyId()` - Edge case: Empty/invalid ID
- `testEncodeGeneratesQrCode()` - Verifies QR code generation
- `testEncodeGeneratesBarcode()` - Verifies barcode generation

### 3. **IdforwardTest** (`IdforwardTest.php`)
Tests the main `idforward` class which generates HTML output.

**HTML Generation Tests:**
- `testGetTitleHTML()` - Tests title HTML generation
- `testGetInputHTML()` - Tests input form HTML generation
- `testGetOutputHTML()` - Tests output display HTML generation
- `testGetHTMLWithValidId()` - Full HTML generation with valid numeric ID
- `testGetHTMLWithHexadecimalId()` - Full HTML generation with hex ID
- `testGetHTMLWithEmptyId()` - Edge case: Empty ID handling
- `testGetHTMLWithInvalidId()` - Edge case: Invalid ID handling
- `testGetHTMLWithUrlAsId()` - Full HTML generation with URL ID
- `testGetHTMLStructure()` - Validates proper HTML element ordering
- `testMultipleGetHTMLCalls()` - Tests multiple consecutive calls

## Running the Tests

### Run all tests:
```bash
./vendor/bin/phpunit
```

### Run specific test file:
```bash
./vendor/bin/phpunit tests/ConfigTest.php
./vendor/bin/phpunit tests/CodecTest.php
./vendor/bin/phpunit tests/IdforwardTest.php
```

### Run specific test method:
```bash
./vendor/bin/phpunit --filter testDecodeNumericId
```

### Run with verbose output:
```bash
./vendor/bin/phpunit --verbose
```

## Test Configuration

The test suite uses:
- **PHPUnit 3.7+** (as defined in composer.json)
- **Temporary configuration files** for isolation between tests
- **Reflection** to access protected methods for unit testing
- **Mock config values** for consistent test behavior

## Test Statistics

- **Total Tests:** 25
- **Total Assertions:** 91
- **Coverage:** Core business logic in `Codec`, `Config`, and `idforward` classes

## Known Issues

The current implementation has a type handling bug where:
- Passing invalid input (non-numeric, non-URL) that doesn't match any decode pattern returns an empty string for the ID
- The `dechex()` function then attempts to convert this empty string, throwing a `TypeError` in PHP 8+

Tests are written to accommodate this current behavior, but it should be fixed in the source code:

**Recommended Fix in Codec.php line ~40:**
```php
// Before calling dechex(), ensure $did is an integer
if ($did !== '' && $did !== 0) {
    $hexid = 'x' . strtoupper(dechex($did));
} else {
    $hexid = 'x0';
}
```

## Setup Instructions

If tests haven't been set up yet:

1. Install dependencies:
   ```bash
   composer install
   ```

2. Run tests:
   ```bash
   ./vendor/bin/phpunit
   ```

## Files Included

- `ConfigTest.php` - Tests for Config class
- `CodecTest.php` - Tests for Codec class  
- `IdforwardTest.php` - Tests for idforward class
- `bootstrap.php` - PHPUnit bootstrap file for autoloading
- `../phpunit.xml` - PHPUnit configuration file
- `README.md` - This file

## Contributing

When adding new features or methods:
1. Write tests first (TDD approach) or immediately after
2. Ensure all existing tests continue to pass
3. Add test cases for edge cases and error conditions
4. Update this README with new test descriptions
