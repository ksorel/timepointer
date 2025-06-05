<?php
// This file sets the headers for the API responses
// It allows cross-origin requests and specifies the content type
class Response {
    public static function send($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit();
    }

    public static function error($message, $status = 400) {
        self::send([
            'status' => 'error',
            'message' => $message
        ], $status);
    }

    public static function success($message, $data = null, $status = 200) {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data) {
            $response['data'] = $data;
        }

        self::send($response, $status);
    }
}
?>