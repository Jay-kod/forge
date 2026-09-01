<?php

declare(strict_types=1);

namespace App\Modules\Projects\Enums;

enum ProjectType: string
{
    case NEW_PRODUCT = 'NEW_PRODUCT';
    case EXISTING_PRODUCT = 'EXISTING_PRODUCT';
    case BUSINESS_GROWTH = 'BUSINESS_GROWTH';
    case DIGITAL_TRANSFORMATION = 'DIGITAL_TRANSFORMATION';
    case PROCESS_AUTOMATION = 'PROCESS_AUTOMATION';
    case WEBSITE_IMPROVEMENT = 'WEBSITE_IMPROVEMENT';
    case SOFTWARE_REBUILD = 'SOFTWARE_REBUILD';
    case SOFTWARE_OPTIMIZATION = 'SOFTWARE_OPTIMIZATION';
    case MARKET_VALIDATION = 'MARKET_VALIDATION';
    case BUSINESS_VALIDATION = 'BUSINESS_VALIDATION';
    case TECHNICAL_AUDIT = 'TECHNICAL_AUDIT';
    case MARKET_EXPANSION = 'MARKET_EXPANSION';
    case STRATEGIC_PLANNING = 'STRATEGIC_PLANNING';
    case UNDEFINED = 'UNDEFINED';

    public function label(): string
    {
        return match ($this) {
            self::NEW_PRODUCT => 'New Product Idea',
            self::EXISTING_PRODUCT => 'Existing Product',
            self::BUSINESS_GROWTH => 'Business Growth',
            self::DIGITAL_TRANSFORMATION => 'Digital Transformation',
            self::PROCESS_AUTOMATION => 'Process Automation',
            self::WEBSITE_IMPROVEMENT => 'Website Improvement',
            self::SOFTWARE_REBUILD => 'Software Rebuild',
            self::SOFTWARE_OPTIMIZATION => 'Software Optimization',
            self::MARKET_VALIDATION => 'Market Validation',
            self::BUSINESS_VALIDATION => 'Business Validation',
            self::TECHNICAL_AUDIT => 'Technical Audit',
            self::MARKET_EXPANSION => 'Market Expansion',
            self::STRATEGIC_PLANNING => 'Strategic Planning',
            self::UNDEFINED => 'General Inquiry',
        };
    }
}
