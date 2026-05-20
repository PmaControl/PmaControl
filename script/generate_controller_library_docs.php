<?php

declare(strict_types=1);

/**
 * Generate baseline PHPDoc blocks in App/Controller and App/Library and write
 * Markdown summaries into documentation/Controller~Library.
 */
final class ControllerLibraryDocGenerator
{
    private const AUTHOR = 'Aurélien LEQUOY <pmacontrol@68koncept.com>';
    private const LICENSE = 'GPL-3.0';
    private const SINCE = '5.0';
    private const VERSION = '1.0';

    /**
     * @var list<string>
     */
    private array $targets;

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * @var array<int,array{title:string,file:string,kind:string,methods:list<string>}>
     */
    private array $documentationIndex = [];

    /**
     * @param list<string> $targets Directories scanned for PHP files.
     */
    public function __construct(array $targets)
    {
        $this->targets = $targets;
        $this->baseDir = getcwd() ?: __DIR__.'/..';
    }

    public function run(): void
    {
        foreach ($this->targets as $target) {
            foreach ($this->getPhpFiles($target) as $file) {
                $this->processFile($file);
            }
        }

        $this->writeIndex();
    }

    /**
     * @return list<string>
     */
    private function getPhpFiles(string $relativeDir): array
    {
        $directory = $this->baseDir.'/'.$relativeDir;
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $item) {
            if (!$item->isFile() || $item->getExtension() !== 'php') {
                continue;
            }

            $files[] = $item->getPathname();
        }

        sort($files);

        return $files;
    }

    private function processFile(string $path): void
    {
        $source = (string) file_get_contents($path);
        if ($source === '') {
            return;
        }

        $lines = preg_split("/\r\n|\n|\r/", $source) ?: [];
        $tokens = token_get_all($source);

        $namespace = '';
        $className = '';
        $classKind = '';
        $classDepth = null;
        $braceDepth = 0;
        $insertions = [];
        $methodDocs = [];
        $line = 1;

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                $line = $token[2];
            }

            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $namespace = $this->collectNamespace($tokens, $i);
                continue;
            }

            if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                $name = $this->nextIdentifier($tokens, $i);
                if ($name === null) {
                    continue;
                }

                $className = $name;
                $classKind = $this->mapClassKind($token[0]);

                if (!$this->hasDocblockAbove($lines, $line)) {
                    $insertions[] = [
                        'line' => $line,
                        'doc' => $this->buildClassDoc($namespace, $className, $classKind),
                    ];
                }

                $methodDocs[] = '# '.$className;
                $methodDocs[] = '';
                $methodDocs[] = '- Type: '.$classKind;
                $methodDocs[] = '- Namespace: `'.$namespace.'`';
                $methodDocs[] = '- Source: `'.$this->relativePath($path).'`';
                $methodDocs[] = '';

                $classDepth = $braceDepth;
                continue;
            }

            if ($className !== '' && is_array($token) && $this->isPropertyToken($tokens, $i, $braceDepth, $classDepth)) {
                $propertyName = ltrim($token[1], '$');
                if (!$this->hasDocblockAbove($lines, $line)) {
                    $insertions[] = [
                        'line' => $line,
                        'doc' => $this->buildPropertyDoc($propertyName, $this->inferPropertyType($tokens, $i)),
                    ];
                }
                continue;
            }

            if (is_array($token) && $token[0] === T_FUNCTION) {
                $function = $this->parseFunction($tokens, $i, $namespace, $className, $classKind);
                if ($function === null) {
                    continue;
                }

                $methodDocs[] = '- `'.$function['name'].'('.implode(', ', array_map(
                    static fn (array $param): string => '$'.$param['name'],
                    $function['params']
                )).')`: '.$function['summary'];

                if (!$this->hasDocblockAbove($lines, $function['line'])) {
                    $insertions[] = [
                        'line' => $function['line'],
                        'doc' => $this->buildFunctionDoc($function, $namespace, $className, $classKind),
                    ];
                }
            }

            if ($token === '{') {
                $braceDepth++;
                continue;
            }

            if ($token === '}') {
                $braceDepth--;
                if ($classDepth !== null && $braceDepth === $classDepth) {
                    $classDepth = null;
                    if ($className !== '') {
                        $this->writeMarkdownSummary($path, $className, $classKind, $methodDocs);
                        $methodDocs = [];
                    }
                    $className = '';
                    $classKind = '';
                }
            }
        }

        if ($insertions !== []) {
            usort(
                $insertions,
                static fn (array $left, array $right): int => $right['line'] <=> $left['line']
            );

            foreach ($insertions as $insertion) {
                array_splice($lines, $insertion['line'] - 1, 0, [$insertion['doc']]);
            }

            file_put_contents($path, implode("\n", $lines)."\n");
        }
    }

    private function writeMarkdownSummary(string $path, string $className, string $classKind, array $methodDocs): void
    {
        $directory = $this->baseDir.'/documentation/Controller~Library';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = $directory.'/'.$className.'.md';
        file_put_contents($filename, implode("\n", $methodDocs)."\n");

        $methods = [];
        foreach ($methodDocs as $line) {
            if (str_starts_with($line, '- `')) {
                $methods[] = $line;
            }
        }

        $this->documentationIndex[] = [
            'title' => $className,
            'file' => 'documentation/Controller~Library/'.$className.'.md',
            'kind' => $classKind,
            'methods' => $methods,
        ];
    }

    private function writeIndex(): void
    {
        $directory = $this->baseDir.'/documentation/Controller~Library';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        usort(
            $this->documentationIndex,
            static fn (array $left, array $right): int => strcmp($left['title'], $right['title'])
        );

        $lines = [
            '# Controller and Library Reference',
            '',
            'Generated automatically from `App/Controller` and `App/Library`.',
            '',
        ];

        foreach ($this->documentationIndex as $entry) {
            $lines[] = '## '.$entry['title'];
            $lines[] = '';
            $lines[] = '- Kind: '.$entry['kind'];
            $lines[] = '- Summary: `'.$entry['file'].'`';
            $lines[] = '- Methods: '.count($entry['methods']);
            $lines[] = '';
        }

        file_put_contents($directory.'/README.md', implode("\n", $lines)."\n");
    }

    private function collectNamespace(array $tokens, int $index): string
    {
        $namespace = '';
        $count = count($tokens);
        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_string($token) && $token === ';') {
                break;
            }

            if (!is_array($token)) {
                continue;
            }

            if (in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                $namespace .= $token[1];
            }
        }

        return $namespace;
    }

    private function nextIdentifier(array $tokens, int $index): ?string
    {
        $count = count($tokens);
        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_STRING) {
                return $token[1];
            }
        }

        return null;
    }

    private function mapClassKind(int $tokenId): string
    {
        return match ($tokenId) {
            T_INTERFACE => 'interface',
            T_TRAIT => 'trait',
            default => 'class',
        };
    }

    private function isPropertyToken(array $tokens, int $index, int $braceDepth, ?int $classDepth): bool
    {
        if ($classDepth === null || $braceDepth !== $classDepth + 1) {
            return false;
        }

        if (!isset($tokens[$index]) || !is_array($tokens[$index]) || $tokens[$index][0] !== T_VARIABLE) {
            return false;
        }

        for ($i = $index - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            if ($token === ';' || $token === '{') {
                return false;
            }

            if (is_array($token) && in_array($token[0], [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_VAR], true)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * @return array{
     *     line:int,
     *     name:string,
     *     params:list<array{name:string,type:string,description:string}>,
     *     returnType:string,
     *     summary:string,
     *     sideEffects:string,
     *     exceptions:list<string>,
     *     isMethod:bool
     * }
     */
    private function parseFunction(array $tokens, int $index, string $namespace, string $className, string $classKind): ?array
    {
        $count = count($tokens);
        $line = is_array($tokens[$index]) ? $tokens[$index][2] : 1;
        $name = null;

        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_STRING) {
                $name = $token[1];
                $index = $i;
                break;
            }

            if ($token === '(') {
                return null;
            }
        }

        if ($name === null) {
            return null;
        }

        while ($index < $count && $tokens[$index] !== '(') {
            $index++;
        }

        if ($index >= $count) {
            return null;
        }

        $params = [];
        $depth = 1;
        $buffer = '';
        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            if ($text === '(') {
                $depth++;
            }
            if ($text === ')') {
                $depth--;
                if ($depth === 0) {
                    if (trim($buffer) !== '') {
                        $params[] = $this->buildParamFromSignature($buffer);
                    }
                    $index = $i;
                    break;
                }
            }

            if ($depth === 1 && $text === ',') {
                $params[] = $this->buildParamFromSignature($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $text;
        }

        $returnType = 'mixed';
        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            if ($text === ':') {
                $returnType = trim($this->collectReturnType($tokens, $i + 1));
                break;
            }
            if ($text === '{' || $text === ';') {
                break;
            }
        }

        $inspection = $this->inspectFunctionBody($tokens, $index);
        if ($returnType === 'mixed') {
            $returnType = $inspection['returnType'];
        }

        $isMethod = $className !== '' && $classKind !== '';

        return [
            'line' => $line,
            'name' => $name,
            'params' => array_values(array_filter($params)),
            'returnType' => $returnType,
            'summary' => $this->buildSummary($name, $className, $isMethod),
            'sideEffects' => $inspection['sideEffects'],
            'exceptions' => $inspection['exceptions'],
            'isMethod' => $isMethod,
        ];
    }

    /**
     * @return array{name:string,type:string,description:string}|null
     */
    private function buildParamFromSignature(string $signature): ?array
    {
        $signature = trim($signature);
        if ($signature === '') {
            return null;
        }

        if (!preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/', $signature, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $name = $matches[1][0];
        $offset = $matches[0][1];
        $typePart = trim(substr($signature, 0, $offset));
        $typePart = str_replace(['public ', 'protected ', 'private ', 'readonly '], '', $typePart);
        $typePart = trim($typePart);

        if ($typePart === '') {
            $typePart = $this->inferTypeFromName($name);
        }

        if (str_contains($signature, '=') && !str_contains($typePart, 'null') && preg_match('/=\s*null\b/', $signature)) {
            $typePart .= '|null';
        }

        return [
            'name' => $name,
            'type' => $typePart,
            'description' => $this->describeParameter($name),
        ];
    }

    private function collectReturnType(array $tokens, int $index): string
    {
        $type = '';
        $count = count($tokens);

        for ($i = $index; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;

            if ($text === '{' || $text === ';') {
                break;
            }

            $type .= $text;
        }

        return trim($type) === '' ? 'mixed' : preg_replace('/\s+/', '', $type);
    }

    /**
     * @return array{returnType:string,sideEffects:string,exceptions:list<string>}
     */
    private function inspectFunctionBody(array $tokens, int $functionIndex): array
    {
        $count = count($tokens);
        $bodyStart = null;
        for ($i = $functionIndex; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            if ($text === '{') {
                $bodyStart = $i;
                break;
            }
            if ($text === ';') {
                return [
                    'returnType' => 'void',
                    'sideEffects' => 'This function delegates behavior without a local body.',
                    'exceptions' => [],
                ];
            }
        }

        if ($bodyStart === null) {
            return [
                'returnType' => 'mixed',
                'sideEffects' => 'This function may interact with external application state.',
                'exceptions' => [],
            ];
        }

        $depth = 1;
        $returnsValue = false;
        $throws = [];
        $sideEffects = [];

        for ($i = $bodyStart + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;

            if ($text === '{') {
                $depth++;
            } elseif ($text === '}') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }

            if (is_array($token) && $token[0] === T_RETURN) {
                $next = $this->nextNonWhitespaceToken($tokens, $i + 1);
                if ($next !== null && $next !== ';') {
                    $returnsValue = true;
                }
            }

            if (is_array($token) && $token[0] === T_THROW) {
                $throws[] = '\\Throwable';
            }

            if (is_array($token) && $token[0] === T_ECHO) {
                $sideEffects['output'] = 'This action may stream a direct HTTP or CLI response.';
            }
        }

        if (empty($sideEffects)) {
            $sideEffects['state'] = 'This routine may read or mutate framework state, superglobals or persistence layers.';
        }

        return [
            'returnType' => $returnsValue ? 'mixed' : 'void',
            'sideEffects' => implode(' ', array_values($sideEffects)),
            'exceptions' => array_values(array_unique($throws)),
        ];
    }

    private function nextNonWhitespaceToken(array $tokens, int $index): array|string|null
    {
        $count = count($tokens);
        for ($i = $index; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $token;
        }

        return null;
    }

    private function hasDocblockAbove(array $lines, int $line): bool
    {
        for ($i = $line - 2; $i >= 0; $i--) {
            $trimmed = trim($lines[$i]);
            if ($trimmed === '') {
                continue;
            }

            return str_ends_with($trimmed, '*/');
        }

        return false;
    }

    private function buildClassDoc(string $namespace, string $className, string $classKind): string
    {
        $subcategory = str_contains($namespace, '\\Controller') ? 'Controller' : 'Library';
        $summary = ucfirst($classKind).' responsible for '.$this->humanize($className).' workflows.';

        return implode("\n", [
            '/**',
            ' * '.$summary,
            ' *',
            ' * This '.$classKind.' belongs to the PmaControl application layer and documents the',
            ' * public surface consumed by controllers, services, static analysis tools and IDEs.',
            ' *',
            ' * @category PmaControl',
            ' * @package App',
            ' * @subpackage '.$subcategory,
            ' * @author '.self::AUTHOR,
            ' * @license '.self::LICENSE,
            ' * @since '.self::SINCE,
            ' * @version '.self::VERSION,
            ' */',
        ]);
    }

    private function buildPropertyDoc(string $propertyName, string $type): string
    {
        return implode("\n", [
            '/**',
            ' * Stores '.$this->describeProperty($propertyName).'.',
            ' *',
            ' * @var '.$type,
            ' * @phpstan-var '.$type,
            ' * @psalm-var '.$type,
            ' */',
        ]);
    }

    /**
     * @param array{
     *     line:int,
     *     name:string,
     *     params:list<array{name:string,type:string,description:string}>,
     *     returnType:string,
     *     summary:string,
     *     sideEffects:string,
     *     exceptions:list<string>,
     *     isMethod:bool
     * } $function
     */
    private function buildFunctionDoc(array $function, string $namespace, string $className, string $classKind): string
    {
        $subcategory = str_contains($namespace, '\\Controller') ? 'Controller' : 'Library';
        $lines = [
            '/**',
            ' * '.$function['summary'],
            ' *',
            ' * '.$function['sideEffects'],
            ' *',
        ];

        foreach ($function['params'] as $param) {
            $lines[] = ' * @param '.$param['type'].' $'.$param['name'].' '.$param['description'];
            $lines[] = ' * @phpstan-param '.$param['type'].' $'.$param['name'];
            $lines[] = ' * @psalm-param '.$param['type'].' $'.$param['name'];
        }

        $lines[] = ' * @return '.$function['returnType'].' Returned value for '.$function['name'].'.';
        $lines[] = ' * @phpstan-return '.$function['returnType'];
        $lines[] = ' * @psalm-return '.$function['returnType'];

        foreach ($function['exceptions'] as $exception) {
            $lines[] = ' * @throws '.$exception.' When the underlying operation fails.';
        }

        if ($function['isMethod'] && $className !== '') {
            $lines[] = ' * @see self::'.$function['name'].'()';
            $lines[] = ' * @example /fr/'.strtolower($className).'/'.$function['name'];
        } else {
            $lines[] = ' * @example '.$function['name'].'(...);';
        }

        $lines[] = ' * @category PmaControl';
        $lines[] = ' * @package App';
        $lines[] = ' * @subpackage '.$subcategory;
        $lines[] = ' * @author '.self::AUTHOR;
        $lines[] = ' * @license '.self::LICENSE;
        $lines[] = ' * @since '.self::SINCE;
        $lines[] = ' * @version '.self::VERSION;
        $lines[] = ' */';

        return implode("\n", $lines);
    }

    private function buildSummary(string $name, string $className, bool $isMethod): string
    {
        $resource = $className !== '' ? $this->humanize($className) : 'application';
        $verb = match (true) {
            str_starts_with($name, 'get'), str_starts_with($name, 'list'), str_starts_with($name, 'fetch') => 'Retrieve',
            str_starts_with($name, 'add'), str_starts_with($name, 'create') => 'Create',
            str_starts_with($name, 'update'), str_starts_with($name, 'save') => 'Update',
            str_starts_with($name, 'delete'), str_starts_with($name, 'remove') => 'Delete',
            str_starts_with($name, 'toggle') => 'Toggle',
            $name === 'index', $name === 'main' => 'Render',
            $name === 'before' => 'Prepare',
            default => 'Handle',
        };

        $target = $isMethod ? $resource.' state through `'.$name.'`.' : '`'.$name.'`.';

        return $verb.' '.$target;
    }

    private function inferPropertyType(array $tokens, int $index): string
    {
        $count = count($tokens);
        for ($i = $index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            $text = is_array($token) ? $token[1] : $token;
            if ($text === '=') {
                $next = $this->nextNonWhitespaceToken($tokens, $i + 1);
                if (is_array($next)) {
                    return match ($next[0]) {
                        T_LNUMBER => 'int',
                        T_DNUMBER => 'float',
                        T_CONSTANT_ENCAPSED_STRING => 'string',
                        T_ARRAY => 'array<int|string,mixed>',
                        T_STRING => in_array(strtolower($next[1]), ['true', 'false'], true) ? 'bool' : 'mixed',
                        default => 'mixed',
                    };
                }

                return $next === '[' ? 'array<int|string,mixed>' : 'mixed';
            }

            if ($text === ';') {
                break;
            }
        }

        return 'mixed';
    }

    private function inferTypeFromName(string $name): string
    {
        return match (true) {
            $name === 'param', $name === 'params' => 'array<int,mixed>',
            str_starts_with($name, 'id_'), str_starts_with($name, 'nb'), str_starts_with($name, 'count') => 'int',
            str_contains($name, 'list'), str_contains($name, 'data'), str_contains($name, 'rows') => 'array<int|string,mixed>',
            str_starts_with($name, 'is_'), str_starts_with($name, 'has_') => 'bool',
            default => 'mixed',
        };
    }

    private function describeParameter(string $name): string
    {
        return match ($name) {
            'param', 'params' => 'Route parameters forwarded by the router.',
            default => 'Input value for `'.$name.'`.',
        };
    }

    private function describeProperty(string $name): string
    {
        return '`$'.$name.'` for '.$this->humanize($name);
    }

    private function humanize(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?? $value;
        $value = str_replace(['_', '-'], ' ', $value);

        return strtolower(trim($value));
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace($this->baseDir, '', $path), '/');
    }
}

$generator = new ControllerLibraryDocGenerator(['App/Controller', 'App/Library']);
$generator->run();
