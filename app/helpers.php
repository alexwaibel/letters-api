<?php

use Carbon\Carbon;

function api_response($status, $message, $data) {
  $response = [];

  $response['date'] = Carbon::now()->timestamp;

  $response['status'] = $status;
  $response['message'] = $message;
  $response['data'] = $data;

  return $response;
}

?>
