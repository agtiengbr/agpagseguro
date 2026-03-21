<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Boleto
{
    private $dueDate;
    private $instructionLines;
    private $holder;

    /**
     * Get the value of instructionLines
     */ 
    public function getInstructionLines()
    {
        return $this->instructionLines;
    }

    /**
     * Set the value of instructionLines
     *
     * @return  self
     */ 
    public function setInstructionLines(InstructionLines $instructionLines)
    {
        $this->instructionLines = $instructionLines;

        return $this;
    }

    /**
     * Get the value of dueDate
     */ 
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set the value of dueDate
     *
     * @return  self
     */ 
    public function setDueDate(\DateTime $dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }


    /**
     * Get the value of holder
     */ 
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * Set the value of holder
     *
     * @return  self
     */ 
    public function setHolder(PaymentHolder $holder)
    {
        $this->holder = $holder;

        return $this;
    }
}