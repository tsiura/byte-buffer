<?php

declare(strict_types=1);

namespace Tsiura\ByteBuffer;

use Stringable;

class Buffer implements Stringable
{
    private int $position = 0;

    public function __construct(
        private array $buffer = []
    ) {
    }

    public static function fromArray(array $buffer): self
    {
        return new self($buffer);
    }

    public static function fromString(string $buffer): self
    {
        $self = new self();
        $self->writeUtf8String($buffer);

        return $self;
    }

    public function writeInt(int $value, int $size): self
    {
        $this->checkSigned($value, $size);

        for ($i = 0; $i < $size; ++$i) {
            $this->buffer[] = ($value & 0xff);
            $value >>= 8;
        }

        return $this;
    }

    public function writeIntBE(int $value, int $size): self
    {
        $this->checkSigned($value, $size);

        $buffer = [];
        for ($i = 0; $i < $size; ++$i) {
            $buffer[] = ($value & 0xff);
            $value >>= 8;
        }

        $this->buffer = array_merge($this->buffer, array_reverse($buffer));

        return $this;
    }

    public function writeUInt(int $value, int $size): self
    {
        $this->checkUnsigned($value, $size);

        for ($i = 0; $i < $size; ++$i) {
            $this->buffer[] = ($value & 0xff);
            $value >>= 8;
        }

        return $this;
    }

    public function writeUIntBE(int $value, int $size): self
    {
        $this->checkUnsigned($value, $size);

        $buffer = [];
        for ($i = 0; $i < $size; ++$i) {
            $buffer[] = ($value & 0xff);
            $value >>= 8;
        }

        $this->buffer = array_merge($this->buffer, array_reverse($buffer));

        return $this;
    }

    public function writeFloat(float $value): self
    {
        $this->buffer = array_merge($this->buffer, unpack('C*', pack('g', $value)));

        return $this;
    }

    public function writeFloatBE(float $value): self
    {
        $this->buffer = array_merge($this->buffer, unpack('C*', pack('G', $value)));

        return $this;
    }

    public function writeDouble(float $value): self
    {
        $this->buffer = array_merge($this->buffer, unpack('C*', pack('e', $value)));

        return $this;
    }

    public function writeDoubleBE(float $value): self
    {
        $this->buffer = array_merge($this->buffer, unpack('C*', pack('E', $value)));

        return $this;
    }

    public function writeUtf8String(string $value): self
    {
        $this->buffer = array_merge($this->buffer, unpack('C*', pack('a*', $value)));

        return $this;
    }

    public function writeInt8(int $value): self
    {
        $this->checkSigned($value, 1);

        $this->buffer = array_merge($this->buffer, unpack('C*', pack('c', $value)));

        return $this;
    }

    public function writeUInt8(int $value): self
    {
        $this->checkUnsigned($value, 1);

        $this->buffer = array_merge($this->buffer, unpack('C*', pack('C', $value)));

        return $this;
    }

    public function writeInt16(int $value): self
    {
        return $this->writeInt($value, 2);
    }

    public function writeInt16BE(int $value): self
    {
        return $this->writeIntBE($value, 2);
    }

    public function writeInt24(int $value): self
    {
        return $this->writeInt($value, 3);
    }

    public function writeInt24BE(int $value): self
    {
        return $this->writeIntBE($value, 3);
    }

    public function writeInt32(int $value): self
    {
        return $this->writeInt($value, 4);
    }

    public function writeInt32BE(int $value): self
    {
        return $this->writeIntBE($value, 4);
    }

    public function writeInt64(int $value): self
    {
        return $this->writeInt($value, 8);
    }

    public function writeInt64BE(int $value): self
    {
        return $this->writeIntBE($value, 8);
    }

    public function writeUInt16(int $value): self
    {
        return $this->writeUInt($value, 2);
    }

    public function writeUInt16BE(int $value): self
    {
        return $this->writeUIntBE($value, 2);
    }

    public function writeUInt24(int $value): self
    {
        return $this->writeUInt($value, 3);
    }

    public function writeUInt24BE(int $value): self
    {
        return $this->writeUIntBE($value, 3);
    }

    public function writeUInt32(int $value): self
    {
        return $this->writeUInt($value, 4);
    }

    public function writeUInt32BE(int $value): self
    {
        return $this->writeUIntBE($value, 4);
    }

    public function writeUInt64(int $value): self
    {
        return $this->writeUInt($value, 8);
    }

    public function writeUInt64BE(int $value): self
    {
        return $this->writeUIntBE($value, 8);
    }

    public function readUtf8String(?int $length = null): string
    {
        if ($length > 0) {
            $this->checkOffset($length);
        }
        $slice = array_slice($this->buffer, $this->position, $length);
        $this->position = null !== $length
            ? $this->position + $length
            : array_key_last($this->buffer);

        return pack('C*', ...$slice);
    }

    public function readInt(int $size): int
    {
        $this->checkOffset($size);

        $val = $this->buffer[$this->position];
        $mul = 1;
        $i = 0;
        while (++$i < $size) {
            $mul *= 0x100;
            $val += $this->buffer[$this->position + $i] * $mul;
        }
        $mul *= 0x80;
        $this->position += $size;

        return $val >= $mul
            ? $val - pow(2, 8 * $size)
            : $val;
    }

    public function readIntBE(int $size): int
    {
        $this->checkOffset($size);

        $i = $size;
        $mul = 1;
        $val = $this->buffer[$this->position + --$i];
        while ($i > 0) {
            $mul *= 0x100;
            $val += $this->buffer[$this->position + --$i] * $mul;
        }
        $mul *= 0x80;
        $this->position += $size;

        return $val >= $mul
            ? $val - pow(2, 8 * $size)
            : $val;
    }

    public function readUInt(int $size): int
    {
        $this->checkOffset($size);

        $val = $this->buffer[$this->position];
        $mul = 1;
        $i = 0;
        while (++$i < $size) {
            $mul *= 0x100;
            $val += $this->buffer[$this->position + $i] * $mul;
        }
        $this->position += $size;

        return $val;
    }

    public function readUIntBE(int $size): int
    {
        $this->checkOffset($size);
        $i = $size;
        $mul = 1;
        $val = $this->buffer[$this->position + --$i];
        while ($i > 0) {
            $mul *= 0x100;
            $val += $this->buffer[$this->position + --$i] * $mul;
        }
        $this->position += $size;

        return $val;
    }

    public function readInt8(): int
    {
        return $this->readInt(1);
    }

    public function readInt16(): int
    {
        return $this->readInt(2);
    }

    public function readInt24(): int
    {
        return $this->readInt(3);
    }

    public function readInt32(): int
    {
        return $this->readInt(4);
    }

    public function readInt64(): int
    {
        return $this->readInt(8);
    }

    public function readInt16BE(): int
    {
        return $this->readIntBE(2);
    }

    public function readInt24BE(): int
    {
        return $this->readIntBE(3);
    }

    public function readInt32BE(): int
    {
        return $this->readIntBE(4);
    }

    public function readInt64BE(): int
    {
        return $this->readIntBE(8);
    }

    public function readUInt8(): int
    {
        return $this->readUInt(1);
    }

    public function readUInt16(): int
    {
        return $this->readUInt(2);
    }

    public function readUInt24(): int
    {
        return $this->readUInt(3);
    }

    public function readUInt32(): int
    {
        return $this->readUInt(4);
    }

    public function readUInt64(): int
    {
        return $this->readUInt(8);
    }

    public function readUInt16BE(): int
    {
        return $this->readUIntBE(2);
    }

    public function readUInt24BE(): int
    {
        return $this->readUIntBE(3);
    }

    public function readUInt32BE(): int
    {
        return $this->readUIntBE(4);
    }

    public function readUInt64BE(): int
    {
        return $this->readUIntBE(8);
    }

    public function readFloat(): float
    {
        $this->checkOffset(4);

        $slice = array_slice($this->buffer, $this->position, 4);
        $result = unpack('g', pack('C4', ...$slice))[1];

        $this->position += 4;

        return $result;
    }

    public function readFloatBE(): float
    {
        $this->checkOffset(4);

        $slice = array_slice($this->buffer, $this->position, 4);
        $result = unpack('G', pack('C4', ...$slice))[1];

        $this->position += 4;

        return $result;
    }

    public function writeBytes(array $bytes): self
    {
        $this->buffer = array_merge($this->buffer, $bytes);

        return $this;
    }

    public function readBytes(?int $size = null): array
    {
        if ($size > 0) {
            $this->checkOffset($size);
        }
        $slice = array_slice($this->buffer, $this->position, $size);
        $this->position = null !== $size
            ? $this->position + $size
            : array_key_last($this->buffer);

        return $slice;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getBuffer(): string
    {
        return implode(array_map('chr', $this->buffer));
    }

    public function ltrim(): self
    {
        $this->buffer = array_slice($this->buffer, $this->position);
        $this->position = 0;

        return $this;
    }

    public function rtrim(): self
    {
        $this->buffer = array_slice($this->buffer, 0, $this->position + 1);
        $this->position = 0;

        return $this;
    }

    public function clear(): self
    {
        $this->position = 0;
        $this->buffer = [];

        return $this;
    }

    public function remains(): int
    {
        return $this->size() - $this->getPosition() - 1;
    }

    public function size(): int
    {
        return count($this->buffer);
    }

    public function isMore(): bool
    {
        return $this->remains() > 0;
    }

    public function encode(): string
    {
        return implode('', array_map('chr', $this->buffer));
    }

    public function __toString(): string
    {
        return implode(' ', array_map(fn ($v) => sprintf('%02X', $v), $this->buffer));
    }

    public function writeListUInt8(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt8($val);
        }

        return $this;
    }

    public function writeListUInt16(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt16($val);
        }

        return $this;
    }

    public function writeListUInt24(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt24($val);
        }

        return $this;
    }

    public function writeListUInt32(array $data): self
    {
        foreach ($data as $val) {
            $this->writeUInt32($val);
        }

        return $this;
    }

    public function readListUInt8(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt8();
        }

        return $result;
    }

    public function readListUInt16(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt16();
        }

        return $result;
    }

    public function readListUInt24(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt24();
        }

        return $result;
    }

    public function readListUInt32(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->readUInt32();
        }

        return $result;
    }

    public function readList8(int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readUInt8();
        }

        return $result;
    }

    public function writeList8(array $list): self
    {
        foreach ($list as $item) {
            $this->writeUInt8($item);
        }

        return $this;
    }

    public function readList16(int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readUInt16();
        }

        return $result;
    }

    public function writeList16(array $list): self
    {
        foreach ($list as $item) {
            $this->writeUInt16($item);
        }

        return $this;
    }

    private function checkSigned($value, int $size): void
    {
        $min = -(128 * pow(256, ($size - 1)));
        $max = -($min) - 1;
        if ($value > $max || $value < $min) {
            throw new \UnexpectedValueException(sprintf('Value %d is out of range [%d, %d]', $value, $min, $max));
        }
    }

    private function checkUnsigned($value, int $size): void
    {
        $min = 0;
        $max = pow(256, $size);
        if ($value > $max || $value < $min) {
            throw new \UnexpectedValueException(sprintf('Value %d is out of range [%d, %d]', $value, $min, $max));
        }
    }

    private function checkOffset(int $bytes): void
    {
        if (($this->size() - $this->position) < $bytes) {
            throw new \InvalidArgumentException(
                sprintf('There are not enough bytes in the buffer. Current %d, needed %d.', $this->size() - $this->position, $bytes)
            );
        }
    }
}
