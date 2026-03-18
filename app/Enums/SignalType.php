<?php

namespace App\Enums;

enum SignalType: string
{
    case TechnicalSkill = 'technical_skill';
    case SoftSkill = 'soft_skill';
    case ToolProficiency = 'tool_proficiency';
    case Certification = 'certification';
    case Language = 'language';
    case WorkStyle = 'work_style';
    case Value = 'value';
    case Achievement = 'achievement';
    case IndustryExperience = 'industry_experience';
    case ProjectType = 'project_type';
    case TeamRole = 'team_role';
    case LeadershipLevel = 'leadership_level';
}
