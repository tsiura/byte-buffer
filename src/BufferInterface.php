<?php

declare(strict_types=1);

namespace Zeran\ByteBuffer;

use Stringable;

interface BufferInterface extends Stringable
{
    public static function fromArray(array $buffer): self;

    public function writeInt(int $value, int $size): self;

    public function writeIntBE(int $value, int $size): self;

    public function writeUInt(int $value, int $size): self;

    public function writeUIntBE(int $value, int $size): self;

    public function writeFloat(float $value): self;

    public function writeFloatBE(float $value): self;

    public function writeDouble(float $value): self;

    public function writeDoubleBE(float $value): self;

    public function readDouble(): float;

    public function readDoubleBE(): float;

    public function writeUtf8String(string $value): self;

    public function writeInt8(int $value): self;

    public function writeUInt8(int $value): self;

    public function writeInt16(int $value): self;

    public function writeInt16BE(int $value): self;

    public function writeInt24(int $value): self;

    public function writeInt24BE(int $value): self;

    public function writeInt32(int $value): self;

    public function writeInt32BE(int $value): self;

    public function writeInt64(int $value): self;

    public function writeInt64BE(int $value): self;

    public function writeUInt16(int $value): self;

    public function writeUInt16BE(int $value): self;

    public function writeUInt24(int $value): self;

    public function writeUInt24BE(int $value): self;

    public function writeUInt32(int $value): self;

    public function writeUInt32BE(int $value): self;

    public function writeUInt64(int $value): self;

    public function writeUInt64BE(int $value): self;

    public function readUtf8String(?int $length = null): string;

    public function readInt(int $size): int;

    public function readIntBE(int $size): int;

    public function readUInt(int $size): int;

    public function readUIntBE(int $size): int;

    public function readInt8(): int;

    public function readInt16(): int;

    public function readInt24(): int;

    public function readInt32(): int;

    public function readInt64(): int;

    public function readInt16BE(): int;

    public function readInt24BE(): int;

    public function readInt32BE(): int;

    public function readInt64BE(): int;

    public function readUInt8(): int;

    public function readUInt16(): int;

    public function readUInt24(): int;

    public function readUInt32(): int;

    public function readUInt64(): int;

    public function readUInt16BE(): int;

    public function readUInt24BE(): int;

    public function readUInt32BE(): int;

    public function readUInt64BE(): int;

    public function readFloat(): float;

    public function readFloatBE(): float;

    public function writeBytes(array $bytes): self;

    public function readBytes(?int $size = null): array;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    public function getBuffer(): string;

    public function ltrim(): self;

    public function rtrim(): self;

    public function clear(): self;

    public function remains(): int;

    public function size(): int;

    public function isMore(): bool;

    public function encode(): string;

    public function toBytesArray(): array;

    public function __toString(): string;

    public function writeListUInt8(array $data): self;

    public function writeListUInt16(array $data): self;

    public function writeListUInt24(array $data): self;

    public function writeListUInt32(array $data): self;

    public function readListUInt8(int $count): array;

    public function readListUInt16(int $count): array;

    public function readListUInt24(int $count): array;

    public function readListUInt32(int $count): array;

    public function readList8(int $size): array;

    public function writeList8(array $list): self;

    public function readList16(int $size): array;

    public function writeList16(array $list): self;
}
