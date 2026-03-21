<?php
namespace AGTI\PagSeguro\Infrastructure\Service\Mail;

class Mail
{
    public static function send($idLang, $template, $subject, $vars, $to, $toName, $path=null)
    {
        $r = \Mail::send(
            $idLang,
            $template,
            $subject,
            $vars,
            $to,
            $toName,
            null,
            null,
            null,
            null,
            $path
        );
    }
}