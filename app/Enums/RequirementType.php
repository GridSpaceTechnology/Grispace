<?php

namespace App\Enums;

enum RequirementType: string
{
    case Skill = 'skill';
    case Experience = 'experience';
    case Education = 'education';
    case Certification = 'certification';
    case Language = 'language';
    case Personality = 'personality';
    case WorkPreference = 'work_preference';
    case Salary = 'salary';
    case Location = 'location';
}
