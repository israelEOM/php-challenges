<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessChocoboDNA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 120;

    protected $filePath;
    protected $position;
    protected $byte;
    protected $currentFile;
    protected $additionIndex;
    protected $outputFilePath = 'app/adn-chocobos/output.txt';

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $position, string $byte, string $currentFile, int $additionIndex)
    {
        $this->filePath = $filePath;
        $this->position = $position;
        $this->byte = $byte;
        $this->currentFile = $currentFile;
        $this->additionIndex = $additionIndex;
    }

    public function handle()
    {
        $file = fopen($this->filePath, 'c+b');
        if ($file === false) {
            throw new \Exception('Failed to open file');
        }

        try {
            fseek($file, 0, SEEK_END);
            $currentSize = ftell($file);

            if ($this->position > $currentSize) {
                fseek($file, $this->position - 1, SEEK_SET);
                fwrite($file, "\0");
            }

            fseek($file, $this->position, SEEK_SET);
            fwrite($file, $this->byte);
        } finally {
            fclose($file);
        }

        // Generate CRC32 after writing the byte
        $crc32 = hash_file('crc32b', $this->filePath);
        if ($crc32 === false) {
            throw new \Exception('Failed to compute CRC32 for temporary file');
        }

        $output = "$this->currentFile $this->additionIndex: $crc32";
        file_put_contents(storage_path($this->outputFilePath), $output . PHP_EOL, FILE_APPEND);
    }
    public function failed(\Exception $exception)
    {
        Log::error("Job failed for {$this->currentFile} at position {$this->position}: {$exception->getMessage()}");
    }
}
