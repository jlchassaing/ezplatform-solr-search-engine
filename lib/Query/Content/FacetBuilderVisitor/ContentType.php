<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\Query\Content\FacetBuilderVisitor;

use EzSystems\EzPlatformSolrSearchEngine\Query\FacetBuilderVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the ContentType facet builder.
 */
class ContentType extends FacetBuilderVisitor
{

    /**
     * @var ContentTypeService
     */
    protected $contentTypeService;


    /**
     * @var LanguageService
     */
    private $languageService;

    public function __construct(ContentTypeService $contentTypeService, LanguageService $languageService )
    {
        $this->contentTypeService = $contentTypeService;
        $this->languageService = $languageService;
    }

    /**
     * CHeck if visitor is applicable to current facet result.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canMap($field)
    {
        return $field === 'type_id';
    }

    /**
     * Map Solr facet result back to facet objects.
     *
     * @param string $field
     * @param array $data
     *
     * @return Facet
     */
    public function map($field, array $data)
    {
        return new Facet\ContentTypeFacet(
            array(
                'name' => 'type',
                'entries' => $this->mapData($data),
            )
        );
    }

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @param FacetBuilder $facetBuilder
     *
     * @return bool
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\ContentTypeFacetBuilder;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param FacetBuilder $facetBuilder;
     *
     * @return string
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        return array(
            'facet.field' => 'type_id',
            'f.type_id.facet.limit' => $facetBuilder->limit,
            'f.type_id.facet.mincount' => $facetBuilder->minCount,
        );
    }

    /**
     * Map Solr return array into a sane hash map.
     *
     * @param array $data
     *
     * @return array
     */
    protected function mapData(array $data)
    {
        $values = array();
        reset($data);
        $currentLanguage = $this->languageService->getDefaultLanguageCode();
        while ($key = current($data)) {
            $name =   $this->contentTypeService->loadContentType($key)->getNames();

            $values[$key] = ['count' => next($data),
                             'name'  => $name[$currentLanguage]];
            next($data);
        }

        return $values;
    }


    /**
     * Return Query Filter for selected Data
     *
     * @param $data
     *
     * @return Criterion\ContentTypeId
     */
    public function getFilterQuery($data)
    {
        return new Criterion\ContentTypeId($data);
    }
}
