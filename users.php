<?php

// Include the file containing the insertAuditLog() function
include('functions.php');

// Check if a session has already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the necessary files and establish the database connection
require_once('config/db_connect.php');

// Check if the database connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Initialize variables for form validation and username/email availability
$nameErr = $usernameErr = $passwordErr = $emailErr = '';
$name = $username = $password = $email = '';

// Function to add a user account with a specific usertype
function addUser($username, $password, $email, $usertype) {
  global $conn;

  // Prepare and execute the query to insert the user's information
  $query = "INSERT INTO users (username, password, email, usertype) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ssss", $username, $password, $email, $usertype);

  if ($stmt->execute()) {
    return true;
  } else {
    return false;
  }
}

// Check if the "Remove" link is clicked
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];

    // Update the usertype to "staff" for the clicked user
    if (updateUserUsertype($remove_id, 'staff')) {
        // Usertype update successful, redirect to a success page or display a success message
        header("Location: users.php?usertype_updated=true");

        // Insert audit log entry for the successful user usertype update
        $username = getUsername($remove_id);
        //$action = "Updated usertype to 'staff' for user with ID: $remove_id and username: $username";
        $action = "Updated usertype to 'staff' for: $username";
        insertAuditLog($_SESSION['username'], $action);

        exit;
    } else {
        // Usertype update failed, handle the error and provide feedback to the user
        $error_message = "Failed to update usertype. Please try again.";
    }
}

// Function to update the usertype of a user
function updateUserUsertype($id, $usertype) {
  global $conn;

  // Prepare and execute the query to update the user's usertype
  $query = "UPDATE users SET usertype = ? WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("si", $usertype, $id);

  if ($stmt->execute()) {
    return true;
  } else {
    return false;
  }
}


// Check if the registration form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (empty($_POST['username'])) {
    $usernameErr = "Username is required";
  } else {
    $username = $_POST['username'];

    // Check if the username already exists in the database
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $usernameErr = "Username already exists";
    }
  }

  if (empty($_POST['password'])) {
    $passwordErr = "Password is required";
  } else {
    $password = $_POST['password'];
  }

  if (empty($_POST['email'])) {
    $emailErr = "Email is required";
  } else {
    $email = $_POST['email'];

    // Check if the email already exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $emailErr = "Email already exists";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }

  // If there are no validation errors, insert the user's information into the database
  if (empty($nameErr) && empty($usernameErr) && empty($passwordErr) && empty($emailErr)) {
    // Set the usertype to either "admin" or "staff" based on the selected option
    $usertype = ($_POST['usertype'] === 'admin') ? 'admin' : 'staff';

    // Register the user account with the specified usertype
    if (addUser($username, $password, $email, $usertype)) {
      // Insert audit log entry for the successful user registration
      $action = "Registered a new user: $username";
      insertAuditLog($_SESSION['username'], $action);
      // Registration successful, redirect to a success page or display a success message
      header("Location: users.php?success=true&username=" . urlencode($username) . "&email=" . urlencode($email));
      exit;
    } else {
      // Registration failed, handle the error and provide feedback to the user
      $error_message = "Registration failed. Please try again.";
    }
  }
}

// Query to fetch all registered users
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

// Check if any users are found
if (mysqli_num_rows($result) > 0) {

} else {
  // No users found
  echo "No registered users.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users | Ninja Pizza</title>
  <link rel="stylesheet" href="css/aniera.css">
</head>

<?php include('templates/header.php'); ?>

<body>
  <div class="container">
    <?php if ($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin')  { ?>
      <h4 class="center grey-text">Manage Users</h4>
    <?php } ?>


    <div class="row">
      <table class="white">
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <?php if ($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin') { ?>
              <th>Actions</th>
            <?php } ?>

          </tr>
        </thead>
        <tbody>
          <?php
          // Loop through each user and display their details
          while ($row = $result->fetch_assoc()) {
          ?>
            <tr>
              <td><?php echo $row['username']; ?></td>
              <td><?php echo $row['email']; ?></td>
              <td><?php echo $row['usertype']; ?></td>
              <td>
                  <?php if (($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin') && $row['usertype'] !== 'main admin' && $row['id'] !== 1) { ?>
                      <?php if ($row['usertype'] === 'admin') { ?>
                          <a href="users.php?remove_id=<?php echo $row['id']; ?>">Remove</a>
                      <?php } else { ?>
                          <a href="delete_user.php?id=<?php echo $row['id']; ?>">Delete</a>
                      <?php } ?>
                  <?php } ?>
              </td>
            </tr>
          <?php
          }
          ?>
        </tbody>
      </table>
    </div>


    <!-- Include this code where you want to display the messages -->
    <?php if (isset($error_message)) { ?>
      <p class="red-text center"><?php echo $error_message; ?></p>
      <?php unset($error_message); ?>
    <?php } ?>

    <?php if (isset($_GET['success'])) { ?>
      <p class="green-text center">User account created successfully! Username: <?php echo $_GET['username']; ?>, Email: <?php echo $_GET['email']; ?></p>
      <?php unset($_GET['success']); ?>
    <?php } ?>

    <?php if (isset($_GET['usertype_updated'])) { ?>
      <p class="green-text center">User usertype updated successfully!</p>
      <?php unset($_GET['usertype_updated']); ?>
    <?php } ?>

    <?php if (isset($_GET['user_deleted'])) { ?>
      <?php if ($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin') { ?>
        <p class="green-text center">User account removed successfully!</p>
      <?php } ?>
      <?php unset($_GET['user_deleted']); ?>
    <?php } ?>

    <?php if (isset($_GET['user_removed'])) { ?>
      <p class="green-text center">User account deleted successfully!</p>
      <?php unset($_GET['user_removed']); ?>
    <?php } ?>




    <?php if ($_SESSION['usertype'] === 'admin' || $_SESSION['usertype'] === 'main admin') { ?>

          <!-- Your admin-only content here -->

          <h4 class="center grey-text">Add User</h4>


          <?php if (isset($error_message)) : ?>
              <p><?php echo $error_message; ?></p>
          <?php endif; ?>
          <form class="white" method="POST" action="users.php">
              <label for="username">Username:</label>
              <input type="text" id="username" name="username" required>
              <span class="error"><?php echo $usernameErr; ?></span><br>

              <label for="password">Password:</label>
              <input type="password" id="password" name="password" required>
              <span class="error"><?php echo $passwordErr; ?></span><br>

              <label for="email">Email:</label>
              <input type="email" id="email" name="email" required>
              <span class="error"><?php echo $emailErr; ?></span><br>

              <label for="usertype">User Type:</label>
              <select class="custom-select" id="usertype" name="usertype" required>
                  <option value="admin">Admin</option>
                  <option value="staff">Staff</option>
              </select>
              <br><br>
              <input type="submit" name="submit" value="Register" class="btn brand z-depth-0">
          </form>
      <?php } ?>
  </div>



  </div>
</body>

<?php include('templates/footer.php'); ?>

</html>
