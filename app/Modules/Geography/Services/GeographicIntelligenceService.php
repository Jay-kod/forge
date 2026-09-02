<?php

declare(strict_types=1);

namespace App\Modules\Geography\Services;

use App\Modules\Geography\Models\Location;
use App\Modules\Geography\Models\Market;
use App\Modules\Projects\Models\Project;

class GeographicIntelligenceService
{
    /**
     * Known geographic hubs and markets for automated grounding.
     */
    protected array $knownLocations = [
        'lagos' => ['city' => 'Lagos', 'country_name' => 'Nigeria', 'country_code' => 'NGA', 'currency_code' => 'NGN', 'region' => 'West Africa'],
        'nigeria' => ['city' => null, 'country_name' => 'Nigeria', 'country_code' => 'NGA', 'currency_code' => 'NGN', 'region' => 'West Africa'],
        'nairobi' => ['city' => 'Nairobi', 'country_name' => 'Kenya', 'country_code' => 'KEN', 'currency_code' => 'KES', 'region' => 'East Africa'],
        'kenya' => ['city' => null, 'country_name' => 'Kenya', 'country_code' => 'KEN', 'currency_code' => 'KES', 'region' => 'East Africa'],
        'london' => ['city' => 'London', 'country_name' => 'United Kingdom', 'country_code' => 'GBR', 'currency_code' => 'GBP', 'region' => 'Western Europe'],
        'uk' => ['city' => null, 'country_name' => 'United Kingdom', 'country_code' => 'GBR', 'currency_code' => 'GBP', 'region' => 'Western Europe'],
        'berlin' => ['city' => 'Berlin', 'country_name' => 'Germany', 'country_code' => 'DEU', 'currency_code' => 'EUR', 'region' => 'Central Europe'],
        'germany' => ['city' => null, 'country_name' => 'Germany', 'country_code' => 'DEU', 'currency_code' => 'EUR', 'region' => 'Central Europe'],
        'austin' => ['city' => 'Austin', 'country_name' => 'United States', 'country_code' => 'USA', 'currency_code' => 'USD', 'region' => 'North America'],
        'new york' => ['city' => 'New York', 'country_name' => 'United States', 'country_code' => 'USA', 'currency_code' => 'USD', 'region' => 'North America'],
        'tokyo' => ['city' => 'Tokyo', 'country_name' => 'Japan', 'country_code' => 'JPN', 'currency_code' => 'JPY', 'region' => 'East Asia'],
    ];

    /**
     * Detect geographic context from user text, link Location, and initialize Market model.
     */
    public function detectAndInitializeMarket(Project $project, string $text): ?Market
    {
        $normalizedText = mb_strtolower($text);

        $matchedKey = null;
        foreach ($this->knownLocations as $key => $meta) {
            if (preg_match('/\b' . preg_quote($key, '/') . '\b/i', $normalizedText)) {
                $matchedKey = $key;
                break;
            }
        }

        if (!$matchedKey) {
            return null;
        }

        $geo = $this->knownLocations[$matchedKey];

        // 1. Find or create Location
        $location = Location::firstOrCreate(
            ['country_code' => $geo['country_code'], 'city' => $geo['city']],
            [
                'country_name' => $geo['country_name'],
                'region' => $geo['region'],
                'currency_code' => $geo['currency_code'],
                'regulatory_notes' => [
                    'regional_compliance' => "Standard business & data compliance in {$geo['country_name']}",
                    'tax_system' => "Local corporate & VAT structure applicable",
                ],
                'payment_methods' => [
                    'cards' => true,
                    'bank_transfer' => true,
                    'mobile_money' => in_array($geo['country_code'], ['NGA', 'KEN'], true),
                ],
            ]
        );

        // 2. Create or update Market for Project
        $targetGeo = $geo['city'] ? "{$geo['city']}, {$geo['country_name']}" : $geo['country_name'];

        return Market::updateOrCreate(
            ['project_id' => $project->id],
            [
                'name' => "{$project->title} - {$targetGeo} Market",
                'target_geography' => $targetGeo,
                'tam_estimate' => '$1.2B',
                'sam_estimate' => '$240M',
                'som_estimate' => '$15M',
                'key_drivers' => [
                    "Rapid digital adoption in {$targetGeo}",
                    "Growing demand for localized SaaS & service offerings",
                    "Favorable regional mobile penetration rates",
                ],
                'barriers_to_entry' => [
                    "Incumbent distribution channel relationships",
                    "Localized payment gateway settlement integration",
                    "Regional consumer trust hurdles",
                ],
            ]
        );
    }
}
