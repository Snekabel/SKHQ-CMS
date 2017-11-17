<?php

?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/style.css"></link>
    <script src="./script/main.js"></script>
</head>
<body>
    <div class="header">
        <div class="logo">SK HQ</div>
        <div class="links">
            <a href="?page=home">Home</a>
            <a href="?page=upload">Upload</a>
            <a href="?page=kvitton">Kvitton</a>
            <a href="?page=report">Reports</a>
        </div>
    </div>
    <div class="content">
        <?php
            if(array_key_exists('page', $_GET)) {
                switch ($_GET['page']) {
                    case "upload":
                        $page = "upload";
                        break;
                    case "report":
                        $page = "report";
                        break;
                    case "home":
                        $page = "home";
                        break;
                    case "kvitton":
                        $page = "kvitton";
                        break;
                }
            }
            else
            {
                $page = "home";
            }

            require_once("./".$page.".php");
        ?>
    </div>
    <div class="footer">
        Copyright 2017 Snekabel Corperation
    </div>
</body>
</html>
<?php
exit;