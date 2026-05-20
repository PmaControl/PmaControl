<?php

class FakeSsh {
    private $responses = [];
    public $executed = [];

    public function __construct(array $responses) {
        $this->responses = $responses;
    }

    public function exec($cmd) {
        $this->executed[] = $cmd;

        foreach ($this->responses as $pattern => $output) {
            if (preg_match($pattern, $cmd)) {
                return $output;
            }
        }

        return "";
    }

    public function disconnect() {
        return true;
    }
}