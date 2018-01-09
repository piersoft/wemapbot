<?php
//modulo delle KEYs per funzionamento dei bot (da non committare)

//Telegram

define('TELEGRAM_BOT','');
define('BOT_WEBHOOK', 'https://www.xxxxxxxxxxxx/start.php');
define('LOG_FILE', 'db/telegram.log');

// Your csv database
$csv_path=dirname(__FILE__).'/./db/map_data.txt';
$db_path=dirname(__FILE__).'/./db/db.sqlite';
define ('DB_NAME', "sqlite:". $db_path);
define('DB_TABLE',"user");
define('DB_TABLE_GEO',"segnalazioni");
define('DB_CONF', 0666);
define('DB_ERR', "errore database SQLITE");

?>
