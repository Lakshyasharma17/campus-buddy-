<?php 
require("connection.php");

if (isset($_GET['email']) && isset($_GET['verification_code'])) {
    // Sanitize the incoming GET parameters
    $email = mysqli_real_escape_string($con, $_GET['email']);
    $v_code = mysqli_real_escape_string($con, $_GET['verification_code']);

    // Prepare the SQL statement to prevent SQL injection
    $query = "SELECT * FROM `accounts` WHERE `email` = ? AND `verification_code` = ?";
    $stmt = mysqli_prepare($con, $query);

    // Bind the parameters to the query
    mysqli_stmt_bind_param($stmt, 'ss', $email, $v_code);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Get the result
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $result_fetch = mysqli_fetch_assoc($result);

            // Check if email is already verified
            if ($result_fetch['is_verified'] == 0) {
                // Prepare the update statement
                $update = "UPDATE `accounts` SET `is_verified` = 1 WHERE `email` = ?";
                $update_stmt = mysqli_prepare($con, $update);

                // Bind the email parameter to the update query
                mysqli_stmt_bind_param($update_stmt, 's', $email);

                // Execute the update query
                if (mysqli_stmt_execute($update_stmt)) {
                    // Email verification success
                    echo "
                    <script>
                    alert('Email verified successfully!');
                    window.location.href = 'index.php';
                    </script>
                    ";
                } else {
                    // Error in update query
                    echo "Error in update query: " . mysqli_error($con);
                    echo "
                    <script>
                    alert('Cannot run update query. Please try again later.');
                    window.location.href = 'index.php';
                    </script>
                    ";
                }
            } else {
                // Case when email is already verified
                echo "
                <script>
                alert('Email is already verified.');
                window.location.href = 'index.php';
                </script>
                ";
            }
        } else {
            // No matching records found
            echo "
            <script>
            alert('Invalid link or token.');
            window.location.href = 'index.php';
            </script>
            ";
        }
    } else {
        // If the query fails
        echo "Query failed: " . mysqli_error($con);
        echo "
        <script>
        alert('Cannot run query. Please try again later.');
        window.location.href = 'index.php';
        </script>
        ";
    }
} else {
    // If email or v_code are not set
    echo "
    <script>
    alert('Invalid request.');
    window.location.href = 'index.php';
    </script>
    ";
}
?>
