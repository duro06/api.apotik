<?php

namespace App\Helpers\Formating;


class FormatingHelper
{

    public static function genKodeBarang($n, $kode)
    {

        $lbr = strlen($n);
        $hasil = str_pad($n, $lbr + 5, '0', STR_PAD_LEFT);
        return $kode . $hasil;
    }
}
