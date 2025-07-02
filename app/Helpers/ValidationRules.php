<?php

namespace App\Helpers;

class ValidationRules
{
    public const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*\(\)\[\]\{\}:;_\-\.\,\\\\\/\|~`]).+$/';

    public const PASSWORD_HELPER_MSG = 'Deve conter pelo menos 1 letra maiúscula, 1 número e 1 símbolo especial (! @ # $ %, etc.)';
}