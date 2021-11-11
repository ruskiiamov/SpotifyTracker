<?php

namespace App\Services;

use Exception;

class Report
{
    /**
     * Report fields container
     *
     * @var array
     */
    private $report = ['error_messages' => []];

    /**
     * Report fields initialization
     *
     * @param ...$fields
     */
    public function __construct(...$fields)
    {
        foreach ($fields as $field) {
            $this->report[$field] = 0;
        }
    }

    /**
     * Report counter increment
     *
     * @param $name
     * @param $arguments
     * @throws Exception
     */
    public function __call($name, $arguments): void
    {
        if (isset($this->report[$name])) {
            $this->report[$name]++;
        } else {
            throw new Exception("{$name} - not valid report field");
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function setValue($name, $value): void
    {
        if (isset($this->report[$name])) {
            $this->report[$name] = $value;
        } else {
            throw new Exception("{$name} - not valid report field");
        }
    }

    /**
     * Set new error message
     *
     * @param string $message
     */
    public function setErrorMessage(string $message): void
    {
        $this->report['error_messages'][] = $message;
    }

    /**
     * Get final report
     *
     * @return array
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
