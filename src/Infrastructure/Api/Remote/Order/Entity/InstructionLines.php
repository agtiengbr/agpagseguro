<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class InstructionLines
{
    private $line1;
    private $line2;


    /**
     * Get the value of line2
     */ 
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * Set the value of line2
     *
     * @return  self
     */ 
    public function setLine2($line2)
    {
        $this->line2 = $line2;

        return $this;
    }

    /**
     * Get the value of line1
     */ 
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * Set the value of line1
     *
     * @return  self
     */ 
    public function setLine1($line1)
    {
        $this->line1 = $line1;

        return $this;
    }
}