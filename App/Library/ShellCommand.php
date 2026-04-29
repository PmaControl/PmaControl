<?php

declare(strict_types=1);

namespace App\Library;

final class ShellCommand
{
    private const TOKEN_PATTERN = '/\A(?:--|--?[A-Za-z0-9][A-Za-z0-9_-]*)\z/';

    /**
     * @var list<string>
     */
    private array $parts = [];

    private bool $finalized = false;

    private function __construct()
    {
    }

    public static function of(string $binary): self
    {
        $command = new self();
        $command->parts[] = self::isSafeBareCommand($binary) ? $binary : escapeshellarg($binary);

        return $command;
    }

    /**
     * @param array<int, mixed> $args
     */
    public static function fromArguments(array $args): self
    {
        if ($args === []) {
            throw new \InvalidArgumentException('Shell command requires at least one argument');
        }

        $command = self::of((string) array_shift($args));

        foreach ($args as $arg) {
            $command->arg((string) $arg);
        }

        return $command;
    }

    public function arg(string $value): self
    {
        $this->guardNotFinalized();
        $this->parts[] = escapeshellarg($value);

        return $this;
    }

    /**
     * @param array<int, mixed> $args
     */
    public function args(array $args): self
    {
        foreach ($args as $arg) {
            $this->arg((string) $arg);
        }

        return $this;
    }

    public function flag(string $name): self
    {
        $this->guardNotFinalized();
        self::assertToken($name);
        $this->parts[] = $name;

        return $this;
    }

    public function rawToken(string $token): self
    {
        return $this->flag($token);
    }

    public function option(string $name, string $value, string $separator = ' '): self
    {
        $this->guardNotFinalized();
        self::assertToken($name);
        self::assertSeparator($separator);

        if ($separator === '=') {
            $this->parts[] = $name.'='.escapeshellarg($value);

            return $this;
        }

        $this->parts[] = $name;
        $this->parts[] = escapeshellarg($value);

        return $this;
    }

    public function intOption(string $name, int $value, string $separator = ' '): self
    {
        $this->guardNotFinalized();
        self::assertToken($name);
        self::assertSeparator($separator);

        if ($separator === '=') {
            $this->parts[] = $name.'='.(string) $value;

            return $this;
        }

        $this->parts[] = $name;
        $this->parts[] = (string) $value;

        return $this;
    }

    public function redirect(int $fileDescriptor, string $operator, string $file): self
    {
        $this->guardNotFinalized();

        if (!in_array($fileDescriptor, [1, 2], true)) {
            throw new \InvalidArgumentException('Unsupported shell redirection file descriptor');
        }

        if (!in_array($operator, ['>', '>>'], true)) {
            throw new \InvalidArgumentException('Unsupported shell redirection operator');
        }

        $this->parts[] = ($fileDescriptor === 1 ? '' : (string) $fileDescriptor).$operator;
        $this->parts[] = escapeshellarg($file);

        return $this;
    }

    public function mergeStderrIntoStdout(): self
    {
        $this->guardNotFinalized();
        $this->parts[] = '2>&1';

        return $this;
    }

    public function pipeTo(self $next): self
    {
        $this->guardNotFinalized();
        $next->guardNotFinalized();

        $command = new self();
        $command->parts = array_merge($this->parts, ['|'], $next->parts);

        return $command;
    }

    public function inBackgroundCapturingPid(): self
    {
        $this->guardNotFinalized();
        $this->parts[] = '& echo $!';
        $this->finalized = true;

        return $this;
    }

    public function toString(): string
    {
        return implode(' ', $this->parts);
    }

    private function guardNotFinalized(): void
    {
        if ($this->finalized) {
            throw new \LogicException('Shell command is already finalized');
        }
    }

    private static function assertToken(string $token): void
    {
        if (preg_match(self::TOKEN_PATTERN, $token) !== 1) {
            throw new \InvalidArgumentException('Invalid shell token');
        }
    }

    private static function assertSeparator(string $separator): void
    {
        if ($separator !== ' ' && $separator !== '=') {
            throw new \InvalidArgumentException('Invalid shell option separator');
        }
    }

    private static function isSafeBareCommand(string $binary): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._+-]*\z/', $binary) === 1;
    }
}
