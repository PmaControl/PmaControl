<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ViewActionParityTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testRoutableViewsHavePublicControllerActions(): void
    {
        $orphans = [];

        foreach ($this->viewFiles() as $viewFile) {
            $filename = basename($viewFile);
            if (str_starts_with($filename, '_')) {
                continue;
            }

            $viewRelative = $this->relativePath($viewFile, self::ROOT . '/App/view/');
            $parts = explode('/', $viewRelative);
            if (count($parts) < 2) {
                continue;
            }

            $controller = $parts[0];
            $action = substr($filename, 0, -strlen('.view.php'));
            $controllerFile = self::ROOT . '/App/Controller/' . $controller . '.php';

            if (!is_file($controllerFile)) {
                $orphans[] = $viewRelative . ' has no App/Controller/' . $controller . '.php';
                continue;
            }

            $methods = $this->publicControllerMethods($controllerFile);
            if (!isset($methods[strtolower($action)])) {
                $orphans[] = $viewRelative . ' has no public ' . $controller . '::' . $action . '()';
            }
        }

        sort($orphans);

        $this->assertSame(
            [],
            $orphans,
            "Every routable App/view/<Controller>/<action>.view.php file must have a public controller action.\n"
                . implode("\n", $orphans)
        );
    }

    /**
     * @return list<string>
     */
    private function viewFiles(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::ROOT . '/App/view', FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.view.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return array<string,true>
     */
    private function publicControllerMethods(string $controllerFile): array
    {
        $tokens = token_get_all((string) file_get_contents($controllerFile));
        $methods = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }

            $nameIndex = $this->functionNameIndex($tokens, $i);
            if ($nameIndex === null || !$this->isPublicFunction($tokens, $i)) {
                continue;
            }

            $methods[strtolower($tokens[$nameIndex][1])] = true;
        }

        return $methods;
    }

    /**
     * @param array<int,mixed> $tokens
     */
    private function functionNameIndex(array $tokens, int $functionIndex): ?int
    {
        $index = $functionIndex + 1;
        $count = count($tokens);

        while ($index < $count && $this->isIgnorableToken($tokens[$index])) {
            $index++;
        }

        if ($index < $count && $this->isReferenceToken($tokens[$index])) {
            $index++;
            while ($index < $count && $this->isIgnorableToken($tokens[$index])) {
                $index++;
            }
        }

        return ($index < $count && is_array($tokens[$index])) ? $index : null;
    }

    /**
     * @param array<int,mixed> $tokens
     */
    private function isPublicFunction(array $tokens, int $functionIndex): bool
    {
        for ($index = $functionIndex - 1; $index >= 0; $index--) {
            $token = $tokens[$index];

            if ($this->isIgnorableToken($token)) {
                continue;
            }

            if (is_array($token) && in_array($token[0], [T_STATIC, T_FINAL, T_ABSTRACT], true)) {
                continue;
            }

            if (is_array($token) && $token[0] === T_PUBLIC) {
                return true;
            }

            if (is_array($token) && in_array($token[0], [T_PRIVATE, T_PROTECTED], true)) {
                return false;
            }

            return true;
        }

        return true;
    }

    private function isIgnorableToken(mixed $token): bool
    {
        return is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
    }

    private function isReferenceToken(mixed $token): bool
    {
        if ($token === '&') {
            return true;
        }

        return is_array($token)
            && in_array($token[0], [T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG, T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG], true);
    }

    private function relativePath(string $path, string $prefix): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($prefix)));
    }
}
