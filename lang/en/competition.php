<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Competition System — English
    |--------------------------------------------------------------------------
    */

    // Navigation
    'navigation_group'       => 'Competition & Excellence',
    'leaderboard_title'      => 'Branch Levels Leaderboard',
    'leaderboard_subtitle'   => 'Branches ranked by lowest financial loss from tardiness',

    // Period
    'period'                 => 'Period',

    // Ranking
    'ranking_method'         => 'Ranking Method',
    'ranking_by_loss'        => 'Lowest financial loss first',
    'financial_loss'         => 'Financial Loss',
    'total_delay'            => 'Total Delay',
    'sar'                    => 'SAR',
    'min'                    => 'min',

    // Levels
    'level_legendary'        => 'Legendary',
    'level_diamond'          => 'Diamond',
    'level_gold'             => 'Gold',
    'level_silver'           => 'Silver',
    'level_bronze'           => 'Bronze',
    'level_starter'          => 'Starter',

    // Stats
    'score'                  => 'Score',
    'employees'              => 'Employees',
    'late_checkins'          => 'Late Check-ins',
    'missed_days'            => 'Missed Days',
    'perfect_employees'      => 'Perfect Employees',
    'total_points'           => 'Excellence Points',

    // Badges
    'trophy_winner'          => 'Champion - First Place',
    'turtle_last'            => 'Turtle - Last Place',

    // Scoring Legend
    'scoring_legend'         => 'Scoring Formula',
    'base_score'             => 'Base Score',
    'late_penalty'           => 'Penalty per late check-in',
    'missed_penalty'         => 'Penalty per missed day',
    'perfect_bonus'          => 'Bonus per perfect employee',
    'points_bonus'           => 'Excellence points bonus',

    // News Ticker
    'news_ticker_title'      => 'Latest News',
    'trophy_first_title'     => 'First & Last Check-in Today - Per Branch',
    'turtle_last_title'      => 'Last Check-in Today',
    'no_turtles'             => 'Only one check-in',
    'ticker_trophy'          => 'Most disciplined branch today: :branch',
    'ticker_turtle'          => 'Least disciplined branch today: :branch',
    'ticker_attendance'      => 'Today attendance: :on_time on time | :late late',
    'ticker_total_employees' => 'Total active employees: :count',
    'ticker_top_scorer'      => 'Top scorer: :name (:points pts)',

    // Empty States
    'no_branches'            => 'No active branches to rank',
    'no_news'                => 'No news at the moment',

    // Hints
    'leaderboard_hint'       => 'A competitive ranking system that ranks branches from best to worst based on attendance, discipline, and absence scores.',
    'scoring_legend_hint'    => 'Each branch starts with 1000 points. 5 points deducted per late check-in, 15 per absence. 20 bonus points per perfect employee.',
    'level_legendary_hint'   => 'Highest competitive level (950+ points) — indicates near-perfect discipline with exceptional performance.',
    'financial_loss_hint'    => 'Total financial cost of branch delays for the current month.',

    // Score Adjustments
    'score_adjustment_navigation' => 'Score Adjustments',
    'score_adjustment_model'      => 'Score Adjustment',
    'score_adjustment_plural'     => 'Score Adjustments',
    'scope_hint'                  => 'Define the adjustment scope: applies to an entire branch, a single employee, or a specific department.',
    'scope_branch_hint'           => 'The branch to which the score adjustment will be applied.',
    'scope_user_hint'             => 'The employee to whom the score adjustment will be applied.',
    'scope_department_hint'       => 'The department to which the score adjustment will be applied.',
    'points_delta_hint'           => 'Number of points to add (positive) or deduct (negative) from the current balance.',
    'value_delta_hint'            => 'Additional monetary adjustment linked to the score change (if any) — in SAR.',
    'category_hint'               => 'Classification: manual (administrative decision), reward, disciplinary deduction, or error correction.',
    'adjustment_reason_hint'      => 'Reason for the score adjustment — required for documentation and auditing.',
];
