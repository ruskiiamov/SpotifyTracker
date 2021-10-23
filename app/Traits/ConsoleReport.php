<?php

namespace App\Traits;

trait ConsoleReport
{
    private function showReport(array $report)
    {
        foreach ($report as $key => $value) {
            if ($key !== 'error_messages') {
                $newKey = ucfirst(str_replace('_', ' ', $key));
                $this->info("{$newKey}: {$value}");
            }
        }

        if (empty($report['error_messages'])) {
            $this->info('Errors: 0');
        } else {
            $this->error('Errors: ' . count($report['error_messages']));
            foreach ($report['error_messages'] as $error_message) {
                $this->line($error_message);
                $this->newLine();
            }
        }

    }

}
