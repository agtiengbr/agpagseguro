<?php

class AdminAgPagSeguroRequestController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap        = true;
        $this->table            = 'agpagseguro_request';
        $this->className        = 'AgPagSeguroRequest';
        $this->identifier       = 'id_agpagseguro_request';
        $this->list_no_link     = true;
        $this->_defaultOrderBy  = 'id_agpagseguro_request';
        $this->_defaultOrderWay = 'DESC';


        parent::__construct();

        $this->fields_list = [
            'id_agpagseguro_request' => [
                'title' => 'ID',
                'align' => 'center',
                'type' => 'int',
                'class' => 'fixed-width-xs',
            ],
            'http_code' => [
                'title' => 'Código HTTP',
                'type' => 'int',
                'class' => 'fixed-width-md'
            ],
            'method' => [
                'title' => 'Método',
                'type' => 'text',
                'class' => 'fixed-width-md'
            ],
            'endpoint' => [
                'title' => 'URL',
                'type' => 'text'
            ],
            'date_add' => [
                'title' => 'Data',
                'type' => 'datetime'
            ]
        ];

        $this->actions = ['view'];
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


    public function initContent()
    {
        parent::initContent();

        if (Tools::getIsSet('view' . $this->table)) {
            $request = $this->loadObject();

            $html  = $this->content;

            //contéudo geral da ação VER
            $tpl = $this->createTemplate('view.tpl');
            $tpl->assign(['obj' => $request]);
            $html .= $tpl->fetch();

            $this->content = $html;
            $this->context->smarty->assign(['content' => $html]);

            return;
        }
    }
}