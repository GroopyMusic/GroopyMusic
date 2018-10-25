<?php

namespace AppBundle\Services;


class ImageHelper
{
    public static function base64($url)
    {
        try {
            $data = file_get_contents($url);
            $base64 = 'data:image/png;base64,' . base64_encode($data);
        }
        catch(\Throwable $t) {
            $base64 = false;
        }
        return $base64;
    }

}