<?php

namespace App\Support;

class CodexPromptEnvelopeFormatter
{
    private const CANONICAL_PROMPT_ID_LABEL = 'CODEX PROMPT ID:';

    public function formatPromptIdLine(string $promptId): string
    {
        return self::CANONICAL_PROMPT_ID_LABEL . ' ' . trim($promptId);
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function formatSummaryBlock(string $position, string $promptId, array $summary): string
    {
        return implode("\n", [
            sprintf('Codex Summary (%s):', $position),
            $this->formatPromptIdLine($promptId),
            '- Мета:',
            '  ' . (string) ($summary['goal'] ?? ''),
            '- Що саме зробити:',
            '  ' . (string) ($summary['work'] ?? ''),
            '- Ключові обмеження / адаптації:',
            '  ' . (string) ($summary['constraints'] ?? ''),
            '- Підсумковий результат:',
            '  ' . (string) ($summary['result'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function wrapPrompt(string $promptId, array $summary, string $body): string
    {
        $promptIdLine = $this->formatPromptIdLine($promptId);
        $trimmedBody = trim($body);

        $sections = [
            $promptIdLine,
            '',
            $this->formatSummaryBlock('Top', $promptId, $summary),
        ];

        if ($trimmedBody !== '') {
            $sections[] = '';
            $sections[] = $trimmedBody;
        }

        $sections[] = '';
        $sections[] = $this->formatSummaryBlock('Bottom', $promptId, $summary);
        $sections[] = '';
        $sections[] = $promptIdLine;

        return implode("\n", $sections);
    }
}
