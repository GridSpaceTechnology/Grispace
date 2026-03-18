<?php

namespace App\Enums;

enum OrganizationalType: string
{
    case Startup = 'startup';
    case SmallBusiness = 'small_business';
    case MidSizeCompany = 'mid_size_company';
    case LargeCorporation = 'large_corporation';
    case NonProfit = 'non_profit';
    case Government = 'government';
    case Agency = 'agency';
    case Consulting = 'consulting';
    case Freelance = 'freelance';
}
