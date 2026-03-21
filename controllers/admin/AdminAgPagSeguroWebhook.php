<?php

use AGTI\PagSeguro\Enum\AgPagSeguroWebhookStatuses;

class AdminAgPagSeguroWebhookController extends ModuleAdminController
{
    public function __construct()
	{
        $this->bootstrap  = true;
        $this->table      = 'agpagseguro_webhook';
        $this->identifier = 'id_agpagseguro_webhook';
        $this->className  = 'AgPagseguroWebhook';
        $this->noLink = true;
        $this->list_no_link = true;

        $this->_orderBy = 'date_upd';
        $this->_orderWay = 'DESC';

        parent::__construct();

        $this->informations[] = "O processamento automático dos webhooks pode levar de 15 a 20 minutos para ser realizado.";
        $this->informations[] = "Caso precise aplicar os webhooks imediatamente, habilite a opção correspondente nas configurações do módulo.";


        $this->actions = ['enqueue'];

        $this->fields_list = [
            'id_agpagseguro_webhook' => [
                'title' => 'ID',
                'class' => 'center fixed-width-xs'
            ],
            'notification_code' => [
                'title' => 'Código'
            ],
            'status' => [
                'title' => 'Estado',
                'type' => 'select',
                'list' => [
                    0 => 'Na Fila',
                    1 => 'Error',
                    2 => 'Processamento Concluído'
                ],
                'filter_key' => 'a!status'
            ],
            'date_add' => [
                'title' => 'Data de Criação',
                'type' => 'datetime'
			],
			'date_upd' => [
                'title' => 'Última Atualização',
                'type' => 'datetime'
            ]
        ];
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
                $this->_list[$i]['status'] = AgPagSeguroWebhookStatuses::get((int) $this->_list[$i]['status']);
            } catch (InvalidArgumentException $e) {
                $this->_list[$i]['status'] = "Estado {$this->_list[$i]} não tratado.";
            }
        }
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display === 'list' || empty($this->display)) {
            $this->page_header_toolbar_btn['process_next'] = array(
                'href' => $this->context->link->getModuleLink($this->module->name, 'webhook', ['process_next' => 1]),
                'desc' => 'Processar Webhooks',
                'icon' => 'process-icon- icon-check',
                'target' => '_blank'
            );
        }

        $this->page_header_toolbar_btn['configuration'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name,
            'desc' => 'Configurações',
            'icon' => 'process-icon- icon-cogs',
        );

        parent::initPageHeaderToolbar();
    }
    
    public function initContent()
    {
        parent::initContent();
        if (Tools::getIsset("enqueue")) {
            /** @var AgPagSeguroWebhook */
            $obj = $this->loadObject();
            $obj->status = 0;
            $obj->save();

            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
        }
    }




    /********* AÇÕES INDIVIDUAIS *************/
    public function displayEnqueueLink(
        $token,
        $id
    )
    {
        $tpl = $this->createTemplate('helpers/list/enqueue.tpl');
        $tpl->assign([
            'url' => self::$currentIndex . "&token={$this->token}&enqueue&{$this->identifier}={$id}"
        ]);
        return $tpl->fetch();
    }
}