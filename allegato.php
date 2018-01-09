<?php

include("settings_t.php");
// POST HANDLER -->
$telegramtk=TELEGRAM_BOT; // inserire il token


    $file_id = $_GET['id'];

if ($file_id == null){
  echo "nessun allegato disponibile";
  exit;
}
$rawData = file_get_contents("https://api.telegram.org/bot".$telegramtk."/getFile?file_id=".$file_id);
$obj=json_decode($rawData, true);
//var_dump($obj);
$path=$obj["result"]["file_path"];


$pathc="https://api.telegram.org/file/bot".$telegramtk."/".$path;
//header("Location: ".$pathc);

  $filename = $pathc;
   $buffer = file_get_contents($filename);
   header("Content-Type: application/force-download");
   header("Content-Type: application/octet-stream");
   header("Content-Type: application/download");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Content-Type: application/octet-stream");
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: " . strlen($buffer));
   header("Content-Disposition: attachment; filename=$path");
   echo $buffer;

// POST HANDLER -->
?>
