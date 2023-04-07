<?php

namespace Tests;

use Tsiura\ByteBuffer\Buffer;
use PHPUnit\Framework\TestCase;

class WriteReadTest extends TestCase
{
    public static function getBuffer(): array
    {
        return [
            [
                new Buffer(),
            ],
        ];
    }

    /**
     * @dataProvider getBuffer
     */
    public function testReadWrite($buf): void
    {
        // FLOAT
        $buf->writeFloat(10.5);
        self::assertEquals('00 00 28 41', (string)$buf);
        $buf->clear();

        $buf->writeFloatBE(10.5);
        self::assertEquals('41 28 00 00', (string)$buf);
        $buf->clear();
// INT
        $buf->writeInt(200, 2);
        self::assertEquals('C8 00', (string)$buf);
        $buf->clear();

        $buf->writeInt(2000, 3);
        self::assertEquals('D0 07 00', (string)$buf);
        $buf->clear();

        $buf->writeInt(-1000, 2);
        self::assertEquals('18 FC', (string)$buf);
        $buf->clear();

        $buf->writeIntBE(-1000, 2);
        self::assertEquals('FC 18', (string)$buf);
        $buf->clear();

        $buf->writeIntBE(200, 2);
        self::assertEquals('00 C8', (string)$buf);
        $buf->clear();

        $buf->writeIntBE(2000, 3);
        self::assertEquals('00 07 D0', (string)$buf);
        $buf->clear();
// STRING
        $buf->writeUtf8String('abcde');
        self::assertEquals('61 62 63 64 65', (string)$buf);
        $buf->clear();
// DOUBLE
        $buf->writeDouble(123456789.22);
        self::assertEquals('AE 47 E1 54 34 6F 9D 41', (string)$buf);
        $buf->clear();

        $buf->writeDoubleBE(123456789.22);
        self::assertEquals('41 9D 6F 34 54 E1 47 AE', (string)$buf);
        $buf->clear();
// UINT
        $buf->writeUInt(1000, 2);
        self::assertEquals('E8 03', (string)$buf);
        $buf->clear();

        $buf->writeUIntBE(1000, 2);
        self::assertEquals('03 E8', (string)$buf);
        $buf->clear();

        $buf->writeInt8(-50);
        self::assertEquals('CE', (string)$buf);
        $buf->clear();

        $buf->writeUInt8(100);
        self::assertEquals('64', (string)$buf);
        $buf->clear();

        $buf->writeInt16(1000);
        self::assertEquals('E8 03', (string)$buf);
        $buf->clear();

        $buf->writeInt16BE(1000);
        self::assertEquals('03 E8', (string)$buf);
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
}
