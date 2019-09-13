<?php

namespace rias\scout;

/**
 * Attributes.
 *
 * @method self searchableAttributes(string[] $attributes)
 * @method self attributesForFaceting(string[] $attributes)
 * @method self unretrievableAttributes(string[] $attributes)
 * @method self attributesToRetrieve(string[] $attributes)
 *
 * Ranking
 * @method self ranking(string[] $ranking)
 * @method self customRanking(string[] $customRanking)
 * @method self replicas(string[] $replicas)
 *
 * Faceting
 * @method self maxValuesPerFacet(int $maxValuesPerFacet)
 * @method self sortFacetValuesBy(string $sortBy)
 *
 * Highlighting / Snippeting
 * @method self attributesToHighlight(string[] $attributes)
 * @method self attributesToSnippet(string[] $attributes)
 * @method self highlightPreTag(string $tag)
 * @method self highlightPostTag(string $tag)
 * @method self snippetEllipsisText(string $text)
 * @method self restrictHighlightAndSnippetArrays(bool $restrict)
 *
 * Pagination
 * @method self hitsPerPage(int $hits)
 * @method self paginationLimitedTo(int $limit)
 *
 * Typos
 * @method self minWordSizefor1Typo(int $minWordSize)
 * @method self minWordSizefor2Typos(int $minWordSize)
 * @method self typoTolerance(bool $tolerance)
 * @method self allowTyposOnNumericTokens(bool $allow)
 * @method self disableTypoToleranceOnAttributes(string[] $attributes)
 * @method self disableTypoToleranceOnWords(string[] $words)
 * @method self separatorsToIndex(string $separators)
 *
 * Languages
 * @method self ignorePlurals(bool|string[] $ignorePlurals)
 * @method self removeStopWords(bool|string[] $removeStopWords)
 * @method self camelCaseAttributes(string[] $attributes)
 * @method self decompoundedAttributes(array[] $attributes)
 * @method self keepDiacriticsOnCharacters(string $diacritics)
 * @method self queryLanguages(string[] $languages)
 *
 * Query rules
 * @method self enableRules(bool $enableRules)
 *
 * Query Strategy
 * @method self queryType(string $queryType)
 * @method self removeWordsIfNoResults(string $removeWords)
 * @method self advancedSyntax(bool $advancedSyntax)
 * @method self optionalWords(string|string[] $optionalWords)
 * @method self disablePrefixOnAttributes(string[] $attributes)
 * @method self disableExactOnAttributes(string[] $attributes)
 * @method self exactOnSingleWordQuery(string $exactOnSingleWord)
 * @method self alternativesAsExact(string[] $alternatives)
 * @method self advancedSyntaxFeatures(string[] $advancedSyntaxFeatures)
 *
 * Performance
 * @method self numericAttributesForFiltering(string[] $attributes)
 * @method self allowCompressionOfIntegerArray(bool $allowCompression)
 *
 * Advanced
 * @method self attributeForDistinct(string $attributes)
 * @method self distinct(int|bool $distinct)
 * @method self replaceSynonymsInHighlight(bool $replaceSynonyms)
 * @method self minProximity(int $minProximity)
 * @method self responseFields(string[] $fields)
 * @method self maxFacetHits(int $maxHits)
 * @method self attributeCriteriaComputedByMinProximity(bool $attributeCriteriaComputedByMinProximity)
 * @method self userData(object $userData)
 */
class IndexSettings
{
    /** @var bool */
    public $forwardToReplicas = true;

    /** @var array */
    public $settings = [];

    public static function create(array $indexSettings = []): self
    {
        $new = new self();

        if (isset($indexSettings['forwardToReplicas'])) {
            $new->forwardToReplicas = (bool) $indexSettings['forwardToReplicas'];
        }

        if (isset($indexSettings['settings'])) {
            $new->settings = $indexSettings['settings'];
        }

        return $new;
    }

    public function forwardToReplicas(bool $forwardToReplicas): self
    {
        $this->forwardToReplicas = $forwardToReplicas;

        return $this;
    }

    public function __call($name, $arguments): self
    {
        $this->settings[$name] = $arguments[0];

        return $this;
    }
}
