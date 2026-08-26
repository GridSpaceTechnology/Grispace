<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Component Weights
    |--------------------------------------------------------------------------
    |
    | Relative contribution of each component to the overall 0-100 match
    | score. Values should total 100. Adjust here - never in controllers.
    |
    */

    'weights' => [
        'skills' => 30,
        'role' => 20,
        'experience' => 15,
        'personality' => 15,
        'work_preference' => 8,
        'salary' => 5,
        'education' => 4,
        'availability' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Score Categories
    |--------------------------------------------------------------------------
    |
    | Overall scores are bucketed into human-friendly categories. Candidates
    | below the lowest threshold are described as "Low Match" - never as
    | bad or unsuitable.
    |
    */

    'thresholds' => [
        'excellent' => 90,
        'strong' => 80,
        'good' => 70,
        'potential' => 60,
    ],

    'labels' => [
        'excellent' => 'Excellent Match',
        'strong' => 'Strong Match',
        'good' => 'Good Match',
        'potential' => 'Potential Match',
        'low' => 'Low Match',
    ],

    /*
    |--------------------------------------------------------------------------
    | Missing Data Policy
    |--------------------------------------------------------------------------
    |
    | When either side is missing data for a component (no assessment, no
    | salary expectation, etc.) the component receives this neutral score
    | instead of a punitive zero, and the breakdown explains why.
    |
    */

    'neutral_score' => 75,

    /*
    |--------------------------------------------------------------------------
    | Skills Matching
    |--------------------------------------------------------------------------
    */

    'skills' => [
        // Share of the skills score driven by required vs preferred skills.
        'required_weight' => 0.7,
        'preferred_weight' => 0.3,

        // Bonus for holding preferred skills beyond requirements, capped.
        'preferred_bonus_per_skill' => 5,
        'preferred_bonus_cap' => 15,

        // Small bonus when matched skills exceed the required proficiency.
        'proficiency_bonus_cap' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Matching
    |--------------------------------------------------------------------------
    |
    | Token overlap between the candidate's desired role and the job title /
    | role field. Tokens in the same synonym group are treated as equal so
    | "Backend Developer" matches "Backend Engineer".
    |
    */

    'role' => [
        'synonym_groups' => [
            ['developer', 'engineer', 'programmer', 'coder', 'software'],
            ['designer', 'creative'],
            ['manager', 'lead', 'head'],
            ['analyst', 'analytics'],
            ['marketer', 'marketing'],
            ['administrator', 'admin'],
            ['accountant', 'accounting', 'finance'],
            ['recruiter', 'talent', 'hiring'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Experience Matching
    |--------------------------------------------------------------------------
    */

    'experience' => [
        // Extra years beyond the requirement that still earn full marks.
        'sweet_spot_years' => 4,

        // Mild score reduction applied per year beyond the sweet spot so
        // heavily over-qualified candidates are flagged, not punished.
        'overqualified_decay' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Work Environment Compatibility Matrix
    |--------------------------------------------------------------------------
    |
    | Row = candidate preference, column = job arrangement.
    |
    */

    'work_preference_matrix' => [
        'remote' => ['remote' => 100, 'hybrid' => 80, 'flexible' => 85, 'onsite' => 30],
        'hybrid' => ['remote' => 80, 'hybrid' => 100, 'flexible' => 90, 'onsite' => 60],
        'onsite' => ['remote' => 30, 'hybrid' => 60, 'flexible' => 60, 'onsite' => 100],
        'flexible' => ['remote' => 90, 'hybrid' => 90, 'flexible' => 100, 'onsite' => 70],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temperament / Work Style Compatibility
    |--------------------------------------------------------------------------
    |
    | Candidate assessments use descriptive temperament words while jobs use
    | a four-type enum, so aliases map between the vocabularies first.
    | Matrix row/column use the job enum space: analytical, driver,
    | expressive, amiable.
    |
    */

    'temperament_aliases' => [
        'analytical' => 'analytical',
        'decisive' => 'driver',
        'energetic' => 'expressive',
        'calm' => 'amiable',
        'expressive' => 'expressive',
        'driver' => 'driver',
        'amiable' => 'amiable',
        'balanced' => null,
    ],

    'temperament_matrix' => [
        'analytical' => ['analytical' => 100, 'driver' => 70, 'expressive' => 50, 'amiable' => 55],
        'driver' => ['analytical' => 70, 'driver' => 100, 'expressive' => 55, 'amiable' => 50],
        'expressive' => ['analytical' => 50, 'driver' => 60, 'expressive' => 100, 'amiable' => 80],
        'amiable' => ['analytical' => 55, 'driver' => 45, 'expressive' => 80, 'amiable' => 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Culture Signal Groups
    |--------------------------------------------------------------------------
    |
    | Employer culture fields are free-ish strings; these keyword groups let
    | the engine compare them against candidate work-style categories in a
    | deterministic way. Each group lists matching keywords plus the
    | candidate profile values considered a strong fit.
    |
    */

    'culture' => [

        'pace_groups' => [
            'fast' => [
                'keywords' => ['fast', 'rapid', 'dynamic', 'startup', 'agile'],
                'work_styles' => ['Energetic and Fast-Paced', 'Flexible and Adaptive', 'Takes Initiative and Leads'],
            ],
            'steady' => [
                'keywords' => ['slow', 'steady', 'stable', 'structured', 'methodical', 'corporate'],
                'work_styles' => ['Structured and Methodical', 'Steady and Consistent'],
            ],
            'balanced' => [
                'keywords' => ['balanced', 'moderate', 'mixed'],
                'work_styles' => ['Balanced and Versatile', 'Expressive and Engaging'],
            ],
        ],

        'environment_groups' => [
            'startup' => [
                'keywords' => ['startup', 'dynamic', 'agile', 'fast-paced'],
                'organizational_fits' => ['Startup or Dynamic Environment', 'Adaptable to Various Environments'],
            ],
            'corporate' => [
                'keywords' => ['corporate', 'structured', 'formal', 'established'],
                'organizational_fits' => ['Structured Corporate Environment'],
            ],
            'mission' => [
                'keywords' => ['nonprofit', 'mission', 'purpose', 'social', 'ngo'],
                'organizational_fits' => ['Mission-Driven Organization'],
            ],
            'remote' => [
                'keywords' => ['remote', 'distributed', 'virtual'],
                'organizational_fits' => ['Remote-First Organization', 'Adaptable to Various Environments'],
            ],
        ],

        'independence_groups' => [
            'independent' => [
                'keywords' => ['independent', 'autonomous', 'solo'],
                'collaboration_styles' => ['Independent Contributor', 'Supportive Team Player'],
            ],
            'collaborative' => [
                'keywords' => ['collaborative', 'team', 'group'],
                'collaboration_styles' => ['Highly Collaborative', 'Balanced Collaborator'],
            ],
        ],
    ],
];
