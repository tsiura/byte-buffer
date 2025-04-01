# Zeran ByteBuffer

A PHP library for binary data manipulation with support for multiple buffer implementations.

## Introduction

Zeran ByteBuffer is a powerful library for manipulating binary data in PHP. It provides a consistent interface for working with byte arrays and binary strings, supporting operations like reading and writing integers, floats, doubles, and strings in both little-endian and big-endian formats.

The library includes two primary implementations:
- `ArrayBuffer`: Stores data as an array of integers
- `StringBuffer`: Stores data as a string, which can be more memory-efficient

## Installation

You can install the package via composer:

```bash
composer require zeran/byte-buffer
```

## Usage

### Basic Usage

```php
use Zeran\ByteBuffer\ArrayBuffer;
use Zeran\ByteBuffer\StringBuffer;

// Choose an implementation
$buffer = new ArrayBuffer();
// or
$buffer = new StringBuffer();

// Write data
$buffer->writeInt16(1000);   // Write signed 16-bit integer
$buffer->writeUInt32(50000); // Write unsigned 32-bit integer
$buffer->writeFloat(123.45); // Write float
$buffer->writeUtf8String("Hello World"); // Write string

// Read data (remember to manage your position)
$buffer->setPosition(0);
$int16 = $buffer->readInt16();   // Read signed 16-bit integer
$uint32 = $buffer->readUInt32(); // Read unsigned 32-bit integer
$float = $buffer->readFloat();   // Read float
$string = $buffer->readUtf8String(11); // Read 11 bytes as UTF-8 string
```

### Creating Buffers

```php
// Create empty buffer
$buffer = new ArrayBuffer();

// Create from byte array
$buffer = ArrayBuffer::fromArray([0x01, 0x02, 0x03]);

// Create from string
$buffer = StringBuffer::fromString("Hello");

// Alternatively, write to buffer
$buffer = new StringBuffer();
$buffer->writeUtf8String("Hello");
```

### Integers (Signed and Unsigned)

```php
// Write integers in different sizes and endianness
$buffer->writeInt8(-50);          // 1-byte signed
$buffer->writeUInt8(100);         // 1-byte unsigned
$buffer->writeInt16(1000);        // 2-byte signed (little-endian)
$buffer->writeInt16BE(1000);      // 2-byte signed (big-endian)
$buffer->writeUInt16(1000);       // 2-byte unsigned (little-endian)
$buffer->writeUInt16BE(1000);     // 2-byte unsigned (big-endian)
$buffer->writeInt32(-123456);     // 4-byte signed (little-endian)
$buffer->writeUInt32BE(123456);   // 4-byte unsigned (big-endian)
$buffer->writeInt64(-9876543210); // 8-byte signed (little-endian)

// Generic write methods with custom size
$buffer->writeInt(-1000, 3);     // 3-byte signed (little-endian)
$buffer->writeUIntBE(100000, 3); // 3-byte unsigned (big-endian)

// Reading integers
$buffer->setPosition(0);
$int8 = $buffer->readInt8();
$uint8 = $buffer->readUInt8();
$int16 = $buffer->readInt16();
$int16be = $buffer->readInt16BE();
// ...and so on
```

### Floating-point numbers

```php
// Write float (4 bytes)
$buffer->writeFloat(100.5);
$buffer->writeFloatBE(100.5); // Big-endian

// Write double (8 bytes)
$buffer->writeDouble(123.456);
$buffer->writeDoubleBE(123.456); // Big-endian

// Reading floating-point numbers
$buffer->setPosition(0);
$float = $buffer->readFloat();
$floatBE = $buffer->readFloatBE();
$double = $buffer->readDouble();
$doubleBE = $buffer->readDoubleBE();
```

### Strings and Bytes

```php
// Write string
$buffer->writeUtf8String("Hello, world!");

// Write raw bytes
$buffer->writeBytes([0x0A, 0x0B, 0x0C, 0x01, 0x02, 0x03]);

// Reading strings and bytes
$buffer->setPosition(0);
$string = $buffer->readUtf8String(13); // Specify length or leave null to read all remaining
$bytes = $buffer->readBytes(6);        // Read 6 bytes as array
```

### Arrays/Lists

```php
// Write arrays of integers
$buffer->writeListUInt8([1, 2, 3, 4, 5]);
$buffer->writeListUInt16([1000, 2000, 3000]);
$buffer->writeListUInt32([100000, 200000, 300000]);

// Alternative syntax
$buffer->writeList8([1, 2, 3, 4, 5]);
$buffer->writeList16([1000, 2000, 3000]);

// Reading arrays/lists
$buffer->setPosition(0);
$uint8List = $buffer->readListUInt8(5);    // Read 5 uint8 values
$uint16List = $buffer->readListUInt16(3);  // Read 3 uint16 values
$uint32List = $buffer->readListUInt32(3);  // Read 3 uint32 values
```

### Buffer Management

```php
// Get current position
$position = $buffer->getPosition();

// Set position
$buffer->setPosition(4);

// Get total buffer size
$size = $buffer->size();

// Check remaining bytes
$remaining = $buffer->remains();

// Check if there are more bytes to read
$hasMore = $buffer->isMore();

// Trim the buffer
$buffer->ltrim(); // Remove bytes before current position
$buffer->rtrim(); // Remove bytes after current position

// Clear the buffer
$buffer->clear();

// Get raw buffer
$rawBuffer = $buffer->getBuffer(); // Returns string

// Convert buffer to byte array
$byteArray = $buffer->toBytesArray();

// String representation (hex format)
$hexString = $buffer->__toString(); // e.g., "01 02 03 04"

// Export as string
$encodedString = $buffer->encode();
```

## Type Range Limitations

The library automatically validates that values fit within their specified type ranges:

| Type | Size (bytes) | Range |
|------|--------------|-------|
| Int8 | 1 | -128 to 127 |
| UInt8 | 1 | 0 to 255 |
| Int16 | 2 | -32,768 to 32,767 |
| UInt16 | 2 | 0 to 65,535 |
| Int24 | 3 | -8,388,608 to 8,388,607 |
| UInt24 | 3 | 0 to 16,777,215 |
| Int32 | 4 | -2,147,483,648 to 2,147,483,647 |
| UInt32 | 4 | 0 to 4,294,967,295 |
| Int64 | 8 | -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807 |
| UInt64 | 8 | 0 to 18,446,744,073,709,551,615 |

## Endianness

Most methods come in two variants:
- Standard (e.g., `writeInt32`): Uses little-endian (least significant byte first)
- BE suffix (e.g., `writeInt32BE`): Uses big-endian (most significant byte first)

## Which Implementation to Use?

- `ArrayBuffer`: Better for cases where you need to manipulate individual bytes frequently
- `StringBuffer`: More memory-efficient for larger buffers when you primarily write and read sequentially

Both implementations share the same interface (`BufferInterface`), so your code can switch between them without changes.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.