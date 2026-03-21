<div class='panel'>
    <dl class="dl-horizontal">
        <dt>Endpoint</dt>
        <dd>{$obj->endpoint}</dd>

        <dt>Método</dt>
        <dd>{$obj->method}</dd>

        <dt>Headers</dt>
        <dd><pre>{print_r(unserialize($obj->headers), true)}</pre></dd>

        <dt>Body</dt>
        <dd><pre>{print_r(json_decode($obj->body), true)}</pre></dd>

        <dt>Código HTTP</dt>
        <dd>{$obj->http_code}</dd>

        <dt>Resposta</dt>
        <dd><pre>{print_r($obj->response, true)|escape:'html'}</pre></dd>

        <dt>Data</dt>
        <dd>{Tools::displayDate($obj->date_add)}</dd>
    </dl>
</div>