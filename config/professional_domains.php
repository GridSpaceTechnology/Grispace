<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Professional Domain Taxonomy
    |--------------------------------------------------------------------------
    |
    | Each domain contains keywords used to classify role strings.
    | Compound keywords (2+ words) are weighted more heavily than single
    | words to improve classification accuracy. The domain with the most
    | weighted keyword matches wins. Ties are treated as ambiguous.
    |
    */

    'domains' => [

        'technology' => [
            'label' => 'Software Engineering / Technology',
            'keywords' => [
                // Compound keywords first (higher match weight)
                'full stack', 'fullstack', 'front end', 'frontend',
                'back end', 'backend', 'devops', 'site reliability',
                'sre', 'data scientist', 'data engineer', 'data analyst',
                'machine learning', 'ml engineer', 'ai engineer',
                'cloud engineer', 'security engineer', 'cyber security',
                'information security', 'mobile developer', 'ios developer',
                'android developer', 'web developer', 'web engineer',
                'test engineer', 'qa engineer', 'database administrator',
                // Single keywords
                'software', 'programmer', 'developer', 'engineer',
                'coder', 'architect', 'technical', 'technology',
                'database', 'qa', 'python', 'php', 'java', 'javascript',
                'react', 'angular', 'vue', 'node', 'laravel', 'django',
                'typescript', 'golang', 'rust', 'kubernetes', 'docker',
                'aws', 'azure', 'terraform', 'sql',
            ],
        ],

        'finance' => [
            'label' => 'Accounting / Finance',
            'keywords' => [
                'accounts payable', 'accounts receivable', 'financial analyst',
                'financial controller', 'cost accountant', 'management accountant',
                'management accounting', 'finance officer', 'tax accountant',
                'tax', 'auditor', 'audit', 'bookkeeper', 'bookkeeping',
                'treasury', 'payroll', 'accountant', 'accounting', 'finance',
                'financial', 'cfo', 'fp&a', 'budget',
            ],
        ],

        'design' => [
            'label' => 'Design / Creative',
            'keywords' => [
                'product designer', 'ux designer', 'ui designer',
                'ux researcher', 'graphic designer', 'visual designer',
                'interaction designer', 'creative director', 'art director',
                'brand designer', 'motion designer', 'illustrator',
                'designer', 'creative',
            ],
        ],

        'marketing' => [
            'label' => 'Marketing',
            'keywords' => [
                'digital marketing', 'content marketing', 'social media',
                'email marketing', 'growth marketing', 'brand manager',
                'marketing manager', 'seo', 'sem', 'ppc',
                'marketing', 'marketer', 'advertising',
            ],
        ],

        'sales' => [
            'label' => 'Sales / Business Development',
            'keywords' => [
                'business development', 'account executive', 'account manager',
                'sales manager', 'sales representative', 'sales executive',
                'sales',
            ],
        ],

        'healthcare' => [
            'label' => 'Healthcare',
            'keywords' => [
                'nurse', 'nursing', 'doctor', 'physician', 'surgeon',
                'pharmacist', 'pharmacy', 'therapist', 'radiologist',
                'laboratory', 'medical', 'healthcare', 'health care',
                'clinical', 'patient', 'dental', 'registered nurse',
            ],
        ],

        'legal' => [
            'label' => 'Legal',
            'keywords' => [
                'lawyer', 'attorney', 'solicitor', 'barrister', 'legal',
                'paralegal', 'compliance', 'regulatory', 'litigation',
            ],
        ],

        'education' => [
            'label' => 'Education',
            'keywords' => [
                'teacher', 'teaching', 'professor', 'lecturer', 'tutor',
                'instructor', 'educator', 'curriculum', 'academic',
                'school', 'university', 'education', 'training',
            ],
        ],

        'hr' => [
            'label' => 'Human Resources',
            'keywords' => [
                'human resources', 'hr manager', 'hr officer', 'hr director',
                'talent acquisition', 'recruiter', 'recruitment', 'recruiting',
                'compensation', 'benefits', 'employee relations',
                'people operations', 'organizational development',
                'talent management', 'learning and development',
            ],
        ],

        'operations' => [
            'label' => 'Operations / Project Management',
            'keywords' => [
                'operations manager', 'operations director', 'coo',
                'project manager', 'program manager', 'scrum master',
                'product manager', 'product owner', 'business analyst',
                'process improvement', 'supply chain', 'procurement',
                'operations',
            ],
        ],

        'construction' => [
            'label' => 'Construction / Architecture',
            'keywords' => [
                'civil engineer', 'structural engineer',
                'construction', 'quantity surveyor', 'building',
            ],
        ],

        'logistics' => [
            'label' => 'Logistics / Supply Chain',
            'keywords' => [
                'logistics', 'warehouse', 'fleet', 'distribution',
                'supply chain manager', 'inventory', 'logistics manager',
            ],
        ],

        'customer_support' => [
            'label' => 'Customer Support / Service',
            'keywords' => [
                'customer support', 'customer service', 'call center',
                'help desk', 'technical support', 'client success',
                'customer success',
            ],
        ],

        'administration' => [
            'label' => 'Administration / Office Management',
            'keywords' => [
                'office manager', 'office administrator',
                'executive assistant', 'personal assistant', 'receptionist',
                'administrative',
            ],
        ],

        'media' => [
            'label' => 'Media / Journalism',
            'keywords' => [
                'journalist', 'reporter', 'editor', 'content writer',
                'copywriter', 'broadcast', 'media', 'publishing',
                'photographer', 'videographer', 'filmmaker',
            ],
        ],

    ],

];
