<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PASSWORD UPDATE</title>
</head>
<body>
    
<?php 
require("connection.php");

if(isset($_GET['email']) && isset($_GET['reset_token'])){
    date_default_timezone_set('Asia/kolkata');
    $date = date("Y-m-d");
    $query = "SELECT * FROM `accounts` WHERE `email` = '$_GET[email]' AND `resettoken` = '$_GET[reset_token]' AND `resettokenexpire` = '$date' ";
    $result = mysqli_query($con, $query);
    
    if($result && mysqli_num_rows($result) == 1){
        echo "
        <form action='' method='POST'>
            <h3>Create new password</h3>
            <input type='password' placeholder='New Password' name='password' required>
            <button type='submit' name='updatepassword'>UPDATE</button>
            <input type='hidden' name='email' value='$_GET[email]'>
        </form>
        ";
    } else {
        echo "
        <script>
        alert('Invalid link or token');
        window.location.href = 'index.php';
        </script>
        ";
    }
}
?>

<?php
if(isset($_POST['updatepassword'])){
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $update = "UPDATE `accounts` SET `password`='$pass', `resettoken`=NULL, `resettokenexpire`=NULL WHERE `email` = '$_POST[email]'";
    
    if(mysqli_query($con, $update)){
        echo "
        <script>
        alert('PASSWORD SUCCESSFULLY UPDATED !!');
        window.location.href = 'index.php';
        </script>
        ";
    } else {
        echo "
        <script>
        alert('Server down, try again later');
        window.location.href = 'index.php';
        </script>
        ";
    }
}
?>

</body>
</html>
