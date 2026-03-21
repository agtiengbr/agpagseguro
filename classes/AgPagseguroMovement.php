<?php

class AgPagseguroMovement extends AgObjectModel
{
    public static $definition = [
        'table' => 'ag_pagseguro_movement',
        'primary' => 'id',
        'fields' => [
            'id' => ['type' => self::TYPE_STRING, 'db_type' => 'bigint'],
            "movimento_api_codigo" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "tipo_registro" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "estabelecimento" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "data_inicial_transacao" => ['type' => self::TYPE_DATE, 'db_type' => 'date'],
            "hora_inicial_transacao" => ['type' => self::TYPE_STRING, 'db_type' => 'time'],
            "data_venda_ajuste" => ['type' => self::TYPE_DATE, 'db_type' => 'date'],
            "hora_venda_ajuste" => ['type' => self::TYPE_STRING, 'db_type' => 'time'],
            "tipo_evento" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "tipo_transacao" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "codigo_transacao" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "codigo_venda" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "valor_total_transacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "valor_parcela" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "pagamento_prazo" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "plano" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "parcela" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "quantidade_parcela" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "data_prevista_pagamento" => ['type' => self::TYPE_DATE, 'db_type' => 'date'],
            "taxa_parcela_comprador" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'], #
            "tarifa_boleto_compra" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'], #
            "valor_original_transacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "taxa_parcela_vendedor" => ['type' => self::TYPE_FLOAT, 'db_type' => 'varchar(255)'],
            "taxa_intermediacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "tarifa_intermediacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "tarifa_boleto_vendedor" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "taxa_rep_aplicacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'float'],
            "valor_liquido_transacao" => ['type' => self::TYPE_FLOAT, 'db_type' => 'FLOAT'],
            "status_pagamento" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "meio_pagamento" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "instituicao_financeira" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "canal_entrada" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "leitor" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "meio_captura" => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            "num_logico" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "nsu" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "cartao_bin" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "cartao_holder" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "codigo_autorizacao" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "codigo_cv" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "numero_serie_leitor" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "tx_id" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "taxa_emissor" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'],
            "taxa_adquirente" => ['type' => self::TYPE_STRING, 'db_type' => 'varchar(255)']
        ]
    ];

    public $id;
    public $movimento_api_codigo;
    public $tipo_registro;
    public $estabelecimento;
    public $data_inicial_transacao;
    public $hora_inicial_transacao;
    public $data_venda_ajuste;
    public $hora_venda_ajuste;
    public $tipo_evento;
    public $tipo_transacao;
    public $codigo_transacao;
    public $codigo_venda;
    public $valor_total_transacao;
    public $valor_parcela;
    public $pagamento_prazo;
    public $plano;
    public $parcela;
    public $quantidade_parcela;
    public $data_prevista_pagamento;
    public $taxa_parcela_comprador;
    public $tarifa_boleto_compra;
    public $valor_original_transacao;
    public $taxa_parcela_vendedor;
    public $taxa_intermediacao;
    public $tarifa_intermediacao;
    public $tarifa_boleto_vendedor;
    public $taxa_rep_aplicacao;
    public $valor_liquido_transacao;
    public $status_pagamento;
    public $meio_pagamento;
    public $instituicao_financeira;
    public $canal_entrada;
    public $leitor;
    public $meio_captura;
    public $num_logico;
    public $nsu;
    public $cartao_bin;
    public $cartao_holder;
    public $codigo_autorizacao;
    public $codigo_cv;
    public $numero_serie_leitor;
    public $tx_id;
    public $taxa_emissor;
    public $taxa_adquirente;
}