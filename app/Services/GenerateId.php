<?php

namespace App\Services;

class GenerateId
{
    public function generateID(): string
    {
        $id = '';
        $randomChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        for ($i = 0; $i < 40; $i++) {
            $id .= $randomChars[rand(0, strlen($randomChars) - 1)];
        }

        return $id;
    }
}
