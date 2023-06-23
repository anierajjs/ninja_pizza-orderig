<?php
session_start();

// Include the necessary files and establish the database connection
require_once('config/db_connect.php');
require_once('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the user ID is the main admin (ID=1)
    if ($id == 1) {
        $error_message = "The main admin account cannot be deleted.";
    } else {
        // Delete the user account
        if (deleteUser($id)) {
            // Deletion successful, redirect to a success page or display a success message
            header("Location: users.php?user_removed=true");
            exit;
        } else {
            // Deletion failed, handle the error and provide feedback to the user
            $error_message = "User deletion failed. Please try again.";
        }
    }
}

// Close the database connection
$conn->close();
?>
