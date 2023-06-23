<?php
session_start();

// Include the database connection settings
include('config/db_connect.php');

// Include the file containing the insertAuditLog() function
include('functions.php');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch audit log entries from the database
$query = "SELECT * FROM audit_logs ORDER BY date_created DESC";
$result = $conn->query($query);


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs | Ninja Pizza</title>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <h4 class="center grey-text">Audit Logs</h4>

    <div class="container">
        <div class="row">
            <table class="white">
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Action Made</th>
                    <th>Date & Time</th>
                </tr>

                <?php
                // Check if there are audit logs available
                if ($result->num_rows > 0) {
                    $i = 1; // Counter for row numbering
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $i++ . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['action_made'] . "</td>";
                        echo "<td>" . date("M d, Y H:i", strtotime($row['date_created'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No audit logs found</td></tr>";
                }
                ?>

            </table>
        </div>
    </div>

    <?php include('templates/footer.php'); ?>
</body>
</html>
