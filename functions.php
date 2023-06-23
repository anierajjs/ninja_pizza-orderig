<?php

// Function to insert audit log entry
function insertAuditLog($username, $action)
{
    // Database connection settings
    include('config/db_connect.php');

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO audit_logs (username, action_made, date_created) VALUES (?, ?, NOW())";

    // Create a prepared statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters to the prepared statement
    $stmt->bind_param("ss", $username, $action);

    // Execute the prepared statement
    $stmt->execute();

    // Close the statement and database connection
    $stmt->close();
    $conn->close();
}

// Check if a session has already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and insert audit log entry for successful login
if (isset($_SESSION['username']) && !isset($_SESSION['login_audit_logged'])) {
    $_SESSION['login_audit_logged'] = true;
    $username = $_SESSION['username'];
    $action = "Logged in";
    insertAuditLog($username, $action);
}


// Function to get the username of a user by ID
function getUsername($userId)
{
    global $conn;

    // Prepare and execute the query
    $query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['username'];
    } else {
        return null;
    }
}


// Function to delete a user by ID
function deleteUser($userId)
{
    global $conn;

    // Get the username of the user
    $username = getUsername($userId);

    // If the user exists
    if ($username) {
        // Prepare and execute the delete query
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();

        // If the delete is successful
        if ($result) {
            // Log the action in the audit log
            //$action = "Deleted user with ID: $userId and username: $username";
            $action = "Deleted user: $username";
            insertAuditLog($_SESSION['username'], $action);
        }

        // Return true if delete is successful, false otherwise
        return $result;
    } else {
        // User does not exist
        return false;
    }
}

?>
