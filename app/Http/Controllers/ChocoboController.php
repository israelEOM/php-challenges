<?php

namespace App\Http\Controllers;

use App\Services\ChocoboService;

class ChocoboController extends Controller
{
    protected $chocoboService;

    public function __construct(ChocoboService $chocoboService)
    {
        $this->chocoboService = $chocoboService;
    }

    public function processChocoboDNA()
    {
        $inputFilePath = storage_path('app/adn-chocobos/input.txt');
        $outputFilePath = storage_path('app/adn-chocobos/output.txt');

        try {
            $message = $this->chocoboService->processChocoboDNA($inputFilePath, $outputFilePath);
            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
