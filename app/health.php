<?php

declare(strict_types=1);

function gamon_health_payload(): array
{
    return [
        'ok' => true,
        'service' => 'gamon',
        'time' => gmdate('c'),
    ];
}
