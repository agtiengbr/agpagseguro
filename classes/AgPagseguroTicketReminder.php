<?php

class AgpagseguroTicketReminder extends AgObjectModel
{
    public static $definition = [
        'table'   => 'agpagseguro_ticket_reminder',
        'primary' => 'id_agpagseguro_ticket_reminder',
        'multilang' => false,
        'fields'  => [
            'id_agpagseguro_ticket_reminder' => ['type' => self::TYPE_INT,'validate' => 'isInt'],
            'id_agpagseguro_transaction' => ['type' => self::TYPE_INT, 'db_type' => 'integer'],
            'scheduled_date' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            'sent_date' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            'canceled' => ['type' => self::TYPE_BOOL, 'db_type' => 'boolean'],
        ]
    ];

    public $id_agpagseguro_ticket_reminder;
    public $id_agpagseguro_transaction;
    public $scheduled_date;
    public $sent_date;
    public $canceled;
}