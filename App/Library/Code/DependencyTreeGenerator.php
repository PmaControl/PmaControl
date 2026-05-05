<?php

declare(strict_types=1);

namespace App\Library\Code;

final class DependencyTreeGenerator
{
    private array $files = [];
    private array $tree = [];

    public function init(string $directory): void
    {
        $this->files = [];
        $this->loadFiles($directory);
    }

    public function generateTree(string $className, string $methodName): array
    {
        $this->tree = [];
        $methodCalls = $this->parseMethodCalls($className, $methodName);

        foreach ($methodCalls as $calledMethod => $calls) {
            if (!isset($this->tree[$methodName])) {
                $this->tree[$methodName] = [];
            }

            if (!in_array($calledMethod, array_column($this->tree[$methodName], 'method'), true)) {
                $this->tree[$methodName][] = [
                    'method' => $calledMethod,
                    'calls' => $calls,
                ];
            }
        }

        return $this->buildTreeRecursive($methodName);
    }

    private function loadFiles(string $directory): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && pathinfo((string)$file, PATHINFO_EXTENSION) === 'php') {
                $this->files[] = $file->getPathname();
            }
        }
    }

    private function parseMethodCalls(string $className, string $methodName): array
    {
        foreach ($this->files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            if (preg_match("/class\\s+{$className}\\b.*?function\\s+{$methodName}\\b/s", $content) === 1) {
                return $this->extractMethodCalls($content, $methodName);
            }
        }

        return [];
    }

    private function extractMethodCalls(string $content, string $currentMethodName): array
    {
        preg_match_all("/\\b(?:self|parent|static)::(.*?)\\(/", $content, $matches);
        if (!isset($matches[1])) {
            return [];
        }

        $methodCalls = [];
        foreach ($matches[1] as $methodCall) {
            $pattern = "/function\\s+{$currentMethodName}\\b.*?\\b{$methodCall}\\(/s";
            if (preg_match_all($pattern, $content, $nestedMatches) === 1) {
                foreach ($nestedMatches[0] as $match) {
                    preg_match("/function\\s+(.*?)\\b/", $match, $funcNameMatch);
                    if (isset($funcNameMatch[1])) {
                        $methodName = trim($funcNameMatch[1]);
                        $methodCalls[$methodName][] = $methodCall;
                    }
                }
            }

            $methodCalls += $this->extractMethodCalls($content, $methodCall);
        }

        return $methodCalls;
    }

    private function buildTreeRecursive(string $methodName, array &$tree = []): array
    {
        if (!isset($this->tree[$methodName])) {
            return $tree;
        }

        foreach ($this->tree[$methodName] as $dependency) {
            if (!isset($tree[$dependency['method']])) {
                $tree[$dependency['method']] = [];
            }

            $subTree = $this->buildTreeRecursive($dependency['method']);
            if ($subTree !== []) {
                $tree[$dependency['method']] += $subTree;
            }
        }

        return $tree;
    }
}
