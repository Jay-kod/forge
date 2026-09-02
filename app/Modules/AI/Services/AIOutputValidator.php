<?php

declare(strict_types=1);

namespace App\Modules\AI\Services;

use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\Exceptions\AIValidationException;

class AIOutputValidator
{
    /**
     * Validate and parse a JSON response from AI.
     *
     * @param string|AIResponse $response
     * @param string[] $requiredFields
     * @param int $maxLength
     * @return array<string, mixed>
     * @throws AIValidationException
     */
    public function validateJson(
        string|AIResponse $response,
        array $requiredFields = [],
        int $maxLength = 100000
    ): array {
        $content = $response instanceof AIResponse ? $response->content : $response;

        if (empty(trim($content))) {
            throw new AIValidationException('AI response content is empty.');
        }

        if (mb_strlen($content) > $maxLength) {
            throw new AIValidationException("AI response exceeded maximum character length of {$maxLength}.");
        }

        // Clean optional Markdown markdown fences like ```json ... ```
        $cleanJson = $this->stripMarkdownFences($content);

        $decoded = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new AIValidationException(
                'Failed to parse AI output as valid JSON: ' . json_last_error_msg(),
                ['raw_content' => $content]
            );
        }

        // Validate presence of required fields
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $decoded) || $decoded[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new AIValidationException(
                'AI JSON response is missing required fields: ' . implode(', ', $missing),
                ['missing_fields' => $missing, 'parsed' => $decoded]
            );
        }

        return $decoded;
    }

    /**
     * Validate plain text or markdown output from AI.
     *
     * @param string|AIResponse $response
     * @param int $minLength
     * @param int $maxLength
     * @return string
     * @throws AIValidationException
     */
    public function validateText(
        string|AIResponse $response,
        int $minLength = 10,
        int $maxLength = 100000
    ): string {
        $content = trim($response instanceof AIResponse ? $response->content : $response);

        $length = mb_strlen($content);

        if ($length < $minLength) {
            throw new AIValidationException("AI response too short ({$length} chars, expected minimum {$minLength}).");
        }

        if ($length > $maxLength) {
            throw new AIValidationException("AI response exceeded maximum character limit ({$length} chars, limit {$maxLength}).");
        }

        return $content;
    }

    /**
     * Strip ```json ... ``` markdown wrappers if present.
     */
    protected function stripMarkdownFences(string $text): string
    {
        $trimmed = trim($text);

        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $trimmed, $matches)) {
            return trim($matches[1]);
        }

        return $trimmed;
    }
}
