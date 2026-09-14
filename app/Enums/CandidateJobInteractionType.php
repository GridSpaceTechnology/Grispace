<?php

namespace App\Enums;

enum CandidateJobInteractionType: string
{
    case View = 'view';
    case Save = 'save';
    case Dismiss = 'dismiss';
}
