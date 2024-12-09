<?php 
require('connection.php');
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($email, $v_code) {
    require('PHPMailer/PHPMailer.php');
    require('PHPMailer/SMTP.php');
    require('PHPMailer/Exception.php');

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'hinddharohar@gmail.com';                
        $mail->Password   = 'ooul uhwd ddim wgpz';                   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                    

        //Recipients
        $mail->setFrom('hinddharohar@gmail.com', 'HIND DHAROHAR');
        $mail->addAddress($email); 

        //Content
        $mail->isHTML(true);                                  
        $mail->Subject = 'HIND DHAROHAR EMAIL VERIFICATION';
        $mail->Body    = "We have received a request to register an account on our website. If you made this request, 
                          please click the link below to verify your email:<br><br>
                          <a href='https://localhost/my_project/parishkar/verify.php?email=$email&verification_code=$v_code'>
                          Verify your email
                          </a> <br><br>

                          From Team Groks<br>HIND DHAROHAR
                          ";
    
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

# --- Login logic ---
if (isset($_POST['login'])) {
    $query = "SELECT * FROM `accounts` WHERE `email` = '$_POST[email_username]' OR `username` = '$_POST[email_username]'";
    $result = mysqli_query($con, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $result_fetch = mysqli_fetch_assoc($result);
            
            if ($result_fetch['is_verified'] == 1) {
                if (password_verify($_POST['password'], $result_fetch['password'])) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $result_fetch['username'];
                    header("location: index.php");
                } else {
                    echo "
                        <script>
                            alert('Incorrect Password');
                            window.location.href = 'index.php';
                        </script>
                    ";
                }
            } else {
                echo "
                    <script>
                        alert('Email not verified');
                        window.location.href = 'index.php';
                    </script>
                ";
            }
        } else {
            echo "
                <script>
                    alert('Email or Username not registered');
                    window.location.href = 'index.php';
                </script>
            ";
        }
    } else {
        echo "
            <script>
                alert('Cannot run query');
                window.location.href = 'index.php';
            </script>
        ";
    }
}

# --- Registration logic ---
if (isset($_POST['register'])) {
    $user_exist_query = "SELECT * FROM `accounts` WHERE `username` = '$_POST[username]' OR `email` = '$_POST[email]'";
    $result = mysqli_query($con, $user_exist_query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) { 
            $result_fetch = mysqli_fetch_assoc($result);
            
            if ($result_fetch['username'] == $_POST['username']) {
                echo "
                    <script>
                        alert('Username \"$_POST[username]\" is already taken.');
                        window.location.href = 'index.php';
                    </script>
                ";
            } else {
                echo "
                    <script>
                        alert('Email \"$_POST[email]\" is already registered.');
                        window.location.href = 'index.php';
                    </script>
                ";
            }
        } else {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $v_code = bin2hex(random_bytes(16));
            $user_type = $_POST['user']; // Capture the "user" radio button value (either Student or Faculty)

            $query = "INSERT INTO `accounts`(`full_name`, `username`, `email`, `password`, `verification_code`, `is_verified`, `user`) 
                      VALUES ('$_POST[fullname]', '$_POST[username]', '$_POST[email]', '$password', '$v_code', '0', '$user_type')";

            if (mysqli_query($con, $query) && sendMail($_POST['email'], $v_code)) {
                echo "
                    <script>
                        alert('Registration successful! Please verify your email.');
                        window.location.href = 'index.php';
                    </script>
                ";
            } else {
                echo "
                    <script>
                        alert('Cannot run query');
                        window.location.href = 'index.php';
                    </script>
                ";
            }
        }
    } else {
        echo "
            <script>
                alert('Cannot run query');
                window.location.href = 'index.php';
            </script>
        ";
    }
}

# --- Password reset logic (optional) ---
if (isset($_POST['send-reset-link'])) {
    $email = $_POST['email'];
    $query = "SELECT * FROM `accounts` WHERE `email` = '$email'";
    $result = mysqli_query($con, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $v_code = bin2hex(random_bytes(16));
            $update_query = "UPDATE `accounts` SET `verification_code` = '$v_code' WHERE `email` = '$email'";
            
            if (mysqli_query($con, $update_query) && sendMail($email, $v_code)) {
                echo "
                    <script>
                        alert('Password reset link has been sent to your email.');
                        window.location.href = 'index.php';
                    </script>
                ";
            } else {
                echo "
                    <script>
                        alert('Unable to send reset link. Please try again later.');
                        window.location.href = 'index.php';
                    </script>
                ";
            }
        } else {
            echo "
                <script>
                    alert('Email not registered.');
                    window.location.href = 'index.php';
                </script>
            ";
        }
    } else {
        echo "
            <script>
                alert('Cannot run query');
                window.location.href = 'index.php';
            </script>
        ";
    }
}
?>
