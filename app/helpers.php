<?php

use Carbon\Carbon;

function api_response($code, $status, $message, $data) {
  $response = [];

  $response['date'] = Carbon::now()->timestamp;

  $response['status'] = $status;
  $response['message'] = $message;
  $response['data'] = $data;

  return response($response, $code)
         ->header('Content-Type', 'application/json');
}

?>
