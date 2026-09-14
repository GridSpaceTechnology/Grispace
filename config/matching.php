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
    | Components with missing data on either side are excluded from the
    | weighted calculation rather than receiving a neutral score. When a
    | truly neutral score is needed as a fallback, this value is used.
    |
    */

    'neutral_score' => 50,

    /*
    |--------------------------------------------------------------------------
    | Domain Compatibility Gate
    |--------------------------------------------------------------------------
    |
    | When the candidate and job belong to different professional domains
    | (e.g. Software Engineer applying to an Accountant role), the overall
    | score is capped at this value so that secondary factors (personality,
    | work preference, salary, etc.) cannot rescue an unrelated match.
    |
    */

    'domain_gate_cap' => 15,

    /*
    |--------------------------------------------------------------------------
    | Base Salary Currency
    |--------------------------------------------------------------------------
    |
    | Candidate salary expectations carry no currency; they are compared
    | against jobs listed in this market currency only.
    |
    */

    'base_salary_currency' => 'NGN',

    /*
    |--------------------------------------------------------------------------
    | Candidate Behavioral Intelligence
    |--------------------------------------------------------------------------
    |
    | Candidate-side ranking enrichment. Searches, job views and applications
    | accumulate into a private per-candidate behavioral profile which gently
    | re-ranks professionally compatible jobs. Behavior is a ranking modifier
    | only: it can never override the professional domain gate, and it is
    | never exposed to employers.
    |
    */

    'behavioral' => [

        // Master toggle for behavioral recording and ranking enrichment.
        'enabled' => true,

        // Signal strength contributed by each interaction type. Applications
        // express the strongest intent, then saves, then searches (which are
        // broader), then mere views.
        'event_weights' => [
            'search' => 10,
            'view' => 8,
            'save' => 12,
            'apply' => 20,
        ],

        // Interest signals decay toward zero with this half-life in days.
        'half_life_days' => 30,

        // A repeated view of the same job within this window counts once.
        'view_dedup_minutes' => 30,

        // Aggregated interest per signal is clamped to this ceiling.
        'interest_cap' => 100,

        // Contribution of each dimension to a job's behavioral relevance
        // score. Intended to sum to 1.0.
        'relevance_weights' => [
            'domain' => 0.35,
            'role' => 0.30,
            'skills' => 0.20,
            'job_specific' => 0.15,
        ],

        // Boost (points) added to an already-professionally-compatible
        // match, scaled by behavioral relevance up to this cap.
        'boost' => [
            'max_points' => 12,
        ],

        // Minimum recorded events before behavior influences ranking, so
        // single stray searches cannot tilt results.
        'min_activity_events' => 3,

        // Deterministic vocabulary used by the search intent parser.
        'work_arrangements' => ['remote', 'hybrid', 'onsite', 'flexible'],
        'seniority_keywords' => [
            'senior' => 'Senior',
            'junior' => 'Junior',
            'lead' => 'Lead',
            'principal' => 'Principal',
            'mid-level' => 'Mid',
            'entry-level' => 'Entry Level',
            'entry' => 'Entry Level',
            'intern' => 'Intern',
        ],
        'locations' => [
            'lagos', 'abuja', 'port harcourt', 'ibadan', 'kaduna', 'kano',
            'enugu', 'owerri', 'benin city', 'yoruba', 'nigeria',
        ],
    ],

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
