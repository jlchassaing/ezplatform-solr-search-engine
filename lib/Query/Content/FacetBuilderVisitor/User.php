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
use eZ\Publish\Core\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the User facet builder.
 */
class User extends FacetBuilderVisitor
{

    /**
     * @var UserService
     */
    private $userService;

    /**
     * User constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        return $field === 'creator_id';
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
        return new Facet\UserFacet(
            array(
                'name' => 'creator',
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
        return $facetBuilder instanceof FacetBuilder\UserFacetBuilder;
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
            'facet.field' => 'creator_id',
            'f.creator_id.facet.limit' => $facetBuilder->limit,
            'f.creator_id.facet.mincount' => $facetBuilder->minCount,
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
        while ($key = current($data)) {
            $values[$key] = ['count' => next($data),
                             'name'  => $this->userService->loadUser($key)->login];
            next($data);
        }

        return $values;
    }


    /**
     * Return Query Filter for selected Data
     *
     * @param $data
     *
     * @return Criterion\UserMetadata
     */
    public function getFilterQuery($data)
    {
        return new Criterion\UserMetadata(Criterion\UserMetadata::OWNER,Criterion\Operator::IN, $data);
    }
}
