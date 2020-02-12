<?php

/**
 * eeJSON
 * @param $message
 * @param $code
 * @param null $data
 * @return false|string
 */
function eeJson($message, $code, $data = null)
{
    $format = [
        'response' => [
            'message' => $message,
            'code'    => $code,
        ],
        'data'     => $data
    ];
    return json_encode($format);
}