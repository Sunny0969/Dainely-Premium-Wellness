<?php

namespace App\Services;

use App\Support\ProductLandingLang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resolves product FAQs for the storefront locale.
 * Prefer real locale CMS/lang copy; auto-translate English leftovers.
 */
class FaqLocalizationService
{
    public function __construct(
        protected ContentTranslationService $translator
    ) {}

    /**
     * @param  Collection<int, object>  $cmsLocaleFaqs
     * @param  Collection<int, object>  $cmsEnFaqs
     * @return Collection<int, object{question:string,answer:string}>
     */
    public function resolveForProduct(
        string $handle,
        string $locale,
        Collection $cmsLocaleFaqs,
        Collection $cmsEnFaqs = new Collection
    ): Collection {
        $locale = in_array($locale, ['en', 'fr', 'de'], true) ? $locale : 'en';

        $normalizedLocale = $this->normalize($cmsLocaleFaqs);
        $enCms = $this->normalize($cmsEnFaqs);
        $langLocale = collect(ProductLandingLang::defaultFaqsForHandle($handle, $locale));
        $langEn = collect(ProductLandingLang::defaultFaqsForHandle($handle, 'en'));
        $langEnNormalized = $this->normalize($langEn);
        $langLocaleNormalized = $this->normalize($langLocale);

        if ($locale === 'en') {
            return $enCms->isNotEmpty() ? $enCms : $langEnNormalized;
        }

        // Locale CMS that is actually translated → use as-is.
        if ($normalizedLocale->isNotEmpty()
            && ! $this->looksLikeEnglishCopy($normalizedLocale, $enCms->isNotEmpty() ? $enCms : $langEnNormalized)
            && ! $this->appearsMostlyEnglish($normalizedLocale)) {
            return $normalizedLocale;
        }

        // Curated lang-file FAQs (real FR/DE, not English duplicates).
        if ($langLocaleNormalized->isNotEmpty()
            && ! $this->looksLikeEnglishCopy($langLocale, $langEn)
            && ! $this->appearsMostlyEnglish($langLocaleNormalized)) {
            // Prefer lang when CMS locale was missing/English and admin hasn't customized EN set.
            if ($enCms->isEmpty()
                || ($enCms->count() === $langEnNormalized->count() && $this->sameQuestions($enCms, $langEnNormalized))) {
                return $langLocaleNormalized;
            }
        }

        // Translate from the best English source (CMS EN → locale CMS if English → lang EN).
        $source = $enCms->isNotEmpty()
            ? $enCms
            : ($normalizedLocale->isNotEmpty() ? $normalizedLocale : $langEnNormalized);

        if ($source->isEmpty()) {
            return $langLocaleNormalized;
        }

        return $this->translateCached($handle, $locale, $source);
    }

    /**
     * @param  Collection<int, object{question:string,answer:string}>  $a
     * @param  Collection<int, object{question:string,answer:string}>  $b
     */
    protected function sameQuestions(Collection $a, Collection $b): bool
    {
        if ($a->count() !== $b->count()) {
            return false;
        }

        foreach ($a->values() as $i => $row) {
            $other = $b->values()->get($i);
            if (! $other) {
                return false;
            }
            if (mb_strtolower($row->question) !== mb_strtolower($other->question)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, object|array>  $rows
     * @return Collection<int, object{question:string,answer:string}>
     */
    protected function normalize(Collection $rows): Collection
    {
        return $rows->map(function ($row) {
            $question = trim((string) (is_object($row) ? ($row->question ?? '') : ($row['question'] ?? '')));
            $answer = trim((string) (is_object($row) ? ($row->answer ?? '') : ($row['answer'] ?? '')));

            return (object) [
                'question' => $question,
                'answer'   => $answer,
            ];
        })->filter(fn ($f) => $f->question !== '' && $f->answer !== '')->values();
    }

    /**
     * @param  Collection<int, array{question?:string,answer?:string}|object>  $localeRows
     * @param  Collection<int, array{question?:string,answer?:string}|object>  $enRows
     */
    protected function looksLikeEnglishCopy(Collection $localeRows, Collection $enRows): bool
    {
        if ($enRows->isEmpty() || $localeRows->count() !== $enRows->count()) {
            return false;
        }

        $localeQs = $localeRows->map(fn ($r) => mb_strtolower(trim((string) (is_object($r) ? ($r->question ?? '') : ($r['question'] ?? '')))))->all();
        $enQs = $enRows->map(fn ($r) => mb_strtolower(trim((string) (is_object($r) ? ($r->question ?? '') : ($r['question'] ?? '')))))->all();

        return $localeQs === $enQs;
    }

    /**
     * Heuristic: FR/DE FAQ rows that are still written in English.
     *
     * @param  Collection<int, object{question:string,answer:string}>  $rows
     */
    protected function appearsMostlyEnglish(Collection $rows): bool
    {
        if ($rows->isEmpty()) {
            return false;
        }

        $englishHits = 0;
        foreach ($rows as $row) {
            $sample = mb_strtolower($row->question.' '.$row->answer);
            if (preg_match('/\b(how|what|when|where|can i|will the|should i|the|and|with|your|brace|wear)\b/u', $sample)) {
                $englishHits++;
            }
        }

        return $englishHits >= (int) ceil($rows->count() * 0.6);
    }

    /**
     * @param  Collection<int, object{question:string,answer:string}>  $source
     * @return Collection<int, object{question:string,answer:string}>
     */
    protected function translateCached(string $handle, string $locale, Collection $source): Collection
    {
        $fingerprint = md5($source->map(fn ($f) => $f->question.'|'.$f->answer)->implode('||'));
        $cacheKey = "storefront.faqs.auto.v2.{$handle}.{$locale}.{$fingerprint}";

        /** @var list<array{question:string,answer:string}>|null $cached */
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            $normalized = $this->normalize(collect($cached));
            // Ignore previously cached English failures.
            if (! $this->appearsMostlyEnglish($normalized)) {
                return $normalized;
            }
            Cache::forget($cacheKey);
        }

        $translated = [];
        $failed = 0;
        foreach ($source as $index => $faq) {
            try {
                $q = $this->translator->translateContent($faq->question, 'en', $locale);
                $a = $this->translator->translateContent($faq->answer, 'en', $locale);
                $translated[] = [
                    'question' => $q,
                    'answer'   => $a,
                ];
            } catch (Throwable $e) {
                $failed++;
                Log::warning('FAQ auto-translate failed', [
                    'handle' => $handle,
                    'locale' => $locale,
                    'index'  => $index,
                    'error'  => $e->getMessage(),
                ]);
                $translated[] = [
                    'question' => $faq->question,
                    'answer'   => $faq->answer,
                ];
            }
        }

        // Only cache successful translations (not English fallbacks).
        if ($translated !== [] && $failed === 0) {
            Cache::put($cacheKey, $translated, now()->addDays(30));
        }

        return $this->normalize(collect($translated));
    }
}
