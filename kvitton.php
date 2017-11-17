<?php
     $filename = null;
	 
?>
<style>
    .kvittoList, .openedKvitto {
        width: 50%;
        float: left;
    }

    .representation {
        max-width: 100%;
    }

    .edit {
        color: blue;
        font-weight: bold;
        font-style: underline;
        font-family: Arial;
    }
</style>
<script>
    function openKvitto(id) {
        //window.location.href = ("?page=kvitton&kvitto="+$id);
        console.log("Opening ", id, window.location.href);
        window.location.href = ("?page=upload&kvitto="+id);
    }
</script>
<div class="kvittoList">
<table>
    <thead>
        <tr>
            <th>ID</th><th>Datetime</th><th>Sum</th><th>User</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $stmt = $con->prepare("
        SELECT receipt.id, receipt.datetime, receipt.amount, user.username
        FROM receipt
        JOIN user ON user.id=receipt.fk_user
        ORDER BY receipt.id ASC
        ");

        if($stmt)
        {
            $result = $stmt->execute();

            $stmt->bind_result($id, $datetime, $sum, $username);
            //$stmt->close();
            while ($stmt->fetch()) {
                echo '<tr><td>'.$id.'</td><td>'.$datetime.'</td><td>'.$sum.'</td><td>'.$username.'</td><td onClick="openKvitto('.$id.')" class="edit">E</td></tr>';
            }
        }
        else
        {
            die("Mysql error, statement could not be created");
        }
    ?>
    </tbody>
</table>
</div>
<div class="openedKvitto">
    <table>
        <tbody>
            <?php
                if(array_key_exists('kvitto', $_GET))
                {
                    $id = $_GET['kvitto'];

                    $stmt = $con->prepare("
                    SELECT receipt.id, receipt.datetime, receipt.amount, user.username, representation.filename
                    FROM receipt
                    JOIN user ON user.id=receipt.fk_user
                    LEFT JOIN representation ON representation.fk_receipt=receipt.id
                    WHERE receipt.id = ?
                    ");

                    if($stmt)
                    {
                        $stmt->bind_param("s", $id);
                        $result = $stmt->execute();

                        $stmt->bind_result($id, $datetime, $sum, $username, $representation_filename);
                        //$stmt->close();
                        while ($stmt->fetch()) {
                            echo '<tr><td>ID</td><td>'.$id.'</td></tr>';
                            echo '<tr><td>Datetime</td><td>'.$datetime.'</td></tr>';
                            echo '<tr><td>Sum</td><td>'.$sum.'</td></tr>';
                            echo '<tr><td>User</td><td>'.$username.'</td></tr>';
                            $filename = $representation_filename;
                        }
                    }
                    else
                    {
                        die("Mysql error, statement could not be created");
                    }

                    $stmt = $con->prepare("
                    SELECT ware.id, ware.name, ware.price, ware.exclude
                    FROM ware
                    WHERE fk_receipt = ?
                    ");

                    if($stmt)
                    {
                        $stmt->bind_param("s", $id);
                        $result = $stmt->execute();

                        $stmt->bind_result($id, $name, $price, $exclude);
                        //$stmt->close();
                        while ($stmt->fetch()) {
                            echo '<tr><td>Vara</td><td>'.$name.'</td><td>'.$price.'kr</td><td>'.$exclude.'</td></tr>';
                        }
                    }
                    else
                    {
                      die("Mysql error, statement could not be created");
                    }
                }
            ?>
        </tbody>
    </table>
    <?php
        if($filename != null) {
            echo '<a href="uploads/'.$filename.'" target="_BLANK"><img class="representation" src="uploads/'.$filename.'"/></a>';
        }
    ?>
</div>
