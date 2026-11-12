<?php

declare(strict_types=1);

namespace Tests;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $scenarioNumber, string $description): void
    {
        if ($condition) {
            $this->passed++;
            echo "\033[32m[PASS]\033[0m Test {$scenarioNumber}: {$description}\n";
        } else {
            $this->failed++;
            $this->failures[] = "Test {$scenarioNumber}: {$description}";
            echo "\033[31m[FAIL]\033[0m Test {$scenarioNumber}: {$description}\n";
        }
    }

    public function assertEquals(mixed $expected, mixed $actual, string $scenarioNumber, string $description): void
    {
        $this->assert($expected === $actual, $scenarioNumber, "{$description} (Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ")");
    }

    public function report(): int
    {
        echo "\n" . str_repeat('=', 65) . "\n";
        echo "PRAGATHI AUTOMATED QUALITY ASSURANCE TEST REPORT\n";
        echo str_repeat('=', 65) . "\n";
        echo "Total Tests Executed: " . ($this->passed + $this->failed) . "\n";
        echo "\033[32mPassed: {$this->passed}\033[0m\n";

        if ($this->failed > 0) {
            echo "\033[31mFailed: {$this->failed}\033[0m\n";
            echo "\nFailed Scenarios:\n";
            foreach ($this->failures as $f) {
                echo " - {$f}\n";
            }
            echo str_repeat('=', 65) . "\n";
            return 1;
        }

        echo "\033[32mAll 20 RTIH Mandatory Test Scenarios Passed Successfully (100% PASS)!\033[0m\n";
        echo str_repeat('=', 65) . "\n";
        return 0;
    }
}
