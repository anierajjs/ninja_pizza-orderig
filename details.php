<?php
session_start();
include('config/db_connect.php');

// Function to insert an audit log entry
function insertAuditLog($username, $action) {
    global $conn;

    $username = mysqli_real_escape_string($conn, $username);
    $action = mysqli_real_escape_string($conn, $action);
    $query = "INSERT INTO audit_logs (username, action_made, date_created) VALUES ('$username', '$action', NOW())";
    mysqli_query($conn, $query);
}

if (isset($_POST['delete'])) {
    $id_to_delete = mysqli_real_escape_string($conn, $_POST['id_to_delete']);

    // Retrieve the pizza details before deleting it
    $getPizzaQuery = "SELECT title, email FROM pizzas WHERE id = $id_to_delete";
    $getPizzaResult = mysqli_query($conn, $getPizzaQuery);

    if ($getPizzaResult && mysqli_num_rows($getPizzaResult) > 0) {
        $row = mysqli_fetch_assoc($getPizzaResult);
        $pizzaTitle = $row['title'];
        $pizzaEmail = $row['email'];

        // Delete the pizza from the database
        $sql = "DELETE FROM pizzas WHERE id = $id_to_delete";

        if (mysqli_query($conn, $sql)) {
            // Deletion successful, redirect to a success page or display a success message
            header("Location: index.php?pizza_deleted=true");

            // Insert audit log entry for the successful pizza deletion
            $username = $_SESSION['username'];
            $action = "Deleted a pizza: $pizzaTitle, created by $pizzaEmail";
            insertAuditLog($username, $action);

            exit;
        } else {
            echo 'query error: ' . mysqli_error($conn);
        }
    } else {
        // Handle the case when the pizza is not found
        echo 'Pizza not found.';
    }
}


	// check GET request id param
	if(isset($_GET['id'])){

		// escape sql chars
		$id = mysqli_real_escape_string($conn, $_GET['id']);

		// make sql
		$sql = "SELECT * FROM pizzas WHERE id = $id";

		// get the query result
		$result = mysqli_query($conn, $sql);

		// fetch result in array format
		$pizza = mysqli_fetch_assoc($result);

		mysqli_free_result($result);
		mysqli_close($conn);

	}

?>

<!DOCTYPE html>
<html>

	<?php include('templates/header.php'); ?>

	<div class="container center grey-text">
		<?php if($pizza): ?>
			<h4><?php echo $pizza['title']; ?></h4>
			<p>Created by <?php echo $pizza['email']; ?></p>
			<p><?php echo date($pizza['created_at']); ?></p>
			<h5>Ingredients:</h5>
			<p><?php echo $pizza['ingredients']; ?></p>

			<!-- DELETE FORM -->
			<form action="details.php" method="POST">
				<input type="hidden" name="id_to_delete" value="<?php echo $pizza['id']; ?>">
				<input type="submit" name="delete" value="Delete" class="btn brand z-depth-0">
			</form>

		<?php else: ?>
			<h5>No such pizza exists.</h5>
		<?php endif ?>
	</div>

	<?php include('templates/footer.php'); ?>

</html>
