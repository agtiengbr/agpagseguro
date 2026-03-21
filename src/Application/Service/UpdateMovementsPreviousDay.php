<?php
namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgPagseguroMovement;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Edi\Movimentos\EdiMovimentosResponseSuccess;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Edi\Movimentos\EdiMovimentosService;
use AGTI\PagSeguro\ValueObject\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class UpdateMovementsPreviousDay
{
    use ApiApplicationTrait;
    
    private $apiService;
    private $em;
    private $config;

    public function __construct(EdiMovimentosService $apiService, EntityManagerInterface $em, Configuration $config)
    {
        $this->config = $config;
        $this->em = $em;
        $this->apiService = $apiService;
    }

    public function exec()
    {

        $this->doLoop(1);
        $this->doLoop(2);

        $this->em->flush();
    }

    private function doLoop($tipoMovimento)
    {
        $date = new \DateTime("-1 day");
        
        $page = 1;
        while(1) {
            $r = $this->apiService->send($date, 20, $page, $tipoMovimento);
            $this->postApiRequest($this->apiService->getRequest(), $this->em);
            
            if ($r instanceof EdiMovimentosResponseSuccess) {
                foreach ($r->getDetalhes() as $movement) {
                    //verifica se a entrada já foi armazenada anteriormente
                    $currentEtt = $this->em->getRepository(AgPagseguroMovement::class)->findOneBy(['movimentoApiCodigo' => $movement->getMovimentoApiCodigo()]);
                    if (is_null($currentEtt)) {
                        $this->em->persist($movement);
                    }
                }

                if ($r->getPagination()->getTotalPages() <= $page) {
                    break;
                }
                
                $page++;
            }
        }
       
    }
}