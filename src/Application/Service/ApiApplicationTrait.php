<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Application\Utils\ValidateApiResponse as UtilsValidateApiResponse;
use AGTI\PagSeguro\Entity\AgpagseguroRequest;
use Doctrine\ORM\EntityManagerInterface;

trait ApiApplicationTrait
{
    protected function postApiRequest(AgpagseguroRequest $r, EntityManagerInterface $manager)
    {
        $manager->persist($r);
        $manager->flush();

        (new UtilsValidateApiResponse)->validate($r);
    }
}