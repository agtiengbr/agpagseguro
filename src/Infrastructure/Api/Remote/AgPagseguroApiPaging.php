<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote;

class AgPagseguroApiPaging
{
    
    /**
     * @var int
     */
    private $elements;
    
    /**
     * @var int
     */
    private $totalPages;
    
    /**
     * @var int
     */
    private $page;
    
    /**
     * @var int
     */
    private $totalElements;

    /**
     * Get the value of elements
     *
     * @return  int
     */ 
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Set the value of elements
     *
     * @param  int  $elements
     *
     * @return  self
     */ 
    public function setElements(int $elements)
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * Get the value of totalPages
     *
     * @return  int
     */ 
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Set the value of totalPages
     *
     * @param  int  $totalPages
     *
     * @return  self
     */ 
    public function setTotalPages(int $totalPages)
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    /**
     * Get the value of page
     *
     * @return  int
     */ 
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @param  int  $page
     *
     * @return  self
     */ 
    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of totalElements
     *
     * @return  int
     */ 
    public function getTotalElements()
    {
        return $this->totalElements;
    }

    /**
     * Set the value of totalElements
     *
     * @param  int  $totalElements
     *
     * @return  self
     */ 
    public function setTotalElements(int $totalElements)
    {
        $this->totalElements = $totalElements;

        return $this;
    }
}