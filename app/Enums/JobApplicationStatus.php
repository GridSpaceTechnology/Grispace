<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case Applied = 'applied';
    case Viewed = 'viewed';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
