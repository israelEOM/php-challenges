<?php

namespace App\Http\Controllers;

use App\Services\BirdService;

class BirdController extends Controller
{
    protected $birdService;

    public function __construct(BirdService $birdService)
    {
        $this->birdService = $birdService;
    }

    public function processBirds()
    {
        $inputFilePath = storage_path('app/choco-billy/input.txt');
        $outputFilePath = storage_path('app/choco-billy/output.txt');

        try {
            $message = $this->birdService->processBirds($inputFilePath, $outputFilePath);
            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
