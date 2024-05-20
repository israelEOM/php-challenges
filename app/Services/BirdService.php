<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class BirdService
{
    public function processBirds($inputFilePath, $outputFilePath)
    {
        if (!File::exists($inputFilePath)) {
            throw new \Exception('Input file not found');
        }

        $input = File::get($inputFilePath);
        $lines = explode("\n", trim($input));

        $numCases = (int)$lines[0]; // Get the number of cases
        array_shift($lines); // Remove the number of cases line

        $output = [];

        for ($i = 0; $i < $numCases; $i++) {
            $weights = array_map('intval', explode(',', trim($lines[$i * 2])));
            $requiredWeight = (int)trim($lines[$i * 2 + 1]);

            $result = $this->minBirds($weights, $requiredWeight);

            if ($result == "Impossible") {
                $output[] = "$requiredWeight: Impossible";
            } else {
                $output[] = count($result) . ':' . implode(',', $result);
            }
        }

        // Write the output to a file
        File::put($outputFilePath, implode("\n", $output));

        return 'Results have been written to output.txt';
    }
    private function minBirds($weights, $requiredWeight)
    {
        // Sort weights in descending order for the greedy approach
        rsort($weights);

        $result = [];
        $currentWeight = 0;

        $this->backtrack($weights, $requiredWeight, $currentWeight, $result);

        return empty($result) ? "Impossible" : $result;
    }

    private function backtrack($weights, $requiredWeight, &$currentWeight, &$result, $currentIndex = 0, $currentPath = [])
    {
        if ($currentWeight == $requiredWeight) {
            // If the exact weight is met, save the result
            $result = $currentPath;
            return true;
        }

        if ($currentWeight > $requiredWeight) {
            // If the current weight exceeds the required weight, backtrack
            return false;
        }

        for ($i = $currentIndex; $i < count($weights); $i++) {
            $currentPath[] = $weights[$i];
            $currentWeight += $weights[$i];

            if ($this->backtrack($weights, $requiredWeight, $currentWeight, $result, $i, $currentPath)) {
                return true;
            }

            // Backtrack: remove the last added weight
            $currentWeight -= $weights[$i];
            array_pop($currentPath);
        }

        return false;
    }
}
