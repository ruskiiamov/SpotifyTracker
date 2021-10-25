<?php

namespace Tests\Unit;

use App\Services\Report;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    private function initializeReport(): Report
    {
        return new Report('first_field', 'second_field');
    }

    public function testInitialization()
    {
        $report = $this->initializeReport();
        $this->assertEquals(0, $report->getReport()['first_field']);
        $this->assertEquals(0, $report->getReport()['second_field']);
    }

    public function testCounter()
    {
        $report = $this->initializeReport();
        $report->first_field();
        $report->first_field();
        $report->second_field();
        $this->assertEquals(2, $report->getReport()['first_field']);
        $this->assertEquals(1, $report->getReport()['second_field']);
    }

    public function testErrorMessage()
    {
        $report = $this->initializeReport();
        $message = 'Test Error Message';
        $report->setErrorMessage($message);
        $this->assertEquals($message, $report->getReport()['error_messages'][0]);
    }

    public function testReport()
    {
        $report = $this->initializeReport();
        $this->assertIsArray($report->getReport());
    }
}
