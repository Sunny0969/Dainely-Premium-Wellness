<?php

namespace App\Contracts;

interface SearchableEntity
{
    public function getTranslatedTitle(?string $locale = null): string;

    public function getPlainTextContent(?string $locale = null): string;

    /** @return string|null comma/space separated keywords */
    public function getSearchKeywords(?string $locale = null): ?string;
}
