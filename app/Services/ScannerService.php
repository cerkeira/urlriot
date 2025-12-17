<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class ScannerService
{
   public function scan(string $url): array
{
    $results = [
        'Google Safe Browsing' => $this->normalizeGoogle($this->checkGoogle($url)),
        'VirusTotal'          => $this->normalizeVirusTotal($this->checkVirusTotal($url)),
        'urlDNA'              => $this->normalizeUrlDna($this->checkDna($url)),
    ];

    $finalRating = $this->computeSafenessRating($results);

    return [
        'rating' => $finalRating,
        'providers' => $results,
    ];
}

// api requests
    private function checkGoogle(string $url): array
    {
        $apiKey = env('GOOGLE_KEY');

        if (!$apiKey) {
            return ['error' => 'Google Safe Browsing integration is temporarily disabled'];
        }

        try {
            $payload = [
            "client" => [
                "clientId" => "urlRIOT",
            ],
            "threatInfo" => [
                "threatTypes"      => ["MALWARE", "SOCIAL_ENGINEERING"],
                "platformTypes"    => ["WINDOWS"],
                "threatEntryTypes" => ["URL"],
                "threatEntries"    => [
                    ["url" => $url]
                ],
            ],
        ];

        $response = Http::timeout(10)
        ->post(
            "https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$apiKey}",
            $payload
        )
        ->throw();

        return $response->json() ?? [];
        } catch  (RequestException $e) {
            return ['error' => 'Google Safe Browsing failed'];
        } catch (ConnectionException $e) {
    return ['error' => 'Service timeout'];
}

    }

    private function checkVirusTotal(string $url): array
    {
        $apiKey = env('VIRUSTOTAL_KEY');

        if (!$apiKey) {
            return ['error' => 'Virus Total integration is temporarily disabled'];
        }

        try {
            $response = Http::timeout(10)
        ->get("https://www.virustotal.com/vtapi/v2/url/report?apikey={$apiKey}&resource={$url}&scan=1")
        ->throw();
            return $response->json() ?? [];
        } catch (RequestException $e) {
            return ['error' => 'Virus Total failed'];
        } catch (ConnectionException $e) {
    return ['error' => 'Service timeout'];
}
    }
    
     private function checkDna(string $url): array
    {
        $apiKey = env('DNA_KEY');

        if (!$apiKey) {
            return ['error' => 'urlDNA integration is temporarily disabled'];
        }

        try {
            $payload = [
            "url"=> $url
        ];

        $response =  Http::timeout(10)
        ->withHeaders([
            'Authorization' => "Bearer $apiKey"
        ])->post(
            "https://api.urldna.io/fast-check",
            $payload
        )
        ->throw();

        return $response->json() ?? [];
        } catch  (RequestException $e) {
            return ['error' => 'urlDNA Browsing failed'];
        } catch (ConnectionException $e) {
    return ['error' => 'Service timeout'];
}

    }
// normalization of results
    private function normalizeGoogle(array $response): array
{
    if (!empty($response['matches'])) {
        return [
            'safe' => false,
            'confidence' => 95,
            'raw' => $response,
        ];
    }

    if (!empty($response) && !isset($response['matches'])) {
        return [
            'safe' => null,
            'confidence' => 0,
            'raw' => $response,
        ];
    }

    return [
        'safe' => true,
        'confidence' => 95,
        'raw' => $response,
    ];
}

private function normalizeVirusTotal(array $response): array
{
    if (!isset($response['positives']) || $response['positives'] == 1) {
        return [
            'safe' => null,
            'confidence' => 0,
            'raw' => $response,
        ];
    }

    if ($response['positives'] > 1) {
        return [
            'safe' => false,
            'confidence' => min(100, $response['positives'] * 10),
            'raw' => $response,
        ];
    }

    return [
        'safe' => true,
        'confidence' => 90,
        'raw' => $response,
    ];
}

private function normalizeUrlDna(array $response): array
{
    if (!isset($response['status'])) {
        return [
            'safe' => null,
            'confidence' => 0,
            'raw' => $response,
        ];
    }

    if (($response['malicious_score'] ?? 0) > 0) {
        return [
            'safe' => false,
            'confidence' => 80,
            'raw' => $response,
        ];
    }

    return match ($response['status']) {
        'SAFE' => [
            'safe' => true,
            'confidence' => 85,
            'raw' => $response,
        ],
        'UNRATED' => [
            'safe' => null,
            'confidence' => 30,
            'raw' => $response,
        ],
        default => [
            'safe' => null,
            'confidence' => 0,
            'raw' => $response,
        ],
    };
}
// weight of each provider for final rating
private array $providerWeights = [
    'Google Safe Browsing' => 3,  // very strong signal
    'VirusTotal'          => 2,  // aggregate engine
    'urlDNA'              => 3,  // heuristic
];
// scoring system
private function verdictToScore(?bool $safe): int
{
    return match ($safe) {
        true  => 1,   // positive signal
        false => -2,  // negative signal (stronger)
        null  => 0,   // unknown
    };
}

private function mapScoreToRating(int $score, int $maxScore): array
{
    // normalize score to range [-1, 1]
    $normalized = $maxScore > 0 ? $score / ($maxScore * 2) : 0;
// translate score to rating
    return match (true) {
        $normalized <= -0.6 => ['rating' => 1, 'label' => 'Very unsafe'],
        $normalized <= -0.2 => ['rating' => 2, 'label' => 'Unsafe'],
        $normalized <  0.2  => ['rating' => 3, 'label' => 'Uncertain'],
        $normalized <  0.6  => ['rating' => 4, 'label' => 'Mostly safe'],
        default             => ['rating' => 5, 'label' => 'Safe'],
    };
}
// final result computation
private function computeSafenessRating(array $results): array
{
    $score = 0;
    $maxScore = 0;

    foreach ($results as $provider => $result) {
        $weight = $this->providerWeights[$provider] ?? 1;
        $verdictScore = $this->verdictToScore($result['safe'] ?? null);

        $score += $verdictScore * $weight;
        $maxScore += $weight;
    }

    return $this->mapScoreToRating($score, $maxScore);
}





}

