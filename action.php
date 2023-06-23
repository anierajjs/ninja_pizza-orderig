<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include the necessary files and establish the database connection
require_once('config/db_connect.php');

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted username and password
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute a parameterized query to fetch the user with the provided username and password
    $query = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching user is found
    if ($result->num_rows == 1) {
        // User found, set session variables and redirect to the home page
        $user = $result->fetch_assoc();
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usertype'] = $user['usertype'];

        // Prepare the response data
        $response = array(
            'status' => 'success',
            'msg' => 'Login successful'
        );
    } else {
        // Invalid login credentials, prepare the error response
        $response = array(
            'status' => 'error',
            'msg' => 'Invalid username or password'
        );
    }

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Redirect to the login page if accessed directly without submitting the form
    header("Location: login.php");
    exit;
}
?>
