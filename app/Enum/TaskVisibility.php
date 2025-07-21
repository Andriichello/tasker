<?php

namespace App\Enum;

enum TaskVisibility: string
{
    case Public = 'public';
    case Private = 'private';
}
