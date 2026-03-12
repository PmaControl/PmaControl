<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction2;
use App\Library\Format;
use App\Library\Debug;
use App\Library\Mysql;
use \Glial\Sgbd\Sgbd;
use App\Library\Chiffrement;

/**
 * Class responsible for dependency tree generator workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class DependencyTreeGenerator extends Controller {

/**
 * Stores `$files` for files.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    private $files = [];
/**
 * Stores `$tree` for tree.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    private $tree = [];

/**
 * Handle dependency tree generator state through `init`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $directory Input value for `directory`.
 * @phpstan-param mixed $directory
 * @psalm-param mixed $directory
 * @return void Returned value for init.
 * @phpstan-return void
 * @psalm-return void
 * @see self::init()
 * @example /fr/dependencytreegenerator/init
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function init($directory) {
        $this->loadFiles($directory);
    }

/**
 * Handle dependency tree generator state through `loadFiles`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $directory Input value for `directory`.
 * @phpstan-param mixed $directory
 * @psalm-param mixed $directory
 * @return void Returned value for loadFiles.
 * @phpstan-return void
 * @psalm-return void
 * @see self::loadFiles()
 * @example /fr/dependencytreegenerator/loadFiles
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function loadFiles($directory) {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->files[] = $file->getPathname();
            }
        }
    }

/**
 * Handle dependency tree generator state through `parseMethodCalls`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $className Input value for `className`.
 * @phpstan-param mixed $className
 * @psalm-param mixed $className
 * @param mixed $methodName Input value for `methodName`.
 * @phpstan-param mixed $methodName
 * @psalm-param mixed $methodName
 * @return mixed Returned value for parseMethodCalls.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::parseMethodCalls()
 * @example /fr/dependencytreegenerator/parseMethodCalls
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function parseMethodCalls($className, $methodName) {
        foreach ($this->files as $file) {
            $content = file_get_contents($file);

            if (preg_match("/class\\s+{$className}\\b.*?function\\s+{$methodName}\\b/s", $content)) {
                return $this->extractMethodCalls($content, $methodName);
            }
        }

        return [];
    }

/**
 * Handle `extractMethodCalls`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $content Input value for `content`.
 * @phpstan-param mixed $content
 * @psalm-param mixed $content
 * @param mixed $currentMethodName Input value for `currentMethodName`.
 * @phpstan-param mixed $currentMethodName
 * @psalm-param mixed $currentMethodName
 * @return mixed Returned value for extractMethodCalls.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example extractMethodCalls(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function extractMethodCalls($content, $currentMethodName) {
        preg_match_all("/\b(?:self|parent|static)::(.*?)\\(/", $content, $matches);

        if (!isset($matches[1])) {
            return [];
        }

        $methods = $matches[1];
        $methodCalls = [];

        foreach ($methods as $methodCall) {
            $pattern = "/function\\s+{$currentMethodName}\\b.*?\\b{$methodCall}\\(/s";
            if (preg_match_all($pattern, $content, $nestedMatches)) {
                foreach ($nestedMatches[0] as $match) {
                    preg_match("/function\\s+(.*?)\\b/", $match, $funcNameMatch);
                    if ($funcNameMatch && isset($funcNameMatch[1])) {
                        $methodName = trim($funcNameMatch[1]);
                        $methodCalls[$methodName][] = $methodCall;
                    }
                }
            }

            // Recursive call to find deeper dependencies
            $methodCalls += $this->extractMethodCalls($content, $methodCall);
        }

        return $methodCalls;
    }

/**
 * Handle `generateTree`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $className Input value for `className`.
 * @phpstan-param mixed $className
 * @psalm-param mixed $className
 * @param mixed $methodName Input value for `methodName`.
 * @phpstan-param mixed $methodName
 * @psalm-param mixed $methodName
 * @return mixed Returned value for generateTree.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example generateTree(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generateTree($className, $methodName) {
        $this->tree = [];
        $methodCalls = $this->parseMethodCalls($className, $methodName);

        foreach ($methodCalls as $calledMethod => $calls) {
            if (!isset($this->tree[$methodName])) {
                $this->tree[$methodName] = [];
            }

            if (!in_array($calledMethod, $this->tree[$methodName])) {
                $this->tree[$methodName][] = [
                    'method' => $calledMethod,
                    'calls'  => $calls
                ];
            }
        }

        return $this->buildTreeRecursive($className, $methodName);
    }

/**
 * Handle `buildTreeRecursive`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $className Input value for `className`.
 * @phpstan-param mixed $className
 * @psalm-param mixed $className
 * @param mixed $methodName Input value for `methodName`.
 * @phpstan-param mixed $methodName
 * @psalm-param mixed $methodName
 * @param & $tree Input value for `tree`.
 * @phpstan-param & $tree
 * @psalm-param & $tree
 * @return mixed Returned value for buildTreeRecursive.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example buildTreeRecursive(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function buildTreeRecursive($className, $methodName, &$tree = []) {
        if (isset($this->tree[$methodName])) {
            foreach ($this->tree[$methodName] as $dependency) {
                if (!in_array($dependency['method'], array_keys($tree))) {
                    $tree[$dependency['method']] = [];
                }
                
                $subTree = $this->buildTreeRecursive($className, $dependency['method']);
                if (!empty($subTree)) {
                    $tree[$dependency['method']] += $subTree;
                }
            }
        }

        return $tree;
    }

/**
 * Handle `printTree`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for printTree.
 * @phpstan-return void
 * @psalm-return void
 * @example printTree(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function printTree() {
        echo '<pre>' . print_r($this->generateTree(...func_get_args()), true) . '</pre>';
    }

/**
 * Handle `show`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @example show(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function show($param)
    {
        echo '<pre>' . print_r($this->generateTree("/srv/www/pmacontrol/App/Controller", "run"), true) . '</pre>';
        
    }

}
