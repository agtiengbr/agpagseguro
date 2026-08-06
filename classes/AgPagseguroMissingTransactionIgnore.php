<?php

class AgPagseguroMissingTransactionIgnore extends ObjectModel
{
    public $id_order;
    public $date_add;

    public static $definition = array(
        'table' => 'agpagseguro_missing_transaction_ignore',
        'primary' => 'id_agpagseguro_missing_transaction_ignore',
        'multilang' => false,
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function createDatabase()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'agpagseguro_missing_transaction_ignore` (
                `id_agpagseguro_missing_transaction_ignore` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_order` INT UNSIGNED NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_agpagseguro_missing_transaction_ignore`),
                UNIQUE KEY `agpagseguro_missing_transaction_ignore_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
        );
    }

    public function createMissingColumns()
    {
        return true;
    }

    public function createIndexes()
    {
        return true;
    }

    public static function getAll()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'agpagseguro_missing_transaction_ignore`'
        );

        if (!is_array($rows)) {
            return array();
        }

        return array_map(function ($row) {
            $model = new self();
            $model->hydrate($row);

            return $model;
        }, $rows);
    }

    public static function getByOrderId($idOrder)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'agpagseguro_missing_transaction_ignore`
             WHERE `id_order` = ' . (int) $idOrder
        );

        if (!is_array($row) || empty($row)) {
            return false;
        }

        $model = new self();
        $model->hydrate($row);

        return $model;
    }
}
