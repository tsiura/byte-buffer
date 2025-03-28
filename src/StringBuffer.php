<?php

declare(strict_types=1);

namespace Zeran\ByteBuffer;

use UnexpectedValueException;
use InvalidArgumentException;

/**
 * @api
 */
class StringBuffer implements BufferInterface
{
    private int $position = 0;
    private string $buffer;

    public function __construct(string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    #[\Override]
    public static function fromArray(array $buffer): self
    {
        $byteValues = array_map('intval', $buffer);
        $packedString = pack('C*', ...$byteValues);
        return new self($packedString);
    }

    #[\Override]
    public function writeInt(int $value, int $size): self
    {
        $this->checkSigned($value, $size);
        $packed = '';
        for ($i = 0; $i < $size; ++$i) {
            $packed .= chr($value & 0xff);
            $value >>= 8;
        }
        $this->buffer .= $packed;
        return $this;
    }

    #[\Override]
    public function writeIntBE(int $value, int $size): self
    {
        $this->checkSigned($value, $size);

        $packed = '';
        for ($i = $size - 1; $i >= 0; --$i) {
            $packed .= chr(($value >> ($i * 8)) & 0xff);
        }
        $this->buffer .= $packed;
        return $this;
    }

    #[\Override]
    public function writeUInt(int $value, int $size): self
    {
        $this->checkUnsigned($value, $size);
        $packed = '';
        for ($i = 0; $i < $size; ++$i) {
            $packed .= chr($value & 0xff); // Append byte character
            $value >>= 8;
        }
        $this->buffer .= $packed;
        return $this;
    }

    #[\Override]
    public function writeUIntBE(int $value, int $size): self
    {
        $this->checkUnsigned($value, $size);
        $packed = '';
        for ($i = $size - 1; $i >= 0; --$i) {
            $packed .= chr(($value >> ($i * 8)) & 0xff);
        }
        $this->buffer .= $packed;
        return $this;
    }

    #[\Override]
    public function writeFloat(float $value): self
    {
        $this->buffer .= pack('g', $value);
        return $this;
    }

    #[\Override]
    public function writeFloatBE(float $value): self
    {
        $this->buffer .= pack('G', $value);
        return $this;
    }

    #[\Override]
    public function writeDouble(float $value): self
    {
        $this->buffer .= pack('e', $value);
        return $this;
    }

    #[\Override]
    public function writeDoubleBE(float $value): self
    {
        $this->buffer .= pack('E', $value);
        return $this;
    }

    #[\Override]
    public function readDouble(): float
    {
        $this->checkOffset(8);
        $slice = substr($this->buffer, $this->position, 8);
        $result = unpack('e', $slice)[1];
        $this->position += 8;
        return $result;
    }

    #[\Override]
    public function readDoubleBE(): float
    {
        $this->checkOffset(8);
        $slice = substr($this->buffer, $this->position, 8);
        $result = unpack('E', $slice)[1];
        $this->position += 8;
        return $result;
    }

    #[\Override]
    public function writeUtf8String(string $value): self
    {
        $this->buffer .= $value;
        return $this;
    }

    #[\Override]
    public function writeInt8(int $value): self
    {
        $this->checkSigned($value, 1);
        $this->buffer .= pack('c', $value);
        return $this;
    }

    #[\Override]
    public function writeUInt8(int $value): self
    {
        $this->checkUnsigned($value, 1);
        $this->buffer .= pack('C', $value);
        return $this;
    }

    #[\Override]
    public function writeInt16(int $value): self
    {
        return $this->writeInt($value, 2);
    }

    #[\Override]
    public function writeInt16BE(int $value): self
    {
        return $this->writeIntBE($value, 2);
    }

    #[\Override]
    public function writeInt24(int $value): self
    {
        return $this->writeInt($value, 3);
    }

    #[\Override]
    public function writeInt24BE(int $value): self
    {
        return $this->writeIntBE($value, 3);
    }

    #[\Override]
    public function writeInt32(int $value): self
    {
        return $this->writeInt($value, 4);
    }

    #[\Override]
    public function writeInt32BE(int $value): self
    {
        return $this->writeIntBE($value, 4);
    }

    #[\Override]
    public function writeInt64(int $value): self
    {
        return $this->writeInt($value, 8);
    }

    #[\Override]
    public function writeInt64BE(int $value): self
    {
        return $this->writeIntBE($value, 8);
    }

    #[\Override]
    public function writeUInt16(int $value): self
    {
        return $this->writeUInt($value, 2);
    }

    #[\Override]
    public function writeUInt16BE(int $value): self
    {
        return $this->writeUIntBE($value, 2);
    }

    #[\Override]
    public function writeUInt24(int $value): self
    {
        return $this->writeUInt($value, 3);
    }

    #[\Override]
    public function writeUInt24BE(int $value): self
    {
        return $this->writeUIntBE($value, 3);
    }

    #[\Override]
    public function writeUInt32(int $value): self
    {
        return $this->writeUInt($value, 4);
    }

    #[\Override]
    public function writeUInt32BE(int $value): self
    {
        return $this->writeUIntBE($value, 4);
    }

    #[\Override]
    public function writeUInt64(int $value): self
    {
        return $this->writeUInt($value, 8);
    }

    #[\Override]
    public function writeUInt64BE(int $value): self
    {
        return $this->writeUIntBE($value, 8);
    }

    #[\Override]
    public function readUtf8String(?int $length = null): string
    {
        $available = $this->size() - $this->position;
        $readLength = $length ?? $available; // Read to end if length is null

        if ($readLength < 0) {
            throw new InvalidArgumentException('Length cannot be negative.');
        }
        if ($length !== null) { // Only check offset if a specific length is requested
            $this->checkOffset($readLength);
        } elseif ($readLength === 0 && $available > 0) {
            $readLength = $available;
        }

        if ($readLength === 0) {
            return ''; // Nothing to read
        }

        $slice = substr($this->buffer, $this->position, $readLength);
        $this->position += $readLength;

        return $slice;
    }

    #[\Override]
    public function readInt(int $size): int
    {
        $this->checkOffset($size);

        $val = 0;
        $mul = 1;
        for ($i = 0; $i < $size; ++$i) {
            $byteValue = ord($this->buffer[$this->position + $i]);
            $val += $byteValue * $mul;
            $mul *= 0x100; // 256
        }
        $this->position += $size;

        $signBitMultiplier = pow(2, ($size * 8) - 1); // e.g., 128 for int8, 32768 for int16
        $maxValue = pow(2, $size * 8);

        if ($val >= $signBitMultiplier) {
            return $val - $maxValue;
        } else {
            return $val;
        }
    }

    #[\Override]
    public function readIntBE(int $size): int
    {
        $this->checkOffset($size);

        $val = 0;
        for ($i = 0; $i < $size; ++$i) {
            $val <<= 8; // Shift left to make space for the next byte
            $byteValue = ord($this->buffer[$this->position + $i]);
            $val |= $byteValue;
        }
        $this->position += $size;

        $signBitMultiplier = pow(2, ($size * 8) - 1);
        $maxValue = pow(2, $size * 8);

        if ($val >= $signBitMultiplier) {
            return $val - $maxValue;
        } else {
            return $val;
        }
    }

    #[\Override]
    public function readUInt(int $size): int
    {
        $this->checkOffset($size);

        $val = 0;
        $mul = 1;
        for ($i = 0; $i < $size; ++$i) {
            $byteValue = ord($this->buffer[$this->position + $i]);
            $val += $byteValue * $mul;
            $mul *= 0x100; // 256
        }
        $this->position += $size;

        return $val;
    }

    #[\Override]
    public function readUIntBE(int $size): int
    {
        $this->checkOffset($size);

        $val = 0;
        for ($i = 0; $i < $size; ++$i) {
            $val <<= 8; // Shift left
            $byteValue = ord($this->buffer[$this->position + $i]);
            $val |= $byteValue;
        }
        $this->position += $size;

        return $val;
    }

    #[\Override]
    public function readInt8(): int
    {
        return $this->readInt(1);
    }

    #[\Override]
    public function readInt16(): int
    {
        return $this->readInt(2);
    }

    #[\Override]
    public function readInt24(): int
    {
        return $this->readInt(3);
    }

    #[\Override]
    public function readInt32(): int
    {
        return $this->readInt(4);
    }

    #[\Override]
    public function readInt64(): int
    {
        return $this->readInt(8);
    }

    #[\Override]
    public function readInt16BE(): int
    {
        return $this->readIntBE(2);
    }

    #[\Override]
    public function readInt24BE(): int
    {
        return $this->readIntBE(3);
    }

    #[\Override]
    public function readInt32BE(): int
    {
        return $this->readIntBE(4);
    }

    #[\Override]
    public function readInt64BE(): int
    {
        return $this->readIntBE(8);
    }

    #[\Override]
    public function readUInt8(): int
    {
        return $this->readUInt(1);
    }

    #[\Override]
    public function readUInt16(): int
    {
        return $this->readUInt(2);
    }

    #[\Override]
    public function readUInt24(): int
    {
        return $this->readUInt(3);
    }

    #[\Override]
    public function readUInt32(): int
    {
        return $this->readUInt(4);
    }

    #[\Override]
    public function readUInt64(): int
    {
        return $this->readUInt(8);
    }

    #[\Override]
    public function readUInt16BE(): int
    {
        return $this->readUIntBE(2);
    }

    #[\Override]
    public function readUInt24BE(): int
    {
        return $this->readUIntBE(3);
    }

    #[\Override]
    public function readUInt32BE(): int
    {
        return $this->readUIntBE(4);
    }

    #[\Override]
    public function readUInt64BE(): int
    {
        return $this->readUIntBE(8);
    }

    #[\Override]
    public function readFloat(): float
    {
        $this->checkOffset(4);
        $slice = substr($this->buffer, $this->position, 4);
        $result = unpack('g', $slice)[1];
        $this->position += 4;
        return $result;
    }

    #[\Override]
    public function readFloatBE(): float
    {
        $this->checkOffset(4);
        $slice = substr($this->buffer, $this->position, 4);
        $result = unpack('G', $slice)[1];
        $this->position += 4;
        return $result;
    }

    #[\Override]
    public function writeBytes(array $bytes): self
    {
        $this->buffer .= pack('C*', ...$bytes);
        return $this;
    }

    #[\Override]
    public function readBytes(?int $size = null): array
    {
        $available = $this->size() - $this->position;
        $readLength = $size ?? $available;

        if ($readLength < 0) {
            throw new InvalidArgumentException('Size cannot be negative.');
        }
        if ($size !== null) {
            $this->checkOffset($readLength);
        } elseif ($readLength === 0 && $available > 0) {
            $readLength = $available;
        }


        if ($readLength === 0) {
            return []; // Nothing to read
        }

        $slice = substr($this->buffer, $this->position, $readLength);
        $this->position += $readLength;

        return array_values(unpack('C*', $slice));
    }

    #[\Override]
    public function getPosition(): int
    {
        return $this->position;
    }

    #[\Override]
    public function setPosition(int $position): void
    {
        if ($position < 0 || $position > $this->size()) {
            throw new InvalidArgumentException("Position {$position} is out of bounds (0, " . $this->size() . ")");
        }
        $this->position = $position;
    }

    #[\Override]
    public function getBuffer(): string
    {
        return $this->buffer;
    }

    #[\Override]
    public function ltrim(): self
    {
        if ($this->position > 0) {
            $this->buffer = substr($this->buffer, $this->position);
            $this->position = 0;
        }
        return $this;
    }

    #[\Override]
    public function rtrim(): self
    {
        $this->buffer = substr($this->buffer, 0, $this->position);
        $this->position = 0;
        return $this;
    }

    #[\Override]
    public function clear(): self
    {
        $this->position = 0;
        $this->buffer = '';
        return $this;
    }

    #[\Override]
    public function remains(): int
    {
        return $this->size() - $this->getPosition();
    }

    #[\Override]
    public function size(): int
    {
        return strlen($this->buffer);
    }

    #[\Override]
    public function isMore(): bool
    {
        return $this->remains() > 0;
    }

    #[\Override]
    public function encode(): string
    {
        return $this->buffer;
    }

    #[\Override]
    public function toBytesArray(): array
    {
        if ($this->buffer === '') {
            return [];
        }

        return array_values(unpack('C*', $this->buffer));
    }

    public function __toString(): string
    {
        if ($this->buffer === '') {
            return '';
        }
        $bytes = array_values(unpack('C*', $this->buffer));

        return implode(' ', array_map(fn ($v) => sprintf('%02X', $v), $bytes));
    }

    #[\Override]
    public function writeListUInt8(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt8($val);
        }

        return $this;
    }

    #[\Override]
    public function writeListUInt16(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt16($val);
        }

        return $this;
    }

    #[\Override]
    public function writeListUInt24(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt24($val);
        }

        return $this;
    }

    #[\Override]
    public function writeListUInt32(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt32($val);
        }

        return $this;
    }

    #[\Override]
    public function writeList8(array $list): self
    {
        foreach ($list as $item) {
            $this->writeUInt8($item);
        }

        return $this;
    }

    #[\Override]
    public function writeList16(array $list): self
    {
        foreach ($list as $item) {
            $this->writeUInt16($item);
        }

        return $this;
    }

    #[\Override]
    public function readListUInt8(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt8();
        }

        return $result;
    }

    #[\Override]
    public function readListUInt16(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt16();
        }

        return $result;
    }

    #[\Override]
    public function readListUInt24(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt24();
        }

        return $result;
    }

    #[\Override]
    public function readListUInt32(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt32();
        }

        return $result;
    }

    #[\Override]
    public function readList8(int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readUInt8();
        }

        return $result;
    }

    #[\Override]
    public function readList16(int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readUInt16();
        }

        return $result;
    }

    private function checkSigned($value, int $size): void
    {
        $bits = $size * 8;
        $min = -pow(2, $bits - 1);
        $max = pow(2, $bits - 1) - 1;
        if ($value > $max || $value < $min) {
            throw new UnexpectedValueException(sprintf('Value %d is out of signed %d-bit range [%d, %d]', $value, $bits, $min, $max));
        }
    }

    private function checkUnsigned($value, int $size): void
    {
        $bits = $size * 8;
        $min = 0;
        $max = pow(2, $bits) - 1;

        if ($value < $min || $value > $max) { // Compare using GMP
            throw new UnexpectedValueException(sprintf('Value %d is out of unsigned %d-bit range [%d, %s]', $value, $bits, $min, $max));
        }
    }

    private function checkOffset(int $bytes): void
    {
        $available = strlen($this->buffer) - $this->position;
        if ($bytes < 0) {
            throw new InvalidArgumentException('Cannot read negative number of bytes.');
        }
        if ($available < $bytes) {
            throw new InvalidArgumentException(
                sprintf('There are not enough bytes in the buffer. Current position %d, buffer size %d, bytes remaining %d, needed %d.', $this->position, strlen($this->buffer), $available, $bytes)
            );
        }
    }
}
