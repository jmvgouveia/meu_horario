<?php

use Illuminate\Support\Facades\Auth;

function currentUser(): ?\App\Models\User
{
    $user = Auth::user();
    return $user instanceof \App\Models\User ? $user : null;
}

function getCurrentUserID(): int
{
    return currentUser()->id();
}

function isUserSuperAdmin() : bool
{
    return currentUser()->isSuperAdmin();
}