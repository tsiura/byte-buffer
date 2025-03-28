<?php

namespace Tests;

use Zeran\ByteBuffer\ArrayBuffer;
use PHPUnit\Framework\TestCase;
use Zeran\ByteBuffer\BufferInterface;
use Zeran\ByteBuffer\StringBuffer;

class WriteReadTest extends TestCase
{
    public static function getBuffer(): array
    {
        return [
            [
                new ArrayBuffer(),
            ],
            [
                new StringBuffer(),
            ],
        ];
    }

    /**
     * @dataProvider getBuffer
     */
    public function testReadWrite(BufferInterface $buf): void
    {
        // Double
        $buf->writeDouble(123.456);
        self::assertEquals('77 BE 9F 1A 2F DD 5E 40', $buf->__toString());
        self::assertEquals(123.456, $buf->readDouble());
        $buf->clear();

        $buf->writeDoubleBE(123.456);
        self::assertEquals('40 5E DD 2F 1A 9F BE 77', $buf->__toString());
        self::assertEquals(123.456, $buf->readDoubleBE());
        $buf->clear();

        $buf->writeDouble(123456789.22);
        self::assertEquals('AE 47 E1 54 34 6F 9D 41', $buf->__toString());
        $buf->clear();

        $buf->writeDoubleBE(123456789.22);
        self::assertEquals('41 9D 6F 34 54 E1 47 AE', $buf->__toString());
        $buf->clear();

        // FLOAT
        $buf->writeFloat(10.5);
        self::assertEquals('00 00 28 41', $buf->__toString());
        $buf->clear();

        $buf->writeFloatBE(10.5);
        self::assertEquals('41 28 00 00', $buf->__toString());
        $buf->clear();
        // INT
        $buf->writeInt(200, 2);
        self::assertEquals('C8 00', $buf->__toString());
        $buf->clear();

        $buf->writeInt(2000, 3);
        self::assertEquals('D0 07 00', $buf->__toString());
        $buf->clear();

        $buf->writeInt(-1000, 2);
        self::assertEquals('18 FC', $buf->__toString());
        $buf->clear();

        $buf->writeIntBE(-1000, 2);
        self::assertEquals('FC 18', $buf->__toString());
        $buf->clear();

        $buf->writeIntBE(200, 2);
        self::assertEquals('00 C8', $buf->__toString());
        $buf->clear();

        $buf->writeIntBE(2000, 3);
        self::assertEquals('00 07 D0', $buf->__toString());
        $buf->clear();
        // STRING
        $buf->writeUtf8String('abcde');
        self::assertEquals('61 62 63 64 65', $buf->__toString());
        $buf->clear();
        // UINT
        $buf->writeUInt(1000, 2);
        self::assertEquals('E8 03', $buf->__toString());
        $buf->clear();

        $buf->writeUIntBE(1000, 2);
        self::assertEquals('03 E8', $buf->__toString());
        $buf->clear();

        $buf->writeInt8(-50);
        self::assertEquals('CE', $buf->__toString());
        $buf->clear();

        $buf->writeUInt8(100);
        self::assertEquals('64', $buf->__toString());
        $buf->clear();

        $buf->writeInt16(1000);
        self::assertEquals('E8 03', $buf->__toString());
        $buf->clear();

        $buf->writeInt16BE(1000);
        self::assertEquals('03 E8', $buf->__toString());
        $buf->clear();

        $buf->writeUtf8String('abcde');
        self::assertEquals('abcde', $buf->readUtf8String(5));
        self::assertEquals(5, $buf->getPosition());
        $buf->clear();

        $buf->writeUInt(10000, 2);
        self::assertEquals(10000, $buf->readUInt(2));
        self::assertEquals(2, $buf->getPosition());
        $buf->clear();

        $buf->writeInt(-1000, 2);
        self::assertEquals(-1000, $buf->readInt(2));
        self::assertEquals(2, $buf->getPosition());
        $buf->clear();

        $buf->writeInt(-100000, 3);
        self::assertEquals(-100000, $buf->readInt(3));
        self::assertEquals(3, $buf->getPosition());
        $buf->clear();

        $buf->writeIntBE(-1000, 2);
        self::assertEquals(-1000, $buf->readIntBE(2));
        self::assertEquals(2, $buf->getPosition());
        $buf->clear();

        $buf->writeIntBE(-100000, 3);
        self::assertEquals(-100000, $buf->readIntBE(3));
        self::assertEquals(3, $buf->getPosition());
        $buf->clear();

        $buf->writeIntBE(100000, 3);
        self::assertEquals(100000, $buf->readIntBE(3));
        self::assertEquals(3, $buf->getPosition());
        $buf->clear();

        $buf->writeUIntBE(100000, 3);
        self::assertEquals(100000, $buf->readUIntBE(3));
        self::assertEquals(3, $buf->getPosition());
        $buf->clear();

        $buf->writeFloat(100.5);
        self::assertEquals(100.5, $buf->readFloat());
        self::assertEquals(4, $buf->getPosition());
        $buf->clear();

        $buf->writeFloat(-100.5);
        self::assertEquals(-100.5, $buf->readFloat());
        self::assertEquals(4, $buf->getPosition());
        $buf->clear();

        $buf->writeFloatBE(100.5);
        self::assertEquals(100.5, $buf->readFloatBE());
        self::assertEquals(4, $buf->getPosition());
        $buf->clear();

        $buf->writeFloatBE(-100.5);
        self::assertEquals(-100.5, $buf->readFloatBE());
        self::assertEquals(4, $buf->getPosition());
        $buf->clear();

        $buf->writeFloatBE(-100.5)->writeUInt(5000, 3);
        self::assertEquals(7, $buf->size());
        self::assertEquals(0, $buf->getPosition());
        $buf->clear();

        $buf->writeInt16(100);
        $buf->writeInt16BE(100);
        self::assertEquals(4, $buf->size());
        $buf->readInt16();
        self::assertEquals(2, $buf->getPosition());
        $buf->ltrim();
        self::assertEquals(2, $buf->size());
        self::assertEquals(0, $buf->getPosition());
        self::assertEquals(100, $buf->readInt16BE());
        $buf->clear();

        $arr = [0x0A, 0x0B, 0x0C, 0x01, 0x02, 0x03];
        $buf->writeBytes($arr);
        $enc = $buf->getBuffer();
        self::assertEquals(6, $buf->size());
        self::assertEquals(implode(array_map('chr', $arr)), $enc);
        self::assertEquals($arr, $buf->readBytes(count($arr)));
        $buf->clear();

        $enc = 'abcde';
        $buf->writeUtf8String($enc);
        self::assertEquals(array_map('ord', str_split($enc)), $buf->readBytes());
    }

    /**
     * @dataProvider getBuffer
     */
    public function testRemains(BufferInterface $buf): void
    {
        self::assertEquals(0, $buf->remains());

        $buf->writeUInt16(1000);
        self::assertEquals(2, $buf->remains());

        $buf->writeUInt32(100000);
        self::assertEquals(6, $buf->remains());

        $buf->writeUInt64(10000000);
        self::assertEquals(14, $buf->remains());
    }
}
