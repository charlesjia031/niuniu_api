<?php
if (!function_exists("echoJson")) {
    function echoJson($status_code, $message, $data, $validation, $trace_code)
    {
        $response['status_code'] = $status_code;
        $response['message'] = $message;
        $response['data'] = $data;
        $response['validation'] = $validation;
        $response['trace_code'] = $trace_code;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit(0);
    }
}
?>