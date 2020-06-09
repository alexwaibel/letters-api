<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Letter;

use Carbon\Carbon;

class PublicController extends Controller
{
    public function letter_html(Request $request, $verify_token) {
      $letter = Letter::where('verify_token', $verify_token)->first();

      if (!$letter) {
        return response([], 404);
      }

      if ($letter->viewed) {
        return response([], 404);
      }

      $letter->viewed = true;
      $letter->save();

      $date = Carbon::parse($letter->created_at)->toFormattedDateString();

      $content = strip_tags($letter->content);

      if ($letter->attached_img_src) {
        $attached_img = $letter->attached_img_src;
        $letter_content = "<!DOCTYPE html><html lang='en' dir='ltr'><head><meta charset='utf-8'><title></title><link href='https://fonts.googleapis.com/css?family=Montserrat&display=swap' rel='stylesheet'></head><body><style>html,body { margin: 0.33in; font-size: 12px; } * {font-family: 'Montserrat', sans-serif;} .date {margin-top: 3in;}</style><p class='date'>$date</p><p class='content'>$content</p><p><img src='$attached_img' style='max-width: 3in; max-height: 4in;'></p></body></html>";
      } else {
        $letter_content = "<!DOCTYPE html><html lang='en' dir='ltr'><head><meta charset='utf-8'><title></title><link href='https://fonts.googleapis.com/css?family=Montserrat&display=swap' rel='stylesheet'></head><body><style>html,body { margin: 0.33in; font-size: 12px; } * {font-family: 'Montserrat', sans-serif;} .date {margin-top: 3in;}</style><p class='date'>$date</p><p class='content'>$content</p></body></html>";
      }

      $letter_content = str_replace("\n", "<br>", $letter_content);

      return $letter_content;
    }
}
