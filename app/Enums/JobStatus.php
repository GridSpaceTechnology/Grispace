<?php

namespace App\Enums;

enum JobStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paused = 'paused';
    case Closed = 'closed';
    case Filled = 'filled';
}
