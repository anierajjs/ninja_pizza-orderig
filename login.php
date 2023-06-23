<?php
session_start();

// Include the file containing the insertAuditLog() function
include('functions.php');


if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
    header("Location:./");
    exit;
}

// Database connection settings
include('config/db_connect.php');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted login data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Perform any necessary validation on the form inputs

    // Check if the username and password are valid
    $query = "SELECT * FROM users WHERE username = ? AND password = ?";

    // Create a prepared statement
    $stmt = $conn->prepare($query);

    if ($stmt) {
        // Bind the parameters to the prepared statement
        $stmt->bind_param("ss", $username, $password);

        // Execute the prepared statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            // Login successful, set the session variables
            $user = $result->fetch_assoc();
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = $user['usertype'];

            // Insert audit log entry for login
            $action = "Logged in";
            insertAuditLog($_SESSION['username'], $action);

            // Check if the user is a staff member
            if ($_SESSION['usertype'] === 'staff') {
                // Set the staff logged-in session
                $_SESSION['staff_logged_in'] = true;

                // Insert audit log for staff login
                $action = "Staff logged in";
                insertAuditLog($_SESSION['username'], $action);
            }

            // Redirect to the home page or any other desired page
            header("Location:./");
            exit;
        } else {
            // Login failed, provide feedback to the user
            $error_message = "Invalid username or password.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle the error if the prepared statement fails
        die("Prepared statement failed: " . $conn->error);
    }
}

// Close the database connection
$conn->close();

?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ninja Pizza</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.7.0/dist/js/bootstrap.min.js"></script>

    <style>
        html,
        body {
            height: 100%;
        }
    </style>
</head>

<body class="bg-dark bg-gradient">
    <div class="h-100 d-flex justify-content-center align-items-center">
        <div class='w-100'>
            <h3 class="py-5 text-center text-light">Ninja Pizza</h3>
            <div class="card my-3 col-md-4 offset-md-4">
                <div class="card-body">
                    <form action="" id="login-form">
                        <center><small>Please enter your system credentials.</small></center>
                        <?php if (!empty($error_message)) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="username" class="control-label">Username</label>
                            <input type="text" id="username" autofocus name="username"
                                class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" id="password" autofocus name="password"
                                class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="form-group d-flex w-100 justify-content-end">
                            <button class="btn btn-sm btn-primary rounded-0 my-1">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    $(function () {
        $('#login-form').submit(function (e) {
            e.preventDefault();
            $('.pop_msg').remove();
            var _this = $(this);
            var _el = $('<div>');
            _el.addClass('pop_msg');
            _this.find('button').attr('disabled', true);
            _this.find('button[type="submit"]').text('Logging in...');
            $.ajax({
                url: './action.php?a=login',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                error: function (err) {
                    console.log(err);
                    _el.addClass('alert alert-danger');

                    _el.text("An error occurred.");
                    _this.prepend(_el);
                    _el.show('slow');
                    _this.find('button').attr('disabled', false);
                    _this.find('button[type="submit"]').text('Save');
                },
                success: function (resp) {
                    if (resp.status == 'success') {
                        _el.addClass('alert alert-success');
                        _el.text(resp.msg);

                        setTimeout(() => {
                            location.replace('./');
                        }, 2000);
                    } else {
                        _el.addClass('alert alert-danger');
                        _el.text(resp.msg);
                    }

                    _el.hide().prependTo(_this).show('slow');
                    _this.find('button').attr('disabled', false);
                    _this.find('button[type="submit"]').text('Login');
                }
            });
        });
    });
</script>

</html>
