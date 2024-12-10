<?php

namespace App\Helper;

class TokenGenerator {

    public static function generateToken($length = 32) {
        return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
    }

}
