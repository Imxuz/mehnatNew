<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;

class CaptchaController extends Controller
{
    public function getCaptcha()
    {
        return response()->json(Captcha::create('default', true)); // 'true' parametrib API formatini bildiradi
    }
}
