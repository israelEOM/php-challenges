<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BirdService;

class ProcessBirdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birds:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process bird weights from input file and generate output file';

    /**
     * Execute the console command.
     */
    public function handle(BirdService $birdService)
    {
        $inputFilePath = storage_path('app/choco-billy/input.txt');
        $outputFilePath = storage_path('app/choco-billy/output.txt');

        try {
            $message = $birdService->processBirds($inputFilePath, $outputFilePath);
            $this->info($message);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
