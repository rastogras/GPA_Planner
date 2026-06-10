<?php
// includes/gpa.php
require_once __DIR__ . '/config.php';

class GPA {

    // Convert letter grade to grade points
    public static function letterToPoints(string $letter): float {
        $scale = GRADE_SCALE;
        return $scale[$letter] ?? 0.0;
    }

    // Calculate GPA for one semester's courses
    // $courses = [['grade_points' => 3.7, 'credit_hours' => 3], ...]
    public static function semesterGPA(array $courses): float {
        $totalPoints  = 0;
        $totalCredits = 0;
        foreach ($courses as $c) {
            if (!empty($c['credit_hours']) && isset($c['grade_points'])) {
                $totalPoints  += $c['grade_points'] * $c['credit_hours'];
                $totalCredits += $c['credit_hours'];
            }
        }
        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 4) : 0.0;
    }

    // Calculate cumulative GPA across all semesters
    // $semesters = [['courses' => [...]], ...]
    public static function cgpa(array $semesters): float {
        $totalPoints  = 0;
        $totalCredits = 0;
        foreach ($semesters as $sem) {
            foreach ($sem['courses'] as $c) {
                if (!empty($c['credit_hours']) && isset($c['grade_points'])) {
                    $totalPoints  += $c['grade_points'] * $c['credit_hours'];
                    $totalCredits += $c['credit_hours'];
                }
            }
        }
        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 4) : 0.0;
    }

    // Get honors classification string for a given CGPA
    public static function getClassification(float $cgpa): array {
        foreach (HONORS as $h) {
            if ($cgpa >= $h['min']) return $h;
        }
        return ['label' => 'Fail', 'min' => 0.0, 'color' => '#E24B4A'];
    }

    // Project required GPA per remaining semester to hit a target CGPA
    public static function projectRequired(
        float $currentCGPA,
        int   $completedCredits,
        float $targetCGPA,
        int   $remainingSemesters,
        int   $creditsPerSem = 18
    ): array {
        $futureCredits = $remainingSemesters * $creditsPerSem;
        $totalCredits  = $completedCredits + $futureCredits;

        $pointsNeeded = ($targetCGPA * $totalCredits) - ($currentCGPA * $completedCredits);
        $gpaPerSem    = $futureCredits > 0 ? round($pointsNeeded / $futureCredits, 4) : null;

        $feasibility = 'not_possible';
        if ($gpaPerSem === null) {
            $feasibility = 'no_future_sems';
        } elseif ($currentCGPA >= $targetCGPA) {
            $feasibility = 'already_met';
        } elseif ($gpaPerSem <= 0) {
            $feasibility = 'already_met';
        } elseif ($gpaPerSem <= 3.0) {
            $feasibility = 'achievable';
        } elseif ($gpaPerSem <= 3.7) {
            $feasibility = 'challenging';
        } elseif ($gpaPerSem <= 4.0) {
            $feasibility = 'very_hard';
        }

        return [
            'target_cgpa'         => $targetCGPA,
            'gpa_needed_per_sem'  => $gpaPerSem,
            'future_credits'      => $futureCredits,
            'feasibility'         => $feasibility,
        ];
    }

    // Generate plain-text AI-style recommendations
    public static function getRecommendations(float $cgpa, float $targetCGPA, string $feasibility): array {
        $tips = [];
        switch ($feasibility) {
            case 'already_met':
                $tips[] = 'You have already met the CGPA requirement. Maintain consistent performance to secure your classification.';
                break;
            case 'achievable':
                $tips[] = 'Your target is achievable. Stay consistent with assignments, CATs, and exams. Aim for a B or higher in every unit.';
                break;
            case 'challenging':
                $tips[] = 'You will need strong performance each semester (B+ to A range). Seek lecturer feedback early, form study groups, and focus extra time on weaker units.';
                $tips[] = 'CATs and assignments can account for 30–40% of your final grade at JKUAT — never miss or underperform in them.';
                break;
            case 'very_hard':
                $tips[] = 'You need near-perfect scores going forward. This is possible but requires maximum effort. Eliminate distractions and dedicate structured daily study hours.';
                $tips[] = 'Talk to your academic advisor about supplementary units or retake options that may improve your CGPA under JKUAT regulations.';
                break;
            case 'not_possible':
                $tips[] = 'Reaching this classification in the remaining semesters is mathematically not possible. Consider targeting a lower classification and aiming to exceed it.';
                $tips[] = 'See your academic advisor to explore options under JKUAT policy, such as credit transfers or supplementary examinations.';
                break;
        }
        if ($cgpa < 2.0) {
            $tips[] = 'Warning: Your CGPA is below the minimum pass threshold (2.0). Contact your academic advisor immediately for a recovery plan.';
        }
        return $tips;
    }
}
