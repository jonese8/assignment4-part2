<?php
#database.php - CS290, Emmalee Jones, Assignment 4.2
#Error Reporting Settings
error_reporting(E_ALL);
ini_set('display_errors', 'OFF');

#Test for duplicate name from video_store
function isNameUniq ($name, $mysqli) {
    if (!($nameRows = $mysqli->query("SELECT name FROM video_store WHERE name=\"{$name}\""))) {
        echo "Error: Name Field Not Found: " . $mysqli->errno . " - " . $mysqli->error;
    }
    return mysqli_num_rows($nameRows);
}

#Delete all video_store rows by truncating table
function clearVideos($mysqli) {
    if (!($mysqli->query("TRUNCATE TABLE video_store"))) {
        echo "Error: Video Store not found on Truncation: " . $mysqli->errno . " - " . $mysqli->error;
    }
}

#Delete one row of videos from video_store
function delRow($id, $mysqli) {
    if (!($mysqli->query("DELETE FROM video_store WHERE id={$id}"))) {
        echo "Error: Id Field Not Found on Delete: " . $mysqli->errno . " - " . $mysqli->error;
    }
}

#Update video_store row by id for check in/check out
function chkInOut($id, $mysqli) {
    if (!($mysqli->query("UPDATE video_store SET rented = !rented WHERE id={$id}"))) {
        echo "Error: Id Field Not Found on Update: " . $mysqli->errno . " - " . $mysqli->error;
    }
}

?>