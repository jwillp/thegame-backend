<?php 

namespace AppBundle\Pagination;

/**
 * PaginationCollection
 * Represents a collection of paginated items
 */
class PaginatedCollection
{
    private $items;  
    private $total;
    private $count;

    private $perPage;
    private $currentPage;
    private $previousPage;
    private $nextPage;
    private $nbPages;

    private $links = array();

    function __construct($items, $total, $perPage, $currentPage, $previousPage, $nextPage, $nbPages) {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);

        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->previousPage = $previousPage;
        $this->nextPage = $nextPage;
        $this->nbPages = $nbPages;
    }

    public function addLink($ref, $url) {
        $this->links[$ref] = $url;
    }

    public function getItems() {
        return $this->items;
    }

    /**
    * Filter collection
    *
    * Filters an the collection using a user function.
    * if the user function returns true, the value is kept
    * in the collection, otherwise it is removed
    */ 
    public function filter($func){
        $this->items = array_filter($this->items, $func);
    }
}   