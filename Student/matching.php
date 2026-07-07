<?php
// matching.php
// (re)calculates roommate compatibility for one
// student against every other student, and saves the results into


function calculate_matches($conn, $student_id) {

    // This student's own preferences
    $stmt = mysqli_prepare($conn, "SELECT * FROM student_preferences WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $myPrefs = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$myPrefs) {
        return; // nothing to compare yet
    }

    // Every other student who also has preferences saved
    $stmt = mysqli_prepare($conn, "
        SELECT sp.*
        FROM student_preferences sp
        JOIN students s ON sp.student_id = s.student_id
        WHERE sp.student_id != ? AND s.role = 'student'
    ");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $others = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

    // Clear this student's old matches before recalculating
    $stmt = mysqli_prepare($conn, "DELETE FROM roommate_matches WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);

    foreach ($others as $other) {
        $score = 0;

        // Room type match: 25%
        if ($myPrefs['room_type'] === $other['room_type']) {
            $score += 25;
        }

        // Study style match: 25%
        if ($myPrefs['study_style'] === $other['study_style']) {
            $score += 25;
        }

        // Sleep schedule match: 25%
        if ($myPrefs['sleep_schedule'] === $other['sleep_schedule']) {
            $score += 25;
        }

        // Budget closeness: 25%, scaled down the further apart they are
        if ($myPrefs['budget'] !== null && $other['budget'] !== null) {
            $diff = abs($myPrefs['budget'] - $other['budget']);
            if ($diff <= 2000) {
                $score += 25;
            } elseif ($diff <= 5000) {
                $score += 15;
            } elseif ($diff <= 10000) {
                $score += 5;
            }
        }

        // Only save it as a match if there's at least some compatibility
        if ($score > 0) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO roommate_matches (student_id, matched_student_id, match_percentage)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "iid", $student_id, $other['student_id'], $score);
            mysqli_stmt_execute($stmt);
        }
    }
}