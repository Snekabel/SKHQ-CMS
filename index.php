<?php
session_start();

    $result = require_once("./PasswordStorage.php");
    if(!$result) die("Required files needed");
    $result = require_once("./connect.php");
    if(!$result) die("Required files needed");

    function validateToken($token) {
        if($token == null) {
            return false;
        }
        return true;
    }

    $token = null;
    $pws = new PasswordStorage();

    if(array_key_exists('register', $_GET))
    {
        if(array_key_exists('username', $_POST) && array_key_exists('password', $_POST))
        {
            echo "<div>Trying to register user: ".$_POST['username']."</div>";
            $stmt = $con->prepare("INSERT INTO user(username, hash) VALUES(?,?)");


            if($stmt)
            {
                $stmt->bind_param("ss", $uname, $hash);

                $hash = $pws->create_hash($_POST['password']);
                echo "<div>".$hash."</div>";
                $uname = $_POST['username'];

                $result = $stmt->execute();
                //$stmt->close();
            }
            else
            {
                die("Mysql error, statement could not be created");
            }
        }
        else
        {
            echo "<div>Username and Password needed to register</div>";
            exit;
        }
    }
    else
    {
        if(array_key_exists('token', $_SESSION)) $token = $_SESSION['token'];
        //echo "<div>Token: ".$token."</div>";
        if(!validateToken($token))
        {
            if(array_key_exists('username', $_POST) && array_key_exists('password', $_POST))
            {
                $hash = "";
                $stmt = $con->prepare("SELECT hash, token, id FROM user WHERE username = ?");
                if($stmt)
                {
                    $stmt->bind_param("s", $uname);
                    $uname = $_POST['username'];

                    $result = $stmt->execute();

                    $stmt->bind_result($hash, $token, $id);

                    while ($stmt->fetch()) {
                        echo "<div>Uname: ".$uname.", Hash from DB: ".$hash.", Token: ".$token."</div>";
                        if($pws->verify_password($_POST['password'], $hash)) {
                            echo "<div>Login Accepted</div>";
                            $_SESSION['token'] = "lol";
                            $_SESSION['user_id'] = $id;
                            /* header('Location: ?page=home'); */
                            ?> <script>window.location.href = "?page=home"</script><?php
                            exit;
                        }
                        else
                        {
                            echo "<div>Login failed</div>";
                            exit;
                        }
                    }
                    echo "User not found or password incorrect<br/>";
                    echo '<a href=".">Try Again</a>';
                    //$stmt->close();
                    exit;
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }
            }
            else
            {
                ?>
                    <div>Login</div>
                    <div>
                    <form method="POST">
                        <div><label>Username <input type="text" name="username"/></label></div>
                        <div><label>Password <input type="password" name="password"/></label></div>
                        <input type="submit"/>
                    </form>
                    </div>
                    <div>Register</div>
                    <form method="POST" action="?register=1">
                        <div><label>Username <input type="text" name="username"/></label></div>
                        <div><label>Password <input type="password" name="password"/></label></div>
                        <input type="submit"/>
                    </form>
                    </div>
                <?php
                exit;
            }
        }
        else
        {
            require("./cms.php");
        }
    }

    echo "All seems ok";


    //$con->close();
?>