<?php

// Function to insert an audit log entry
function insertAuditLog($username, $action) {
    global $conn;

    $username = mysqli_real_escape_string($conn, $username);
    $action = mysqli_real_escape_string($conn, $action);
    $query = "INSERT INTO audit_logs (username, action_made, date_created) VALUES ('$username', '$action', NOW())";
    mysqli_query($conn, $query);
}

session_start();
include('config/db_connect.php');

// Assuming you have a user authentication system in place and the user is logged in

// Retrieve the email based on the currently logged-in user
$userID = $_SESSION['id']; // Replace 'user_id' with the appropriate session variable storing the user ID

$sql = "SELECT email FROM users WHERE id = $userID";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['email'] = $row['email'];
} else {
    // Email retrieval failed, handle the error
    $_SESSION['email'] = 'Unknown'; // Set a default value or handle the error accordingly
}

$email = $_SESSION['email']; // Assign the email value to the $email variable

$title = $ingredients = '';
$errors = array('title' => '', 'ingredients' => '');

if (isset($_POST['submit'])) {
    // check title
    if (empty($_POST['title'])) {
        $errors['title'] = 'A title is required';
    } else {
        $title = $_POST['title'];
        if (!preg_match('/^[a-zA-Z\s]+$/', $title)) {
            $errors['title'] = 'Title must be letters and spaces only';
        } else {
            // Check if a pizza with the same title already exists
            $existingPizzaQuery = "SELECT * FROM pizzas WHERE title = '$title' LIMIT 1";
            $existingPizzaResult = mysqli_query($conn, $existingPizzaQuery);
            if ($existingPizzaResult && mysqli_num_rows($existingPizzaResult) > 0) {
                $errors['title'] = 'A pizza with the same title already exists';
            }
        }
    }

    // check ingredients
    if (empty($_POST['ingredients'])) {
        $errors['ingredients'] = 'At least one ingredient is required';
    } else {
        $ingredients = $_POST['ingredients'];
        if (!preg_match('/^([a-zA-Z\s]+)(,\s*[a-zA-Z\s]*)*$/', $ingredients)) {
            $errors['ingredients'] = 'Ingredients must be a comma-separated list';
        }
    }

    if (array_filter($errors)) {
        // There are errors in the form
    } else {
        // escape sql chars
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $ingredients = mysqli_real_escape_string($conn, $_POST['ingredients']);

        // create sql
        $sql = "INSERT INTO pizzas(title,email,ingredients) VALUES('$title','$email','$ingredients')";

        // save to db and check
        if (mysqli_query($conn, $sql)) {
            // Insert audit log entry for the successful pizza addition
            $username = $_SESSION['username'];
            $action = "Added a new pizza: $title";
            insertAuditLog($username, $action);

            // success
            header('Location: index.php');
        } else {
            echo 'query error: ' . mysqli_error($conn);
        }
    }
} // end POST check
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add | Ninja Pizza</title>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="container grey-text">
        <h4 class="center">Add a Pizza</h4>
        <form class="white" action="add.php" method="POST">
            <label>Your Email</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>

            <label>Pizza Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
            <div class="red-text"><?php echo $errors['title']; ?></div>

            <label>Ingredients (comma separated)</label>
            <input type="text" name="ingredients" value="<?php echo htmlspecialchars($ingredients); ?>">
            <div class="red-text"><?php echo $errors['ingredients']; ?></div>

            <div class="center">
                <input type="submit" name="submit" value="Submit" class="btn brand z-depth-0">
            </div>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
