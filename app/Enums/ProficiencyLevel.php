<?php

namespace App\Enums;

enum ProficiencyLevel: int
{
    case Beginner = 1;
    case Elementary = 2;
    case Intermediate = 3;
    case UpperIntermediate = 4;
    case Advanced = 5;
    case Expert = 6;
}
