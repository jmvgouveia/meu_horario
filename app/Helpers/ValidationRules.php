<?php

namespace App\Helpers;

class ValidationRules
{
    public const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*\(\)\[\]\{\}:;_\-\.\,\\\\\/\|~`]).+$/';
}