<?php
namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AgPagseguroMovement
{
    /**
     * @var int
     * 
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;
    
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $movimentoApiCodigo;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $tipoRegistro;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $estabelecimento;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $dataInicialTransacao;
    
    /**
      * @var string
      *
      * @ORM\Column(type="string")
      */
    private $horaInicialTransacao;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(type="date")
     */
    private $dataVendaAjuste;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $horaVendaAjuste;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $tipoEvento;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $tipoTransacao;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $codigoTransacao;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $codigoVenda;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $valorTotalTransacao;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $valorParcela;
    
    /**
      * @var string
      * @ORM\Column()
      */
    private $pagamentoPrazo;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $plano;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $parcela;
    
    /**
      * @var int

      * @ORM\Column(type="integer")
      */
    private $quantidadeParcela;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(type="date")
     */
    private $dataPrevistaPagamento;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $taxaParcelaComprador;
    
    /**
      * @var string
      * @ORM\Column()
      */
    private $tarifaBoletoCompra;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $valorOriginalTransacao;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $taxaParcelaVendedor;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $taxaIntermediacao;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $tarifaIntermediacao;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $tarifaBoletoVendedor;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $taxaRepAplicacao;
    
    /**
     * @var float
     * 
     * @ORM\Column(type="float")
     */
    private $valorLiquidoTransacao;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $statusPagamento;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $meioPagamento;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $instituicaoFinanceira;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $canalEntrada;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $leitor;
    
    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $meioCaptura;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $numLogico;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $nsu;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $cartaoBin;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $cartaoHolder;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $codigoAutorizacao;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $codigoCv;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $numeroSerieLeitor;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $txId;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $taxaEmissor;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $taxaAdquirente;

    /**
     * Get the value of taxaAdquirente
     *
     * @return  string
     */ 
    public function getTaxaAdquirente()
    {
        return $this->taxaAdquirente;
    }

    /**
     * Set the value of taxaAdquirente
     *
     * @param  string  $taxaAdquirente
     *
     * @return  self
     */ 
    public function setTaxaAdquirente(  $taxaAdquirente)
    {
        $this->taxaAdquirente = $taxaAdquirente;

        return $this;
    }

    /**
     * Get the value of taxaEmissor
     *
     * @return  string
     */ 
    public function getTaxaEmissor()
    {
        return $this->taxaEmissor;
    }

    /**
     * Set the value of taxaEmissor
     *
     * @param  string  $taxaEmissor
     *
     * @return  self
     */ 
    public function setTaxaEmissor(  $taxaEmissor)
    {
        $this->taxaEmissor = $taxaEmissor;

        return $this;
    }

    /**
     * Get the value of txId
     *
     * @return  string
     */ 
    public function getTxId()
    {
        return $this->txId;
    }

    /**
     * Set the value of txId
     *
     * @param  string  $txId
     *
     * @return  self
     */ 
    public function setTxId(  $txId)
    {
        $this->txId = $txId;

        return $this;
    }

    /**
     * Get the value of numeroSerieLeitor
     *
     * @return  string
     */ 
    public function getNumeroSerieLeitor()
    {
        return $this->numeroSerieLeitor;
    }

    /**
     * Set the value of numeroSerieLeitor
     *
     * @param  string  $numeroSerieLeitor
     *
     * @return  self
     */ 
    public function setNumeroSerieLeitor(  $numeroSerieLeitor)
    {
        $this->numeroSerieLeitor = $numeroSerieLeitor;

        return $this;
    }

    /**
     * Get the value of codigoCv
     *
     * @return  string
     */ 
    public function getCodigoCv()
    {
        return $this->codigoCv;
    }

    /**
     * Set the value of codigoCv
     *
     * @param  string  $codigoCv
     *
     * @return  self
     */ 
    public function setCodigoCv(  $codigoCv)
    {
        $this->codigoCv = $codigoCv;

        return $this;
    }

    /**
     * Get the value of codigoAutorizacao
     *
     * @return  string
     */ 
    public function getCodigoAutorizacao()
    {
        return $this->codigoAutorizacao;
    }

    /**
     * Set the value of codigoAutorizacao
     *
     * @param  string  $codigoAutorizacao
     *
     * @return  self
     */ 
    public function setCodigoAutorizacao(  $codigoAutorizacao)
    {
        $this->codigoAutorizacao = $codigoAutorizacao;

        return $this;
    }

    /**
     * Get the value of cartaoHolder
     *
     * @return  string
     */ 
    public function getCartaoHolder()
    {
        return $this->cartaoHolder;
    }

    /**
     * Set the value of cartaoHolder
     *
     * @param  string  $cartaoHolder
     *
     * @return  self
     */ 
    public function setCartaoHolder(  $cartaoHolder)
    {
        $this->cartaoHolder = $cartaoHolder;

        return $this;
    }

    /**
     * Get the value of cartaoBin
     *
     * @return  string
     */ 
    public function getCartaoBin()
    {
        return $this->cartaoBin;
    }

    /**
     * Set the value of cartaoBin
     *
     * @param  string  $cartaoBin
     *
     * @return  self
     */ 
    public function setCartaoBin(  $cartaoBin)
    {
        $this->cartaoBin = $cartaoBin;

        return $this;
    }

    /**
     * Get the value of nsu
     *
     * @return  string
     */ 
    public function getNsu()
    {
        return $this->nsu;
    }

    /**
     * Set the value of nsu
     *
     * @param  string  $nsu
     *
     * @return  self
     */ 
    public function setNsu(  $nsu)
    {
        $this->nsu = $nsu;

        return $this;
    }

    /**
     * Get the value of numLogico
     *
     * @return  string
     */ 
    public function getNumLogico()
    {
        return $this->numLogico;
    }

    /**
     * Set the value of numLogico
     *
     * @param  string  $numLogico
     *
     * @return  self
     */ 
    public function setNumLogico(  $numLogico)
    {
        $this->numLogico = $numLogico;

        return $this;
    }

    /**
     * Get the value of meioCaptura
     *
     * @return  int
     */ 
    public function getMeioCaptura()
    {
        return $this->meioCaptura;
    }

    /**
     * Set the value of meioCaptura
     *
     * @param  int  $meioCaptura
     *
     * @return  self
     */ 
    public function setMeioCaptura($meioCaptura)
    {
        $this->meioCaptura = $meioCaptura;

        return $this;
    }

    /**
     * Get the value of leitor
     *
     * @return  string
     */ 
    public function getLeitor()
    {
        return $this->leitor;
    }

    /**
     * Set the value of leitor
     *
     * @param  string  $leitor
     *
     * @return  self
     */ 
    public function setLeitor(  $leitor)
    {
        $this->leitor = $leitor;

        return $this;
    }

    /**
     * Get the value of canalEntrada
     *
     * @return  string
     */ 
    public function getCanalEntrada()
    {
        return $this->canalEntrada;
    }

    /**
     * Set the value of canalEntrada
     *
     * @param  string  $canalEntrada
     *
     * @return  self
     */ 
    public function setCanalEntrada(  $canalEntrada)
    {
        $this->canalEntrada = $canalEntrada;

        return $this;
    }

    /**
     * Get the value of instituicaoFinanceira
     *
     * @return  string
     */ 
    public function getInstituicaoFinanceira()
    {
        return $this->instituicaoFinanceira;
    }

    /**
     * Set the value of instituicaoFinanceira
     *
     * @param  string  $instituicaoFinanceira
     *
     * @return  self
     */ 
    public function setInstituicaoFinanceira(  $instituicaoFinanceira)
    {
        $this->instituicaoFinanceira = $instituicaoFinanceira;

        return $this;
    }

    /**
     * Get the value of meioPagamento
     *
     * @return  int
     */ 
    public function getMeioPagamento()
    {
        return $this->meioPagamento;
    }

    /**
     * Set the value of meioPagamento
     *
     * @param  int  $meioPagamento
     *
     * @return  self
     */ 
    public function setMeioPagamento ($meioPagamento)
    {
        $this->meioPagamento = $meioPagamento;

        return $this;
    }

    /**
     * Get the value of statusPagamento
     *
     * @return  int
     */ 
    public function getStatusPagamento()
    {
        return $this->statusPagamento;
    }

    /**
     * Set the value of statusPagamento
     *
     * @param  int  $statusPagamento
     *
     * @return  self
     */ 
    public function setStatusPagamento($statusPagamento)
    {
        $this->statusPagamento = $statusPagamento;

        return $this;
    }

    /**
     * Get the value of valorLiquidoTransacao
     *
     * @return  float
     */ 
    public function getValorLiquidoTransacao()
    {
        return $this->valorLiquidoTransacao;
    }

    /**
     * Set the value of valorLiquidoTransacao
     *
     * @param  float  $valorLiquidoTransacao
     *
     * @return  self
     */ 
    public function setValorLiquidoTransacao( $valorLiquidoTransacao)
    {
        $this->valorLiquidoTransacao = $valorLiquidoTransacao;

        return $this;
    }

    /**
     * Get the value of taxaRepAplicacao
     *
     * @return  float
     */ 
    public function getTaxaRepAplicacao()
    {
        return $this->taxaRepAplicacao;
    }

    /**
     * Set the value of taxaRepAplicacao
     *
     * @param  float  $taxaRepAplicacao
     *
     * @return  self
     */ 
    public function setTaxaRepAplicacao( $taxaRepAplicacao)
    {
        $this->taxaRepAplicacao = $taxaRepAplicacao;

        return $this;
    }

    /**
     * Get the value of tarifaBoletoVendedor
     *
     * @return  float
     */ 
    public function getTarifaBoletoVendedor()
    {
        return $this->tarifaBoletoVendedor;
    }

    /**
     * Set the value of tarifaBoletoVendedor
     *
     * @param  float  $tarifaBoletoVendedor
     *
     * @return  self
     */ 
    public function setTarifaBoletoVendedor( $tarifaBoletoVendedor)
    {
        $this->tarifaBoletoVendedor = $tarifaBoletoVendedor;

        return $this;
    }

    /**
     * Get the value of tarifaIntermediacao
     *
     * @return  float
     */ 
    public function getTarifaIntermediacao()
    {
        return $this->tarifaIntermediacao;
    }

    /**
     * Set the value of tarifaIntermediacao
     *
     * @param  float  $tarifaIntermediacao
     *
     * @return  self
     */ 
    public function setTarifaIntermediacao( $tarifaIntermediacao)
    {
        $this->tarifaIntermediacao = $tarifaIntermediacao;

        return $this;
    }

    /**
     * Get the value of taxaIntermediacao
     *
     * @return  float
     */ 
    public function getTaxaIntermediacao()
    {
        return $this->taxaIntermediacao;
    }

    /**
     * Set the value of taxaIntermediacao
     *
     * @param  float  $taxaIntermediacao
     *
     * @return  self
     */ 
    public function setTaxaIntermediacao( $taxaIntermediacao)
    {
        $this->taxaIntermediacao = $taxaIntermediacao;

        return $this;
    }

    /**
     * Get the value of taxaParcelaVendedor
     *
     * @return  float
     */ 
    public function getTaxaParcelaVendedor()
    {
        return $this->taxaParcelaVendedor;
    }

    /**
     * Set the value of taxaParcelaVendedor
     *
     * @param  float  $taxaParcelaVendedor
     *
     * @return  self
     */ 
    public function setTaxaParcelaVendedor( $taxaParcelaVendedor)
    {
        $this->taxaParcelaVendedor = $taxaParcelaVendedor;

        return $this;
    }

    /**
     * Get the value of valorOriginalTransacao
     *
     * @return  float
     */ 
    public function getValorOriginalTransacao()
    {
        return $this->valorOriginalTransacao;
    }

    /**
     * Set the value of valorOriginalTransacao
     *
     * @param  float  $valorOriginalTransacao
     *
     * @return  self
     */ 
    public function setValorOriginalTransacao( $valorOriginalTransacao)
    {
        $this->valorOriginalTransacao = $valorOriginalTransacao;

        return $this;
    }

    /**
     * Get the value of tarifaBoletoCompra
     *
     * @return  string
     */ 
    public function getTarifaBoletoCompra()
    {
        return $this->tarifaBoletoCompra;
    }

    /**
     * Set the value of tarifaBoletoCompra
     *
     * @param  string  $tarifaBoletoCompra
     *
     * @return  self
     */ 
    public function setTarifaBoletoCompra(  $tarifaBoletoCompra)
    {
        $this->tarifaBoletoCompra = $tarifaBoletoCompra;

        return $this;
    }

    /**
     * Get the value of taxaParcelaComprador
     *
     * @return  float
     */ 
    public function getTaxaParcelaComprador()
    {
        return $this->taxaParcelaComprador;
    }

    /**
     * Set the value of taxaParcelaComprador
     *
     * @param  float  $taxaParcelaComprador
     *
     * @return  self
     */ 
    public function setTaxaParcelaComprador( $taxaParcelaComprador)
    {
        $this->taxaParcelaComprador = $taxaParcelaComprador;

        return $this;
    }

    /**
     * Get the value of dataPrevistaPagamento
     *
     * @return  \DateTime
     */ 
    public function getDataPrevistaPagamento()
    {
        return $this->dataPrevistaPagamento;
    }

    /**
     * Set the value of dataPrevistaPagamento
     *
     * @param  \DateTime  $dataPrevistaPagamento
     *
     * @return  self
     */ 
    public function setDataPrevistaPagamento(\DateTime $dataPrevistaPagamento)
    {
        $this->dataPrevistaPagamento = $dataPrevistaPagamento;

        return $this;
    }

    /**
     * Get the value of quantidadeParcela
     */ 
    public function getQuantidadeParcela()
    {
        return $this->quantidadeParcela;
    }

    /**
     * Set the value of quantidadeParcela
     *
     * @return  self
     */ 
    public function setQuantidadeParcela($quantidadeParcela)
    {
        $this->quantidadeParcela = $quantidadeParcela;

        return $this;
    }

    /**
     * Get the value of parcela
     *
     * @return  int
     */ 
    public function getParcela()
    {
        return $this->parcela;
    }

    /**
     * Set the value of parcela
     *
     * @param  int  $parcela
     *
     * @return  self
     */ 
    public function setParcela($parcela)
    {
        $this->parcela = $parcela;

        return $this;
    }

    /**
     * Get the value of plano
     *
     * @return  int
     */ 
    public function getPlano()
    {
        return $this->plano;
    }

    /**
     * Set the value of plano
     *
     * @param  int  $plano
     *
     * @return  self
     */ 
    public function setPlano($plano)
    {
        $this->plano = $plano;

        return $this;
    }

    /**
     * Get the value of pagamentoPrazo
     *
     * @return  string
     */ 
    public function getPagamentoPrazo()
    {
        return $this->pagamentoPrazo;
    }

    /**
     * Set the value of pagamentoPrazo
     *
     * @param  string  $pagamentoPrazo
     *
     * @return  self
     */ 
    public function setPagamentoPrazo(  $pagamentoPrazo)
    {
        $this->pagamentoPrazo = $pagamentoPrazo;

        return $this;
    }

    /**
     * Get the value of valorParcela
     *
     * @return  float
     */ 
    public function getValorParcela()
    {
        return $this->valorParcela;
    }

    /**
     * Set the value of valorParcela
     *
     * @param  float  $valorParcela
     *
     * @return  self
     */ 
    public function setValorParcela( $valorParcela)
    {
        $this->valorParcela = $valorParcela;

        return $this;
    }

    /**
     * Get the value of valorTotalTransacao
     *
     * @return  float
     */ 
    public function getValorTotalTransacao()
    {
        return $this->valorTotalTransacao;
    }

    /**
     * Set the value of valorTotalTransacao
     *
     * @param  float  $valorTotalTransacao
     *
     * @return  self
     */ 
    public function setValorTotalTransacao( $valorTotalTransacao)
    {
        $this->valorTotalTransacao = $valorTotalTransacao;

        return $this;
    }

    /**
     * Get the value of codigoVenda
     *
     * @return  string
     */ 
    public function getCodigoVenda()
    {
        return $this->codigoVenda;
    }

    /**
     * Set the value of codigoVenda
     *
     * @param  string  $codigoVenda
     *
     * @return  self
     */ 
    public function setCodigoVenda(  $codigoVenda)
    {
        $this->codigoVenda = $codigoVenda;

        return $this;
    }

    /**
     * Get the value of codigoTransacao
     *
     * @return  string
     */ 
    public function getCodigoTransacao()
    {
        return $this->codigoTransacao;
    }

    /**
     * Set the value of codigoTransacao
     *
     * @param  string  $codigoTransacao
     *
     * @return  self
     */ 
    public function setCodigoTransacao(  $codigoTransacao)
    {
        $this->codigoTransacao = $codigoTransacao;

        return $this;
    }

    /**
     * Get the value of tipoTransacao
     *
     * @return  int
     */ 
    public function getTipoTransacao()
    {
        return $this->tipoTransacao;
    }

    /**
     * Set the value of tipoTransacao
     *
     * @param  int  $tipoTransacao
     *
     * @return  self
     */ 
    public function setTipoTransacao($tipoTransacao)
    {
        $this->tipoTransacao = $tipoTransacao;

        return $this;
    }

    /**
     * Get the value of tipoEvento
     *
     * @return  int
     */ 
    public function getTipoEvento()
    {
        return $this->tipoEvento;
    }

    /**
     * Set the value of tipoEvento
     *
     * @param  int  $tipoEvento
     *
     * @return  self
     */ 
    public function setTipoEvento($tipoEvento)
    {
        $this->tipoEvento = $tipoEvento;

        return $this;
    }

    /**
     * Get the value of horaVendaAjuste
     *
     * @return  string
     */ 
    public function getHoraVendaAjuste()
    {
        return $this->horaVendaAjuste;
    }

    /**
     * Set the value of horaVendaAjuste
     *
     * @param  string  $horaVendaAjuste
     *
     * @return  self
     */ 
    public function setHoraVendaAjuste(  $horaVendaAjuste)
    {
        $this->horaVendaAjuste = $horaVendaAjuste;

        return $this;
    }

    /**
     * Get the value of dataVendaAjuste
     *
     * @return  \DateTime
     */ 
    public function getDataVendaAjuste()
    {
        return $this->dataVendaAjuste;
    }

    /**
     * Set the value of dataVendaAjuste
     *
     * @param  \DateTime  $dataVendaAjuste
     *
     * @return  self
     */ 
    public function setDataVendaAjuste(\DateTime $dataVendaAjuste)
    {
        $this->dataVendaAjuste = $dataVendaAjuste;

        return $this;
    }

    /**
     * Get the value of horaInicialTransacao
     *
     * @return  string
     */ 
    public function getHoraInicialTransacao()
    {
        return $this->horaInicialTransacao;
    }

    /**
     * Set the value of horaInicialTransacao
     *
     * @param  string  $horaInicialTransacao
     *
     * @return  self
     */ 
    public function setHoraInicialTransacao(  $horaInicialTransacao)
    {
        $this->horaInicialTransacao = $horaInicialTransacao;

        return $this;
    }

    /**
     * Get the value of dataInicialTransacao
     *
     * @return  \DateTime
     */ 
    public function getDataInicialTransacao()
    {
        return $this->dataInicialTransacao;
    }

    /**
     * Set the value of dataInicialTransacao
     *
     * @param  \DateTime  $dataInicialTransacao
     *
     * @return  self
     */ 
    public function setDataInicialTransacao(\DateTime $dataInicialTransacao)
    {
        $this->dataInicialTransacao = $dataInicialTransacao;

        return $this;
    }

    /**
     * Get the value of estabelecimento
     *
     * @return  string
     */ 
    public function getEstabelecimento()
    {
        return $this->estabelecimento;
    }

    /**
     * Set the value of estabelecimento
     *
     * @param  string  $estabelecimento
     *
     * @return  self
     */ 
    public function setEstabelecimento(  $estabelecimento)
    {
        $this->estabelecimento = $estabelecimento;

        return $this;
    }

    /**
     * Get the value of tipoRegistro
     *
     * @return  int
     */ 
    public function getTipoRegistro()
    {
        return $this->tipoRegistro;
    }

    /**
     * Set the value of tipoRegistro
     *
     * @param  string  $tipoRegistro
     *
     * @return  self
     */ 
    public function setTipoRegistro($tipoRegistro)
    {
        $this->tipoRegistro = $tipoRegistro;

        return $this;
    }

    /**
     * Get the value of movimentoApiCodigo
     *
     * @return  string
     */ 
    public function getMovimentoApiCodigo()
    {
        return $this->movimentoApiCodigo;
    }

    /**
     * Set the value of movimentoApiCodigo
     *
     * @param  string  $movimentoApiCodigo
     *
     * @return  self
     */ 
    public function setMovimentoApiCodigo(  $movimentoApiCodigo)
    {
        $this->movimentoApiCodigo = $movimentoApiCodigo;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return  int
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param  int  $id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}