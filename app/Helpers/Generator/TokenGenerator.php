<?php

namespace App\Helpers\Generator;

use Exception;

class TokenGenerator
{

    public static function generateToken($length = 32, $prefix = '', $chars = null)
    {
        $length = $length - strlen($prefix);
        if ($length < 0)
        {
            throw new Exception("Prefix is too long", 1);
        }
        $token = "";

        if ($chars === null)
        {
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            $number_of_chars = 62;
        } else
        {
            $number_of_chars = strlen($chars);
        }

        for ($i = 0; $i < $length; $i++)
        {
            $token .= $chars[self::random(0, $number_of_chars)];
        }

        return $prefix . $token;
    }

    public static function generateMd5Token($len = 32, $md5 = true)
    {
        # Seed random number generator
        # Only needed for PHP versions prior to 4.2
        mt_srand((double) microtime() * 1000000);
        # Array of characters, adjust as desired
        $chars = array(
            'Q', '@', '8', 'y', '%', '^', '5', 'Z', '(', 'G', '_', 'O', '`',
            'S', '-', 'N', '<', 'D', '{', '}', '[', ']', 'h', ';', 'W', '.',
            '/', '|', ':', '1', 'E', 'L', '4', '&', '6', '7', '#', '9', 'a',
            'A', 'b', 'B', '~', 'C', 'd', '>', 'e', '2', 'f', 'P', 'g', ')',
            '?', 'H', 'i', 'X', 'U', 'J', 'k', 'r', 'l', '3', 't', 'M', 'n',
            '=', 'o', '+', 'p', 'F', 'q', '!', 'K', 'R', 's', 'c', 'm', 'T',
            'v', 'j', 'u', 'V', 'w', ',', 'x', 'I', '$', 'Y', 'z', '*'
        );
        # Array indice friendly number of chars;
        $numChars = count($chars) - 1;
        $token = '';
        # Create random token at the specified length
        for ($i = 0; $i < $len; $i++)
        {
            $token .= $chars[mt_rand(0, $numChars)];
        }
        # Should token be run through md5?
        if ($md5)
        {
            # Number of 32 char chunks
            $chunks = ceil(strlen($token) / 32);
            $md5token = '';
            # Run each chunk through md5
            for ($i = 1; $i <= $chunks; $i++)
                $md5token .= md5(substr($token, $i * 32 - 32, 32));
            # Trim the token
            $token = substr($md5token, 0, $len);
        } return $token;
    }

    protected static function random($min, $max)
    {
        $range = $max - $min;

        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }

}
