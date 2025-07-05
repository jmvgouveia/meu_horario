<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class UserHelper
{ 
    public static function currentUser(): ?\App\Models\User
    {
        $user = Auth::user();
        return $user instanceof \App\Models\User ? $user : null;
    }
    
    public static function getCurrentUserID(): int
    {
        return self::currentUser()->id();
    }
    
    public static function isUserSuperAdmin() : bool
    {
        return self::currentUser()->isSuperAdmin();
    }
}