<?php
    echo "<script>console.log(".json_encode($_POST).")</script>";

    $target_dir = "uploads/";
    $results = "";
	$form_date = date('Y-m-d');
	$form_user = 0;
	$form_store = "";
	$form_amount = "";
	$kvitto_id = false;

    $fk_representation = null;
    if ( array_key_exists('kvitto', $_GET) ){
        $kvitto_id = $_GET['kvitto'];
    }

    //if(count($_FILES) > 0) {
    if(array_key_exists('boughtby', $_POST)) {
        if($kvitto_id)
        {
            $stmt = $con->prepare("UPDATE receipt SET datetime = ?, amount = ?, fk_user = ?, store = ? WHERE receipt.id = ?");
            $stmt->bind_param("sssss", $datetime, $amount, $fk_user, $store, $kvitto_id);
        }
        else
        {
            $stmt = $con->prepare("INSERT INTO receipt(datetime, amount, fk_user, store) VALUES(?,?,?,?)");
            $stmt->bind_param("ssss", $datetime, $amount, $fk_user, $store);
        }

        if($stmt)
        {


            $datetime = $_POST['date'];
            $amount = str_replace(',', '.', $_POST['amount']);
            $fk_user = $_POST['boughtby'];
            $store = $_POST['placeOfPurchase'];

            $result = $stmt->execute();
            echo "<script>console.log(".json_encode($result).")</script>";

            if(array_key_exists('produkter_namn', $_POST) && array_key_exists('produkter_pris', $_POST) && array_key_exists('produkter_exclude', $_POST))
            {

                $stmt_update = $con->prepare("UPDATE ware SET fk_receipt = ?, name = ?, price = ?, exclude = ? WHERE ware.id = ?");
                $stmt_update->bind_param("sssss", $fk_receipt, $name, $price, $exclude, $ware_id);

                $stmt_insert = $con->prepare("INSERT INTO ware(fk_receipt, name, price, exclude) VALUES(?,?,?,?)");
                $stmt_insert->bind_param("ssss", $fk_receipt, $name, $price, $exclude);


                if($stmt_update && $stmt_insert)
                {
                    for($i = 0; $i < count($_POST['produkter_namn']); $i++)
                    {
                        $ware_id = $_POST['produkter_id'][$i];
                        if($kvitto_id) {
                            $fk_receipt = $kvitto_id;
                        }
                        else
                        {
                            $fk_receipt = $stmt->insert_id;
                        }
                        $name = $_POST['produkter_namn'][$i];
                        $price = str_replace(',', '.', $_POST['produkter_pris'][$i]);
                        $exclude = $_POST['produkter_exclude'][$i];

                        //error_log($fk_receipt.",".$name.",".$price.",".$exclude.",".$ware_id);

                        if($ware_id != "") {
                            $result = $stmt_update->execute();
                        }
                        else
                        {
                            $result = $stmt_insert->execute();
                        }


                        echo "<script>console.log(".json_encode($result).")</script>";
                    }
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }
            }
            //$stmt->close();
        }
        else
        {
            die("Mysql error, statement could not be created");
        }
    }
    if(!empty($_FILES)) {
        //Move Uploaded file
        $filename = md5(basename($_FILES["file"]["name"])).".jpg";
        $target_file = $target_dir . $filename;
        error_log($target_file);
        $uploadOk = 0;
        $fileExtension = pathinfo(basename($_FILES["file"]["name"]),PATHINFO_EXTENSION);

        if($fileExtension == "jpg")  {
            $uploadOk = 1;
        }

        //error_log($fileExtension);
        if($uploadOk) {
            if(move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                error_log("File Uploaded");

                $stmt = $con->prepare("INSERT INTO representation(filename, fk_receipt) VALUES(?,?)");

                if($stmt)
                {
                    $stmt->bind_param("ss", $filename, $fk_receipt);

                    if($kvitto_id)
                    {
                        $fk_receipt = $kvitto_id;
                    }
                    else
                    {
                        $fk_receipt = $stmt->insert_id;
                    }

                    $result = $stmt->execute();
                    error_log(json_encode($result));
                    //$fk_representation = $stmt->insert_id;
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }

            }
            else {
                error_log("File Upload failed");
            }
        }
        else {
            error_log("File Upload failed");
        }

        echo "<script>console.log(".json_encode($results).")</script>";
    }

    if($kvitto_id)
    {
        $get_kvitto_stmt = $con->prepare("
        SELECT receipt.id, receipt.datetime, receipt.amount, user.username, receipt.store, user.id AS user_id
        FROM receipt
        JOIN user ON user.id=receipt.fk_user
        WHERE receipt.id = ?
        ORDER BY receipt.id ASC
        ");

        if ($get_kvitto_stmt) {
            $get_kvitto_stmt->bind_param("s", $kvitto_id);

            $result = $get_kvitto_stmt->execute();
            $get_kvitto_stmt->bind_result($id, $datetime, $amount, $username, $store, $user_id);

            while ($get_kvitto_stmt->fetch()) {
                $tempdate = date_parse($datetime);
                $form_date = str_pad($tempdate['year'], 4, "0", STR_PAD_LEFT).'-'.str_pad($tempdate['month'], 2, "0", STR_PAD_LEFT).'-'.str_pad($tempdate['day'], 2, "0", STR_PAD_LEFT);
                $form_user = $user_id;
                $form_store = $store;
                $form_amount = $amount;
            }
        }
        else
        {
            die("Mysql error, statement could not be created");
        }
    }
?>
<script src="./script/upload.js"></script>
<style>
    .thumb {
        width: 150px;
    }
</style>
<h2>Ladda upp utlägg för SK HQ</h2>
<form method="POST" enctype="multipart/form-data">
    <table>
        <thead>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label for="date">Datum för köp</label>
                </td>
                <td>
                    <input type="date" id="date" name="date" value="<?php echo $form_date ?>" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="boughtby">Köpt av</label>
                </td>
                <td>
                    <select id="boughtby" name="boughtby">
                        <?php
                            $stmt = $con->prepare("SELECT username,id FROM user");

                            if($stmt)
                            {
                                $result = $stmt->execute();

                                $stmt->bind_result($username, $id);
                                //$stmt->close();
                                while ($stmt->fetch()) {
                                    $selected = "";
                                    if($form_user > 0)
                                    {
                                        if($form_user == $id) { $selected = "selected"; }
                                    }
                                    else
                                    {
                                        if($_SESSION['user_id'] == $id) { $selected = "selected"; }
                                    }

                                    echo '<option value="'.$id.'" '.$selected.'>'.$username.'</option>';
                                }
                            }
                            else
                            {
                                die("Mysql error, statement could not be created");
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="placeOfPurchase">Butik</label>
                </td>
                <td>
                    <input id="placeOfPurchase" type="text" name="placeOfPurchase" placeholder="Köpt på..." value="<?php echo $form_store ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="amount">Summa</label>
                </td>
                <td>
                    <input id="amount" type="text" name="amount" placeholder="0" value="<?php echo $form_amount ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <label>Köpta saker</label>
                </td>
                <td>
                    <table onkeypress="checkEnter(event)">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Pris</th>
                                <th>Exkludera</th>
                            </tr>
                        </thead>
                        <tbody id="products">
							<?php
								$stmt = $con->prepare("
								SELECT ware.id, ware.name, ware.price, ware.exclude
								FROM ware
								WHERE fk_receipt = ?
								");

								if($stmt)
								{
									$stmt->bind_param("s", $kvitto_id);
									$result = $stmt->execute();

									$stmt->bind_result($id, $name, $price, $exclude);
									//$stmt->close();
									while ($stmt->fetch()) {
										echo '<tr><td><input type="hidden" name="produkter_id[]" value="'.$id.'"/><input placeholder="Produkt" name="produkter_namn[]" value="'.$name.'"></td><td><input placeholder="Pris" name="produkter_pris[]" value="'.$price.'"></td><td><input type="checkbox" onclick="setCheck(this)" '.($exclude ? "checked=checked":"").'><input type="hidden" name="produkter_exclude[]" value="'.$exclude.'"></td></tr>';
										//echo '<tr><td>Vara</td><td>'.$name.'</td><td>'.$price.'kr</td><td>'.$exclude.'</td></tr>';
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
                                <td colspan="3">
                                    <input type="button" onclick="addNew()" value="Add new"/>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <?php
            if($kvitto_id) {
                $stmt = $con->prepare("SELECT representation.id, representation.filename FROM representation WHERE representation.fk_receipt = ?");

                if($stmt)
                {
                    $stmt->bind_param("s", $kvitto_id);

                    $result = $stmt->execute();

                    $stmt->bind_result($id, $filename);

                    while ($stmt->fetch()) {
                        echo '<tr><td><span>'.$id.'</span></td><td><a href="uploads/'.$filename.'" target="_BLANK"><img src="uploads/'.$filename.'" class="thumb"/></a></td></tr>';
                    }
                }
                else
                {
                    die("Mysql error, statement could not be created");
                }
            }
            ?>
            <tr>
                <td>
                    <label for="fileupload">Kvitto representation</label>
                </td>
                <td>
                    <input id="fileupload" type="file" name="file">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Submit" name="submit">
                </td>
            </tr>
        <tbody>
    </table>


</form>