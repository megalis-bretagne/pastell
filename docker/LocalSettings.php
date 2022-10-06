<?php

/* LocalSettings.php recopier à la racine par gitlab-ci */

define("SITE_BASE", "https://localhost");


define("BD_DSN_TEST", "mysql:dbname=pastell_test;host=mysql;port=3306;charset=utf8");
define("BD_DBNAME_TEST", "pastell_test");
define("BD_USER_TEST", "user");
define("BD_PASS_TEST", "user");

/* Nécessaire pour démarrer le démon dans le docker */
define("BD_DSN", "mysql:dbname=pastell_test;host=mysql;port=3306;charset=utf8mb4");
define("BD_DBNAME", "pastell_test");
define("BD_USER", "user");
define("BD_PASS", "user");
