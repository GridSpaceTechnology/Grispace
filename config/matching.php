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
    | Algorithm Version
    |--------------------------------------------------------------------------
    |
    | Bump whenever the scoring semantics change. Persisted match scores carry
    | this version so stale rows (computed by an older algorithm) can be
    | detected and recomputed instead of being trusted by dashboards.
    |
    */

    'algorithm_version' => 5,

    /*
    |--------------------------------------------------------------------------
    | Match Status Labels
    |--------------------------------------------------------------------------
    |
    | Engine-level status derived from the professional profile score. The
    | domain gate has priority: whenever it capped a score the status must be
    | 'incompatible' even if the numeric score looks middling. These keys are
    | returned by JobMatchingService::matchStatusFor() and are distinct from
    | the friendlier category labels used by the UI.
    |
    */

    'statuses' => [
        'excellent' => ['label' => 'Excellent Match', 'min' => 90],
        'strong' => ['label' => 'Strong Match', 'min' => 80],
        'moderate' => ['label' => 'Moderate Match', 'min' => 60],
        'weak' => ['label' => 'Weak Match', 'min' => 0],
        'incompatible' => ['label' => 'Incompatible Match', 'min' => 0],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hard vs Soft Gaps
    |--------------------------------------------------------------------------
    |
    | Gaps surfaced by these components (e.g. a missing required skill, an
    | unmet experience requirement) are treated as deal-breakers. Gaps from
    | other components (salary stretch, culture differences, an over-qualified
    | profile) are noted as soft because they are negotiable or contextual.
    |
    */

    'hard_gap_components' => [
        'skills',
        'role',
        'experience',
        'education',
        'work_preference',
        'availability',
    ],

    /*
    |--------------------------------------------------------------------------
    | Recommendation Layer
    |--------------------------------------------------------------------------
    |
    | The candidate-facing ranking pipeline built on top of the professional
    | profile score. Hard-prune filters are applied BEFORE expensive scoring
    | so only plausible jobs are fully scored; the domain gate still governs
    | compatibility and is never bypassed by pruning. Deduplication collapses
    | identical listings posted by the same company and diversity stops one
    | company from sweeping the top results.
    |
    */

    'recommendation' => [
        // Collapse jobs that are identical (same company + title) and keep the
        // best-scoring listing of each duplicate group.
        'dedupe_identical_jobs' => true,

        // Maximum number of listings from a single company allowed in the top
        // results before the remainder is pushed lower, promoting variety.
        'diversity_max_per_company' => 3,

        // Cheap deterministic filters applied before scoring. Each one can be
        // toggled independently. They are hard kills, not soft penalties.
        'hard_prune' => [
            // Drop jobs that demand more experience than the candidate holds.
            'experience' => true,
            // Drop jobs whose working arrangement is a hard mismatch for the
            // candidate's preference (matrix score <= 30).
            'work_preference' => true,
            // Drop on-site/hybrid jobs based abroad when the candidate is
            // elsewhere.
            'location_country' => true,
            // Drop jobs with required skills the candidate has none of.
            // Off by default: strong professionals can still be worth surfacing
            // even with zero literal skill overlap.
            'required_skill_overlap' => false,
        ],

        // Bounded, decaying negative signals derived from candidate feedback
        // (e.g. "not relevant", "not interested", "wrong role"). The penalty
        // applies to the recommendation ranking score only - never to the
        // professional profile match, never to the domain gate, and never to
        // employer-facing candidate rankings.
        'negative_signal' => [
            // Master toggle for negative-signal ranking adjustments.
            'enabled' => true,

            // Points subtracted from recommendation_score per negative
            // assessment before time decay.
            'penalty_points' => 12,

            // Domain spill-over: a dismissed job reduces other jobs in the
            // same professional domain by this fraction of the base penalty.
            'domain_decay' => 0.5,

            // Total penalty applied to a single job is clamped to this cap so
            // a handful of dismissals can never zero out an otherwise strong
            // match.
            'penalty_cap' => 30,

            // Negative signals decay toward zero with this half-life in days.
            'half_life_days' => 14,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Match Quality Metrics
    |--------------------------------------------------------------------------
    |
    | Internal matching analytics guardrails. Reports only surface when enough
    | outcome events exist to be meaningful, and ranking-quality metrics are
    | computed over the top-K candidate/job lists.
    |
    */

    'metrics' => [
        // Minimum recorded outcome events before band reports are shown.
        'min_sample' => 20,

        // Standard list depth used by precision@K / recall@K.
        'top_k' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Experiment Infrastructure
    |--------------------------------------------------------------------------
    |
    | Infrastructure for future algorithm-variant experiments. Every outcome
    | event and match snapshot carries the algorithm_version that produced it,
    | so version A/B comparisons are possible without any live routing. These
    | flags stay off: experiments never change hard safety/compatibility rules.
    |
    */

    'experiments' => [
        // Whether version comparison reports are rendered in the admin UI.
        'reporting' => true,

        // Live routing of users to variants. Always false - recording only.
        'live_routing' => false,

        // Human-readable labels per algorithm version for analytics reports.
        'versions' => [
            3 => 'v3 (deterministic)',
            4 => 'v4 (deterministic + behavioral)',
            5 => 'v5 (semantic-enabled)',
        ],

        // Phase 5 experiment arms. All arms share identical hard gates,
        // thresholds and component weights; only the semantic ranking layer
        // is toggled. 5A records deterministic + behavioral results, 5B adds
        // the bounded semantic understanding layer.
        'arms' => [
            '5a' => ['label' => 'v5A (deterministic + behavioral)', 'semantic' => false],
            '5b' => ['label' => 'v5B (+ semantic understanding)', 'semantic' => true],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Match Outcome Events
    |--------------------------------------------------------------------------
    |
    | The outcome event vocabulary recorded for match-quality measurement.
    | Each key maps to the category used by the internal analytics and the
    | minimum signal weight. Events are append-only; a unique occurrence key
    | makes re-recording idempotent.
    |
    */

    'outcome_events' => [
        'job_recommended' => ['category' => 'exposure', 'weight' => 1],
        'job_viewed' => ['category' => 'exposure', 'weight' => 8],
        'candidate_profile_viewed' => ['category' => 'exposure', 'weight' => 8],
        'job_applied' => ['category' => 'application', 'weight' => 20],
        'application_withdrawn' => ['category' => 'application', 'weight' => 4],
        'application_rejected' => ['category' => 'application', 'weight' => 4],
        'application_advanced' => ['category' => 'application', 'weight' => 10],
        'candidate_shortlisted' => ['category' => 'application', 'weight' => 10],
        'hire_recorded' => ['category' => 'application', 'weight' => 40],
        'employer_contacted_candidate' => ['category' => 'contact', 'weight' => 6],
        'candidate_contacted_employer' => ['category' => 'contact', 'weight' => 6],
        'interview_scheduled' => ['category' => 'interview', 'weight' => 12],
        'interview_completed' => ['category' => 'interview', 'weight' => 18],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recommendation Feedback
    |--------------------------------------------------------------------------
    |
    | Explicit candidate and employer feedback types collected for calibration
    | and quality measurement. Feedback never mutates match scores directly
    | (except the bounded, decaying negative signal above); it drives the
    | internal analytics and human-controlled calibration.
    |
    */

    'feedback' => [
        'candidate' => [
            'relevant' => ['negative' => false],
            'not_relevant' => ['negative' => true],
            'not_interested' => ['negative' => true],
            'already_applied' => ['negative' => false],
            'wrong_role' => ['negative' => true],
            'wrong_location' => ['negative' => true],
            'wrong_experience' => ['negative' => true],
        ],
        'employer' => [
            'relevant_candidate' => ['negative' => false],
            'not_relevant' => ['negative' => false],
            'skills_mismatch' => ['negative' => false],
            'experience_mismatch' => ['negative' => false],
            'role_mismatch' => ['negative' => false],
            'already_contacted' => ['negative' => false],
            'not_interested' => ['negative' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Match Persistence & Staleness
    |--------------------------------------------------------------------------
    |
    | Persisted job_match_scores rows are treated as stale when their stored
    | algorithm version no longer matches, their data checksum differs from the
    | current candidate/job data, or they are older than the TTL. Employer-side
    | candidate matching prefers fresh persisted scores over live scoring.
    |
    */

    'staleness' => [
        'ttl_hours' => 24,
        'use_persisted_matches' => true,
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Semantic Understanding Layer (Phase 5)
    |--------------------------------------------------------------------------
    |
    | A bounded, explainable ranking refinement built ON TOP of the
    | deterministic professional score. It never replaces the engine, never
    | satisfies a required skill, never crosses the domain gate, and never
    | invents experience or qualifications. Its only effect is a capped,
    | additive nudge to the candidate-facing and employer-facing
    | recommendation score so semantically-aligned but differently-worded
    | pairs (Backend Developer vs Backend Engineer, Postgres vs PostgreSQL)
    | rank slightly higher - while an excellent structured match always
    | outranks a poor one, semantic or not.
    |
    | The layer is opt-in. When disabled (default) ranking behaves exactly
    | like the deterministic engine (the 5A experiment arm). Enabling it
    | selects the 5B arm without changing any hard rule.
    |
    */

    'semantic' => [

        // Master toggle. Off by default so deployed ranking is unchanged
        // until the operator opts in via SEMANTIC_MATCHING_ENABLED.
        'enabled' => (bool) env('SEMANTIC_MATCHING_ENABLED', false),

        // score: 'lexical'  - deterministic local vocabulary/taxonomy provider
        //                     (zero cost, zero latency, zero data sent away).
        //         'embeddings' - configured hosted embedding provider behind
        //                     the queue jobs below. Retrieval-only on hot
        //                     paths; generation is queued and idempotent.
        'provider' => env('EMBEDDING_PROVIDER', 'lexical'),

        // Maximum ranking points the semantic score may add on top of the
        // professional profile score. Deliberately small so structure always
        // dominates: an excellent match (>= 90) cannot be overtaken by a
        // weak one (<= 60) no matter how semantically similar.
        'maximum_influence' => 15,

        // Semantic scores are capped at this value when the candidate and job
        // belong to different professional domains, preserving the
        // separation the deterministic gate already enforces.
        'incompatible_cap' => 20,

        // Weighted blend for the 0-100 semantic score. Intended to sum to 1.
        'weights' => [
            'role' => 0.35,
            'skill' => 0.35,
            'description' => 0.20,
            'domain' => 0.10,
        ],

        // Description similarity is only reported when the job description
        // is long enough to be meaningful; short/noisy text is skipped.
        'min_description_chars' => 100,

        // Maximum evidence-based reasons surfaced to the UI per match.
        'reasons_max' => 3,

        // Include the employer company name/description in the job
        // representation (employer-side context for both directions).
        'include_company' => true,

        // Role phrases treated as semantically equal when comparing the
        // candidate's desired role against the job title/role. These are a
        // ranking refinement only; the deterministic role component owns the
        // gating signal.
        'role_synonym_groups' => [
            ['backend', 'back-end', 'back end', 'server-side', 'server side'],
            ['frontend', 'front-end', 'front end', 'client-side', 'client side'],
            ['full-stack', 'full stack', 'fullstack'],
            ['api', 'rest', 'restful', 'web api', 'web service', 'web-service'],
            ['database', 'database administrator', 'dba'],
            ['software', 'application', 'app'],
            ['accountant', 'accounting', 'accounts', 'bookkeeper', 'bookkeeping', 'finance', 'financial'],
            ['nurse', 'nursing', 'registered nurse', 'nurse practitioner'],
        ],

        // Skill spellings that mean the same thing. Aliased BEFORE a match is
        // counted so "PostgreSQL" on a job can match "Postgres" on a profile
        // for the semantic score - but never for required-skill satisfaction,
        // which stays exact and deterministic. Java and JavaScript are NOT
        // aliased, so they remain distinct skills.
        'skill_aliases' => [
            'restful' => 'rest api',
            'rest' => 'rest api',
            'rest apis' => 'rest api',
            'restful api' => 'rest api',
            'restful apis' => 'rest api',
            'rest api' => 'rest api',
            'rest apis api' => 'rest api',
            'postgresql' => 'postgres',
            'postgres' => 'postgres',
            'nodejs' => 'node',
            'node.js' => 'node',
            'react.js' => 'react',
            'reactjs' => 'react',
            'next.js' => 'nextjs',
            'next' => 'nextjs',
            'angularjs' => 'angular',
            'golang' => 'go',
            'typescript' => 'typescript',
            'ts' => 'typescript',
            'c#' => 'c sharp',
            'c++' => 'c plus plus',
            'php' => 'php',
        ],

        // Peer-language vocabulary used for description-level similarity.
        'stopwords' => [
            'a', 'an', 'the', 'of', 'and', 'or', 'in', 'on', 'for', 'to',
            'with', 'at', 'by', 'is', 'are', 'be', 'as', 'we', 'you', 'our',
            'your', 'this', 'that', 'will', 'can', 'must', 'may', 'well',
            'join', 'team', 'role', 'work', 'using', 'use', 'experience',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Provider (optional)
    |--------------------------------------------------------------------------
    |
    | Settings for the hosted embedding provider used only when
    | semantic.provider is 'embeddings'. Embedding vectors live in the
    | semantic_embeddings table; they are generated asynchronously by the
    | GenerateCandidateSemanticEmbedding / GenerateJobSemanticEmbedding
    | queue jobs and refreshed by the semantic:refresh command. All input is
    | the privacy-filtered representation built by SemanticMatchingService -
    | names, emails, phone numbers and private notes are never embedded.
    |
    */

    'embedding' => [
        'model' => env('EMBEDDING_MODEL', 'text-embedding-3-small'),
        'api_key' => env('EMBEDDING_API_KEY', ''),
        'api_url' => env('EMBEDDING_API_URL', 'https://api.openai.com/v1/embeddings'),
        'timeout_seconds' => 30,
        // Respect provider rate limits by spacing generations out this much.
        'min_interval_ms' => 800,
        // Bump this to invalidate and regenerate every stored vector.
        'version' => '2026-09-15',
    ],
];
