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

class DependencyTreeGenerator extends Controller {

    private $files = [];
    private $tree = [];

    public function init($directory) {
        $this->loadFiles($directory);
    }

    private function loadFiles($directory) {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $this->files[] = $file->getPathname();
            }
        }
    }

    private function parseMethodCalls($className, $methodName) {
        foreach ($this->files as $file) {
            $content = file_get_contents($file);

            if (preg_match("/class\\s+{$className}\\b.*?function\\s+{$methodName}\\b/s", $content)) {
                return $this->extractMethodCalls($content, $methodName);
            }
        }

        return [];
    }

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

    public function printTree() {
        echo '<pre>' . print_r($this->generateTree(...func_get_args()), true) . '</pre>';
    }

    public function show($param)
    {
        echo '<pre>' . print_r($this->generateTree("/srv/www/pmacontrol/App/Controller", "run"), true) . '</pre>';
        
    }

}