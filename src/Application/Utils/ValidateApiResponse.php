<?php

namespace AGTI\PagSeguro\Application\Utils;

use AGTI\PagSeguro\Application\Exception\ApiErrorResponseException;
use AGTI\PagSeguro\Application\Exception\HttpCodeException;
use AGTI\PagSeguro\Application\Exception\UnauthorizedHttpException;
use AGTI\PagSeguro\Entity\AgpagseguroRequest;

class ValidateApiResponse
{
    public function validate(AgpagseguroRequest $r)
    {
        if ($r->getHttpCode() == 401) {
            throw new UnauthorizedHttpException("A API retornou falha de autenticação.");
        }

        if ($r->getHttpCode() < 200 || $r->getHttpCode() >= 300) {
            throw new HttpCodeException("A API retornou código HTTP de erro.", (int) $r->getHttpCode());
        }

        $data = json_decode($r->getResponse());
        if (@$data->success === 'true') {
            return true;
         }

        if (@$data->success == false && isset($data->error)) {
            $exception = new ApiErrorResponseException("A API retornou mensagens de erro.");
            $exception->addError($data->error);
            throw $exception;
        }

    }
}