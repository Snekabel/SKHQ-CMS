<?php
    function getMonthname($month)
    {
        $monthname = "";
        switch($month) {
            case "1":
                $monthname = "Januari";
                break;
            case "2":
                $monthname = "Februari";
                break;
            case "3":
                $monthname = "Mars";
                break;
            case "4":
                $monthname = "April";
                break;
            case "5":
                $monthname = "Maj";
                break;
            case "6":
                $monthname = "Juni";
                break;
            case "7":
                $monthname = "July";
                break;
            case "8":
                $monthname = "Augusti";
                break;
            case "9":
                $monthname = "Sepember";
                break;
            case "10":
                $monthname = "Oktober";
                break;
            case "11":
                $monthname = "November";
                break;
            case "12":
                $monthname = "December";
                break;
        }
        return $monthname;
    }
?>

<form method="GET" action="?page=report">
    <input type="hidden" name="page" value="report"/>
    <div>
        <select name="year">
            <?php
                $stmt = $con->prepare("SELECT DISTINCT(EXTRACT(YEAR FROM receipt.datetime)) AS year FROM receipt");
                if($stmt)
                {
                    $result = $stmt->execute();

                    $stmt->bind_result($year);

                    while ($stmt->fetch()) {
                        echo '<option value="'.$year.'">'.$year.'</option>';
                    }
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }
            ?>
        </select>
    </div>
    <div>
        <select name="month">
            <?php
                $stmt = $con->prepare("SELECT DISTINCT(EXTRACT(MONTH FROM receipt.datetime)) AS month FROM receipt");
                if($stmt)
                {
                    $result = $stmt->execute();

                    $stmt->bind_result($month);

                    while ($stmt->fetch()) {
                        $monthname = getMonthname($month);


                        echo '<option value="'.$month.'">'.$monthname.'</option>';
                    }
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }
            ?>
        </select>
    </div>
    <div>
        <input type="submit" value="submit"/>
    </div>
</form>
<hr/>
<?php
    if(array_key_exists('year', $_GET) && array_key_exists('month', $_GET))
    {
        $year = $_GET['year'];
        $month = $_GET['month'];
        $monthname = getMonthname($month);
        echo "<h1>Report for: $monthname, $year</h1>";


        $sum_amount = 0;
        ?>
        <table>
            <thead>
                <th>ID</th>
                <th>Datetime</th>
                <th>Ammmount</th>
                <th>Store</th>
                <th>User</th>
            </thead>
            <tbody>
                <?php

                    $stmt = $con->prepare("
                    SELECT r.id, r.datetime, (
                    r.amount - IFNULL((SELECT SUM(ware.price) FROM ware WHERE ware.exclude = 1 AND ware.fk_receipt = r.id), 0)
                    ) AS amount, r.store, user.username
                    FROM receipt r
                    JOIN user ON user.id=r.fk_user
                    Where YEAR(r.datetime) = ? AND MONTH(r.datetime) = ?
                    ORDER BY r.datetime");

                    if($stmt)
                    {
                        $stmt->bind_param("ss", $year, $month);

                        $result = $stmt->execute();

                        $stmt->bind_result($id, $datetime, $amount, $store, $user);

                        while ($stmt->fetch()) {
                            //error_log($id.$datetime.$amount.$store.$user);
                            echo "<tr><td>$id</td><td>$datetime</td><td>$amount</td><td>$store</td><td>$user</td></tr>";
                            $sum_amount = $sum_amount + $amount;
                        }
                    }
                    else
                    {
                        die("Mysql error, statement could not be created");
                    }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?php echo $sum_amount; ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php
    }
?>
