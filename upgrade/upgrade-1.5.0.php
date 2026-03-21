<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5_0()
{
    
    $id_tab = Tab::getIdFromClassName('AdminAgPagSeguroServices');
    if (!$id_tab) {
        $tab = new Tab();
        $tab->class_name = 'AdminAgPagSeguroServices';
        $tab->id_parent = Tab::getIdFromClassName('AdminParentPayment');
        $tab->active = 0;
        $tab->module = 'agpagseguro';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'vazio';
        }
        $tab->add();   
    }

    $id_tab = Tab::getIdFromClassName('AdminAgPagSeguroTransaction');
    if ($id_tab) {
        $tab = new Tab($id_tab);
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->save();
    }else{
        $tab = new Tab();
        $tab->class_name = 'AdminAgPagSeguroTransaction';
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->module = 'agpagseguro';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'PagSeguro Transações';
        }
        $tab->add();    
    }

    $id_tab = Tab::getIdFromClassName('AdminAgPagSeguroWebhook');
    if ($id_tab) {
        $tab = new Tab($id_tab);
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->save();
    }else{
        $tab = new Tab();
        $tab->class_name = 'AdminAgPagSeguroWebhook';
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->module = 'agpagseguro';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'PagSeguro WebHook';
        }
        $tab->add();    
    }

    $id_tab = Tab::getIdFromClassName('AdminAgPagSeguroRequest');
    if ($id_tab) {
        $tab = new Tab($id_tab);
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->save();
    }else{
        $tab = new Tab();
        $tab->class_name = 'AdminAgPagSeguroRequest';
        $tab->id_parent = Tab::getIdFromClassName('AdminAgPagSeguroServices');
        $tab->active = 1;
        $tab->module = 'agpagseguro';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'PagSeguro Requisições';
        }
        $tab->add();   
    }

    $id_tab = Tab::getIdFromClassName('AdminAgPagSeguroConfig');
    if (!$id_tab) {
        $tab = new Tab();
        $tab->class_name = 'AdminAgPagSeguroConfig';
        $tab->id_parent = Tab::getIdFromClassName('AdminParentPayment');
        $tab->active = 1;
        $tab->module = 'agpagseguro';
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'PagSeguro';
        }
        $tab->add();  
    }
    return true;
  
}
