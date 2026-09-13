<?php

namespace App\Services;

class FaqExtractor
{
    /**
     * Extract question/answer pairs from the `.ex-card` blocks of a FAQ
     * section (`<h2 id="faq">`) in article body HTML.
     *
     * @return list<array{question: string, answer: string}>
     */
    public function extract(string $html): array
    {
        if (! preg_match('/<h2[^>]*id="faq"[^>]*>.*?<\/h2>(.*?)(<h2|$)/is', $html, $section)) {
            return [];
        }

        preg_match_all(
            '/<div[^>]*class="[^"]*ex-card[^"]*"[^>]*>\s*<b[^>]*>(.*?)<\/b>\s*<div[^>]*>(.*?)<\/div>/is',
            $section[1],
            $matches,
            PREG_SET_ORDER,
        );

        $pairs = [];

        foreach ($matches as $match) {
            $question = $this->clean($match[1]);
            $answer = $this->clean($match[2]);

            if ($question !== '' && $answer !== '') {
                $pairs[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $pairs;
    }

    private function clean(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
