<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Jobs\ProcessChocoboDNA;
use Illuminate\Support\Facades\Storage;

class ChocoboService
{
    private const MAX_ADDITIONS = 64;
    private const MAX_BYTE = 255;

    public function processChocoboDNA($inputFilePath, $outputFilePath)
    {
        if (!File::exists($inputFilePath)) {
            throw new \Exception('Input file not found');
        }

        $input = File::get($inputFilePath);
        $lines = explode("\n", trim($input));

        $this->processLines($lines);

        return 'Results are being processed. The output will be written to ' . $outputFilePath;
    }
    private function processLines(array $lines): void
    {
        $currentFile = '';
        $additionIndex = 0;
        $temporaryFiles = []; // Store currentData in files

        foreach ($lines as $line) {
            if (preg_match('/[a-zA-Z]/', $line)) {
                // New currentFile detected
                $parts = explode(' ', $line);
                $currentFile = $parts[0];
                $additionIndex = 0;
                $temporaryFiles[$currentFile] = $this->createTemporaryFile();

                // Dispatch the Job in queue
                ProcessChocoboDNA::dispatch($temporaryFiles[$currentFile], 0, '', $currentFile, $additionIndex);

                $additionIndex++;
            } else {
                // Process data lines
                list($position, $byte) = explode(' ', trim($line));
                $position = (int)$position;
                $byte = chr((int)$byte);

                // Validate position and byte
                if ($position < 0 || strlen($byte) !== 1 || ord($byte) < 0 || ord($byte) > self::MAX_BYTE) {
                    throw new \Exception('Invalid position or byte value');
                }

                // Check if the addition index exceeds the limit
                if ($additionIndex >= self::MAX_ADDITIONS) {
                    throw new \Exception('Exceeded maximum number of additions (64)');
                }

                // Dispatch the Job in queue
                ProcessChocoboDNA::dispatch($temporaryFiles[$currentFile], $position, $byte, $currentFile, $additionIndex);

                $additionIndex++;
            }
        }
    }
    private function createTemporaryFile(): string
    {
        return tempnam(sys_get_temp_dir(), 'chocobo_');
    }
}
