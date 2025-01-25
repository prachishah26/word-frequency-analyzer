<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SplFileObject;

class WordFrequencyService
{
    public function analyzeText($text, $top = 10, $exclude = [])
    {
        $cacheKey = md5($text . json_encode($exclude) . $top);

        return Cache::remember($cacheKey, 3600, function () use ($text, $top, $exclude) {
            return $this->processText($text, $top, $exclude);
        });
    }

    public function analyzeFile($file, $top = 10, $exclude = [])
    {
        return $this->processLargeFile($file->getRealPath(), $top, $exclude);
    }

    private function processText($text, $top, $exclude)
    {
        $text = Str::lower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        return $this->calculateFrequency($words, $top, $exclude);
    }

    private function processLargeFile($filePath, $top, $exclude)
    {
        $frequencies = [];
        $exclude = array_map('strtolower', $exclude);

        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);

        // Increase memory limit temporarily if needed
        ini_set('memory_limit', '512M'); //set limit as per the requirement

        while (!$file->eof()) {
            $line = Str::lower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $file->fgets()));
            $words = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($words as $word) {
                if (!in_array($word, $exclude) && !empty($word)) {
                    $frequencies[$word] = ($frequencies[$word] ?? 0) + 1;
                }
            }
        }

        return $this->calculateFrequency($frequencies, $top);
    }

    private function calculateFrequency($words, $top, $exclude = [])
    {
        if (is_array($words)) {
            $frequencies = is_string(array_key_first($words)) 
                ? $words 
                : array_count_values(array_filter($words, function($word) use ($exclude) {
                    return !in_array($word, $exclude);
                }));
        } else {
            $frequencies = [];
        }

        arsort($frequencies);
        $topWords = array_slice($frequencies, 0, $top, true);

        return array_map(function($word, $count) {
            return [
                'word' => $word,
                'count' => $count
            ];
        }, array_keys($topWords), $topWords);
    }
}