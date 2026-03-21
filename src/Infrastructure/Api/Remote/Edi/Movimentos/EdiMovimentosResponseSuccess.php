<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Edi\Movimentos;

use AGTI\PagSeguro\Entity\AgPagseguroMovement;
use AGTI\PagSeguro\Infrastructure\Api\Remote\AgPagseguroApiPaging;

class EdiMovimentosResponseSuccess
{
    /**
     * @var AgPagseguroMovement[]
     **/
    private $detalhes;

    /**
     * @var AgPagseguroApiPaging
     */
    private $pagination;

    /**
     * Get the value of pagination
     *
     * @return  AgPagseguroApiPaging
     */ 
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Set the value of pagination
     *
     * @param  AgPagseguroApiPaging  $pagination
     *
     * @return  self
     */ 
    public function setPagination(AgPagseguroApiPaging $pagination)
    {
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * Get the value of detalhes
     *
     * @return  AgPagseguroMovement[]
     */ 
    public function getDetalhes()
    {
        return $this->detalhes;
    }

    /**
     * Set the value of detalhes
     *
     * @param  AgPagseguroMovement[]  $detalhes
     *
     * @return  self
     */ 
    public function setDetalhes($detalhes)
    {
        $this->detalhes = $detalhes;

        return $this;
    }
}