<?php

namespace App\Enums;

enum WorkPreference: string
{
    case Remote = 'remote';
    case Hybrid = 'hybrid';
    case OnSite = 'onsite';
    case Flexible = 'flexible';
}
