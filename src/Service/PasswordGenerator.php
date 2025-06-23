<?php

namespace App\Service;

class PasswordGenerator
{
    public  function generateDefaultPassword($length = 12) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        $characterLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $characterLength - 1)];
        }

        return $password;
    }

}