<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZipController extends Controller
{
    public function query_zips($zip=null) {
      if ($zip) {
        $z = Zip::where('zip', $zip)->first();
      } else {
        $z = Zip::paginate(20);
      }

      return api_response("OK", "", $z);
    }
}
