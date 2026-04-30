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

    public static function gzip(string $pathFile, bool $decompress = false): string
    {
        if ($pathFile === '') {
            throw new \InvalidArgumentException('Path file cannot be empty.');
        }

        return 'nice gzip' . ($decompress ? ' -d' : '') . ' -- ' . escapeshellarg($pathFile);
    }

    public static function dockerImagePull(string $name, string $tag): ?string
    {
        $image = self::dockerImageReference($name, $tag);
        if ($image === null) {
            return null;
        }

        return 'docker image pull ' . escapeshellarg($image);
    }

    public static function dockerImageReference(string $name, string $tag): ?string
    {
        $name = trim($name);
        $tag = trim($tag);

        if (!self::isSafeDockerImageName($name) || !self::isSafeDockerTag($tag)) {
            return null;
        }

        return $name . ':' . $tag;
    }

    public static function skopeoDockerInspect(string $name): ?string
    {
        $name = trim($name);
        if (!self::isSafeDockerImageName($name)) {
            return null;
        }

        return 'skopeo inspect ' . escapeshellarg('docker://' . $name);
    }

    public static function gitLogRange(string $build): ?string
    {
        $build = trim($build);
        if (!self::isSafeGitRevision($build)) {
            return null;
        }

        return 'git log ' . escapeshellarg($build . '..HEAD') . ' --pretty=format:"%H"';
    }

    public static function isSafeGitRevision(string $revision): bool
    {
        if ($revision === '' || str_starts_with($revision, '-') || str_contains($revision, '..')) {
            return false;
        }

        return (bool) preg_match('/\A[A-Za-z0-9._\/-]+\z/', $revision);
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

    private static function isSafeDockerImageName(string $name): bool
    {
        if ($name === '' || str_starts_with($name, '-') || str_contains($name, '..')) {
            return false;
        }

        if (!preg_match('/\A[a-z0-9][a-z0-9._\/:-]*\z/', $name)) {
            return false;
        }

        foreach (explode('/', $name) as $part) {
            if ($part === '' || str_starts_with($part, '-')) {
                return false;
            }
        }

        return true;
    }

    private static function isSafeDockerTag(string $tag): bool
    {
        return (bool) preg_match('/\A[A-Za-z0-9_][A-Za-z0-9._-]{0,127}\z/', $tag);
    }

    private static function isSafeBareCommand(string $binary): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._+-]*\z/', $binary) === 1;
    }
}
