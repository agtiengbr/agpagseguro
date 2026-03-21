<?php

use AGTI\PagSeguro\Enum\AgPagSeguroTransactionStatuses;

class AdminAgPagSeguroTransactionController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap  = true;
        $this->table      = 'agpagseguro_transaction';
        $this->identifier = 'id_agpagseguro_transaction';
        $this->className  = 'AgPagseguroTransaction';
        $this->noLink = true;
        $this->list_no_link = true;

        $this->_orderBy = 'date_upd';
        $this->_orderWay = 'DESC';

		parent::__construct();
		$this->informations[] = "O processamento automático dos webhooks pode levar de 15 a 20 minutos para ser realizado.";
		
		$this->_select .= 'o.reference,';
		$this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON a.id_order=o.id_order';

		$this->_select .= 'CONCAT(c.firstname, " ", c.lastname) customer_name';
		$this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'customer c ON c.id_customer=o.id_customer';

		$this->fields_list = [
            'id_agpagseguro_transaction' => [
                'title' => 'ID',
                'class' => 'center fixed-width-xs'
			],
			'reference' => [
				'title' => 'Pedido',
				'class' => 'center fixed-width-xs'
			],
			'customer_name' => [
				'title' => 'Cliente',
			],
			'transaction_code' => [
				'title' => 'Transação'
			],
			'transaction_status' => [
				'title' => 'Estado da Transação',
				'class' => 'fixed-width-sm',
				'type' => 'select',
				'list' => AgPagSeguroTransactionStatuses::getValues(),
				'filter_key' => 'a!transaction_status'
			],
			'date_add' => [
                'title' => 'Data de Criação',
                'type' => 'datetime'
			],
			'date_upd' => [
                'title' => 'Última Atualização',
                'type' => 'datetime'
            ],
			
		];		
	}

	public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['configuration'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name,
            'desc' => 'Configurações',
            'icon' => 'process-icon- icon-cogs',
        );
    }

    public function ajaxProcessCreateTicket()
    {
        $id_order = Tools::getValue('id_order');
        $order = new Order($id_order);
		try {
			$ticket_link = $this->module->createTicketForOrder($order, ['sender_hash' => Tools::getValue('sender_hash')]);
			

			echo Tools::jsonEncode(['ticket_url' => $ticket_link]);
		} catch (Exception $e) {
			echo Tools::jsonEncode([
				'success' => false,
				'error' => $e->getMessage()
			]);
		}

        exit();
	}
	
	public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
		
		foreach ($this->_list as $i=>$row) {
			try {
				$this->_list[$i]['transaction_status'] = AgPagSeguroTransactionStatuses::get((int) $this->_list[$i]['transaction_status']);
			} catch (InvalidArgumentException $e) {
				$this->_list[$i]['transaction_status'] = "Estado {$this->_list[$i]} não tratado.";
			}
		}
	}
}
