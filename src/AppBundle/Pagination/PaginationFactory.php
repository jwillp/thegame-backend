<?php 

namespace AppBundle\Pagination;

use Doctrine\ORM\QueryBuilder;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

use AppBundle\Pagination\PaginatedCollection;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

/**
 * PaginationFactory
 */
class PaginationFactory
{
    private $router;


    function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    public function createCollection(QueryBuilder $qb, Request $request, $route, array $routeParams = array()) {
  
        // Get Data
        $page = $request->query->get('page', 1);
        $maxPerPage = $request->query->get('per_page', 10);

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);
        $items = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $items[] = $result;
        }
        
        // Paginate
        $previousPage = $pagerfanta->hasPreviousPage() ? $pagerfanta->getPreviousPage() : null;
        $nextPage = $pagerfanta->hasNextPage() ? $pagerfanta->getNextPage() : null;

        $paginatedCollection = new PaginatedCollection(
            $items,                      /* items        */
            $pagerfanta->getNbResults(), /* total        */
            $maxPerPage,                 /* perPage      */
            $page,                       /* currentPage  */
            $previousPage,               /* previousPage */
            $nextPage,                   /* nextPage     */
            $pagerfanta->getNbPages()    /* getNbPages   */
        );
        // Create page routes
        $createLinkUrl = function($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ));
        };
        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));
        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }
        return $paginatedCollection;
    }
}