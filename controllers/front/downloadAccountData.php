<?php

use AGTI\PagSeguro\Application\Service\UpdateMovementsPreviousDay;

class agpagsegurodownloadAccountDataModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        
        $s = $this->get(UpdateMovementsPreviousDay::class);
        $s->exec();

        exit();
    }
}