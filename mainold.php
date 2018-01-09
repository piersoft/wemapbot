<?php
/**
* Telegram Bot example for mapping "ViaLiberabot".
* @author Francesco Piero Paolicelli
*/
include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//$data=new getdata();
	// Instances the class
	$db = new PDO(DB_NAME);

	/* If you need to manually take some parameters
	*  $result = $telegram->getData();
	*  $text = $result["message"] ["text"];
	*  $chat_id = $result["message"] ["chat"]["id"];
	*/

	$first_name=$update["message"]["from"]["first_name"];
	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];
	$username=$update["message"]["from"]["username"];
	$this->shell($username,$telegram, $db,$first_name,$text,$chat_id,$user_id,$location,$reply_to_msg);
	//$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($username,$telegram,$db,$first_name,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	$csv_path=dirname(__FILE__).'/./db/map_data.txt';
	$db_path=dirname(__FILE__).'/./db/db.sqlite';

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start" || $text == "info" || $text == "©️info") {
		$reply = "Benvenuto ".$first_name.". Questo Bot è stato realizzato da @piersoft con la collaborazione di @fedelecongedo e permette di mappare oggetti durante processi partecipati (Mappathon).\nI dati risultati sono visualizzati su una mappa realtime e sono utilizzabili scaricando il file di testo www.piersoft.it/wemapbot/db/map_data.txt in licenza CC0 (pubblico dominio). L'autore non è responsabile per l'uso improprio di questo strumento e dei contenuti degli utenti.\nLa mappatura è abilitata solo per utenti che hanno \"username\" (univoci su Telegram tramite la sua sezione Impostazioni) e vengono registrati e visualizzati pubblicamente su mappa con licenza CC0 (pubblico dominio).\nPer partecipare bisogna compilare il seguente form: https://goo.gl/forms/ErTQJ9iH14l4PBGy2. \n\nLa geocodifca dei dati avviene grazie al database Nominatim di openStreeMap con licenza oDBL";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);


		$forcehide=$telegram->buildKeyBoardHide(true);
		$content = array('chat_id' => $chat_id, 'text' => "", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);
		$bot_request_message=$telegram->sendMessage($content);

		$log=$today. ",new chat started," .$chat_id. "\n";
		$this->create_keyboard($telegram,$chat_id);
		exit;
		{
			elseif ($text == "/location" || $text == "🌐posizione") {

		$option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
											);
	// Create a permanent custom keyboard
	$keyb = $telegram->buildKeyBoard($option, $onetime=false);
	$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Attiva la localizzazione sul tuo smartphone / Turn on your GPS");
	$telegram->sendMessage($content);
	exit;
	}else if ($text == "/istruzioni" || $text == "istruzioni" || $text == "❓istruzioni") {

		$img = curl_file_create('istruzioni.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);
		$content = array('chat_id' => $chat_id, 'text' => "[Immagine realizzata da Alessandro Ghezzer]");
		$telegram->sendMessage($content);
		$content = array('chat_id' => $chat_id, 'text' => "Dopo che hai inviato il tuo FILE puoi aggiungere un testo.\nDevi digitare t:numsegnalazione:testo\nper esempio <b>t:123:macchina in terza fila.</b>\nPuò modificare il testo solo lo username che ha fatto la segnalazione.",'parse_mode'=>"HTML");
		$telegram->sendMessage($content);
		$log=$today. ",istruzioni," .$chat_id. "\n";

	}elseif ($text=="update"){
		$statement = "DELETE FROM ". DB_TABLE_GEO ." WHERE username =' '";
		$db->exec($statement);
		exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');

	}
		elseif ($text=="aggiorna" || $text =="/aggiorna" || $text =="❌aggiorna" )
			{

				$reply = "Per aggiornare una segnalazione digita a:numerosegnalazione, esempio a:699";
				$content = array('chat_id' => $chat_id, 'text' => $reply);
				$telegram->sendMessage($content);

			}
			elseif (strpos($text,'a:') !== false) {
				$text=str_replace("a:","",$text);
				$text=str_replace(" ","",$text);

				if ($username==""){
					$content = array('chat_id' => $chat_id, 'text' => "Devi obbligatoriamente impostare il tuo username nelle impostazioni di Telegram",'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$log=$today.",".$todayd. ",nousernameset," .$chat_id.",".$username.",".$user_id."\n";
					file_put_contents('db/telegram.log', $log, FILE_APPEND | LOCK_EX);
					$this->create_keyboard($telegram,$chat_id);
					exit;
				}else
				{
					$text1=strtoupper($username);
					$homepage="";
					// il GDRIVEKEY2 è l'ID per un google sheet dove c'è l'elenco degli username abilitati.
					$url ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(D)%20LIKE%20%27%25".$text1;
					$url .="%25%27%20&key=".GDRIVEKEY."&gid=".GDRIVEGID1;
				//  $url="https://docs.google.com/spreadsheets/d/1r-A2a47HKuy7dUx4YreSmJxI4KQ-fc4v97J-xt5qqqU/gviz/tq?tqx=out:csv&tq=SELECT+*+WHERE+B+LIKE+%27%25VENERD%25%27+AND+A+LIKE+%27%251%25%27";
					$csv = array_map('str_getcsv', file($url));
					$count = 0;
					foreach($csv as $data=>$csv1){
						$count = $count+1;
					}
						if ($count >1)
							{
			//	$user_id = "193317621";
					$statement = "UPDATE ".DB_TABLE_GEO ." SET aggiornata='gestita' WHERE bot_request_message ='".$text."'";
		//	print_r($reply_to_msg['message_id']);
					$db->exec($statement);
					$reply = "Segnalazione n° ".$text." è stata aggiornata";
					$content = array('chat_id' => $chat_id, 'text' => $reply);
					$telegram->sendMessage($content);
					exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');
					$log=$today. ",segnalazione aggiornata," .$chat_id. "\n";
					$db1 = new SQLite3($db_path);
					$q = "SELECT user,username FROM ".DB_TABLE_GEO ." WHERE bot_request_message='".$text."'";
					$result=	$db1->query($q);
					$row = array();
					$i=0;

					while($res = $result->fetchArray(SQLITE3_ASSOC))
							{

									if(!isset($res['user'])) continue;

									 $row[$i]['user'] = $res['user'];
									 $row[$i]['username'] = $res['username'];

									 $i++;
							 }
							 $content = array('chat_id' => $row[0]['user'], 'text' => $row[$i]['username'].", la tua segnalazione è stata presa in gestione, ti ringraziamo.",'disable_web_page_preview'=>true);
						 	 $telegram->sendMessage($content);
				}else{
					$content = array('chat_id' => $chat_id, 'text' => $username.", non risulti essere un utente autorizzato ad aggiornare le segnalazioni.",'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$this->create_keyboard($telegram,$chat_id);
					exit;
				}

			}

		}elseif (strpos($text,'t:') !== false || strpos($text,'T:') !== false) {
			//$text=str_replace("/t:",":",$text);
			$text=str_replace("t:",":",$text);
			$text=str_replace("T:",":",$text);
			function extractString($string, $start, $end) {
					$string = " ".$string;
					$ini = strpos($string, $start);
					if ($ini == 0) return "";
					$ini += strlen($start);
					$len = strpos($string, $end, $ini) - $ini;
					return substr($string, $ini, $len);
			}
			//$testo=$_POST["q"];
			//$testo="bm%11/01/2016?5-11";
			$id=extractString($text,":",":");
			$text=str_replace($id,"",$text);
			$text=str_replace(":","",$text);
				$text=str_replace(",","",$text);
			$statement = "UPDATE ".DB_TABLE_GEO ." SET text='".$text."' WHERE bot_request_message ='".$id."' AND username='".$username."'";
//	print_r($reply_to_msg['message_id']);
			$db->exec($statement);
			$reply = "Segnalazione n° ".$id." è stata aggiornata, solo se sei stato tu l'utente segnalante";
			$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
			exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');
			$log=$today. ",segnalazione aggiornata," .$chat_id. "\n";

	}
		//gestione segnalazioni georiferite
		elseif($location!=null)

		{
			if ($username==""){
				$content = array('chat_id' => $chat_id, 'text' => "Devi obbligatoriamente impostare il tuo username nelle impostazioni di Telegram",'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today.",".$todayd. ",nousernameset," .$chat_id.",".$username.",".$user_id."\n";
				file_put_contents('db/telegram.log', $log, FILE_APPEND | LOCK_EX);
				$this->create_keyboard($telegram,$chat_id);
				exit;
			}else
			{
				$text=strtoupper($username);
				$homepage="";
				// il GDRIVEKEY2 è l'ID per un google sheet dove c'è l'elenco degli username abilitati.
				$url ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20upper(D)%20LIKE%20%27%25".$text;
				$url .="%25%27%20&key=".GDRIVEKEY."&gid=".GDRIVEGID;
			//  $url="https://docs.google.com/spreadsheets/d/1r-A2a47HKuy7dUx4YreSmJxI4KQ-fc4v97J-xt5qqqU/gviz/tq?tqx=out:csv&tq=SELECT+*+WHERE+B+LIKE+%27%25VENERD%25%27+AND+A+LIKE+%27%251%25%27";
				$csv = array_map('str_getcsv', file($url));
				$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
					if ($count >1)
						{
							$this->location_manager($username,$db,$telegram,$user_id,$chat_id,$location);
							exit;
							}else{
								$content = array('chat_id' => $chat_id, 'text' => $username.", non risulti essere un utente autorizzato ad inviare le segnalazioni. Compila questo form: https://goo.gl/forms/29j5UtOx3MUkxoRG2.",'disable_web_page_preview'=>true);
								$telegram->sendMessage($content);
								$this->create_keyboard($telegram,$chat_id);
								exit;
							}

			}


		}
//elseif($text !=null)

else //($reply_to_msg != NULL)
{
if ($reply_to_msg != NULL){

	$response=$telegram->getData();

	$type=$response["message"]["video"]["file_id"];
	$text =$response["message"]["text"];
	$risposta="";
	$file_name="";
	$file_path="";
	$file_name="";


if ($type !=NULL) {
$file_id=$type;
$text="video allegato";
$risposta="ID dell'allegato:".$file_id."\n";
//$content = array('chat_id' => $chat_id, 'text' => "per inviare un allegato devi cliccare \xF0\x9F\x93\x8E e poi File");
//$telegram->sendMessage($content);
exit;
}

$file_id=$response["message"]["photo"][2]["file_id"];

if ($file_id !=NULL) {

$telegramtk=TELEGRAM_BOT; // inserire il token
$rawData = file_get_contents("https://api.telegram.org/bot".$telegramtk."/getFile?file_id=".$file_id);
$obj=json_decode($rawData, true);
$file_path=$obj["result"]["file_path"];
$caption=$response["message"]["caption"];
if ($caption != NULL) $text=$caption;
$risposta="ID dell'allegato: ".$file_id."\n";
/*
$content = array('chat_id' => $chat_id, 'text' => "per inviare un allegato devi cliccare \xF0\x9F\x93\x8E e poi File. \nQuesto perchè la risoluzione delle foto inviate direttamente dal rullino\nè molto bassa, mentre inviando la stessa foto come FILE\n viene mantenuta la risoluzione originale dello scatto.\nRimanda la tua posizione e riprova per cortesia");
$telegram->sendMessage($content);
$statement = "DELETE FROM ". DB_TABLE_GEO ." where bot_request_message = '" . $reply_to_msg['message_id'] . "'";
$db->exec($statement);
exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');

$this->create_keyboard($telegram,$chat_id);
exit;*/
}
$typed=$response["message"]["document"]["file_id"];

if ($typed !=NULL){
$file_id=$typed;
$file_name=$response["message"]["document"]["file_name"];
$text="documento: ".$file_name." allegato";
$risposta="ID dell'allegato:".$file_id."\n";

}

$typev=$response["message"]["voice"]["file_id"];
if ($typev !=NULL){
$file_id=$typev;
$text="audio allegato";
$risposta="ID dell'allegato:".$file_id."\n";
//$content = array('chat_id' => $chat_id, 'text' => "per inviare un allegato devi cliccare \xF0\x9F\x93\x8E e poi File");
//$telegram->sendMessage($content);
exit;
}
$csv_path='db/map_data.txt';
$db_path='db/db.sqlite';
//echo $db_path;
$username=$response["message"]["from"]["username"];
$first_name=$response["message"]["from"]["first_name"];

$db1 = new SQLite3($db_path);
$q = "SELECT lat,lng FROM ".DB_TABLE_GEO ." WHERE bot_request_message='".$reply_to_msg['message_id']."'";
$result=	$db1->query($q);
$row = array();
$i=0;

while($res = $result->fetchArray(SQLITE3_ASSOC))
		{

				if(!isset($res['lat'])) continue;

				 $row[$i]['lat'] = $res['lat'];
				 $row[$i]['lng'] = $res['lng'];
				 $i++;
		 }

		 //inserisce la segnalazione nel DB delle segnalazioni georiferite
			 $statement = "UPDATE ".DB_TABLE_GEO ." SET file_id='". $file_id ."',filename='". $file_name ."',first_name='". $first_name ."',file_path='". $file_path ."',username='". $username ."' WHERE bot_request_message ='".$reply_to_msg['message_id']."'";
			 print_r($reply_to_msg['message_id']);
			 $db->exec($statement);

	  $reply = "La segnalazione n° ".$reply_to_msg['message_id']." è stata registrata.\nGrazie!\n";
 		$reply .= "Puoi visualizzarla su :\nhttp://www.piersoft.it/vialiberabot/#18/".$row[0]['lat']."/".$row[0]['lng'];
 		$content = array('chat_id' => $chat_id, 'text' => $reply);
 		$telegram->sendMessage($content);
		$content = array('chat_id' => $chat_id, 'text' => "Dopo che hai inviato la tua segnalazione puoi aggiungere un testo.\nDevi digitare t:numsegnalazione:testo\nper esempio <b>t:".$reply_to_msg['message_id'].":macchina in terza fila.</b>\nPuò modificare il testo solo lo username che ha fatto la segnalazione.",'parse_mode'=>"HTML");
		$telegram->sendMessage($content);
 		$log=$today. ",information for maps recorded," .$chat_id. "\n";

 		exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');
		$mappa = "Puoi visualizzarla su :\nhttp://www.piersoft.it/vialiberabot/#18/".$row[0]['lat']."/".$row[0]['lng'];
		$linkfile="http://www.piersoft.it/vialiberabot/allegato.php?id=".$file_id;
		$content = array('chat_id' => GRUPPO, 'text' => "Segnalazione in arrivo numero ".$reply_to_msg['message_id']." da parte dell'utente @".$username." il ".$today."\n".$mappa."\nScarica foto:\n".$linkfile);
		$telegram->sendMessage($content);


	}
 	//comando errato

 	else{

 		 $reply = "Hai selezionato un comando non previsto. Ricordati che devi prima inviare la tua posizione";
 		 $content = array('chat_id' => $chat_id, 'text' => $reply);
 		 $telegram->sendMessage($content);

 		 $log=$today. ",wrong command sent," .$chat_id. "\n";

 	 }
}
 	//aggiorna tastiera
 	$this->create_keyboard($telegram,$chat_id);
 	//log
 	file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);
	$statement = "DELETE FROM ". DB_TABLE_GEO ." WHERE username =' '";
	$db->exec($statement);
	exec(' sqlite3 -header -csv '.$db_path.' "select * from segnalazioni;" > '.$csv_path. ' ');

 }




 // Crea la tastiera
 function create_keyboard($telegram, $chat_id)
  {
 	 			$option = array(["❓istruzioni"],["🌐posizione","©️info"]);
 				$keyb = $telegram->buildKeyBoard($option, $onetime=true);
 				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[guarda la mappa delle segnalazioni su http://www.piersoft.it/wemapbot/ oppure invia la tua segnalazione cliccando \xF0\x9F\x93\x8E o /Posizione]");
 				$telegram->sendMessage($content);

  }




  function location_manager($username,$db,$telegram,$user_id,$chat_id,$location)
  	{
  		if ($username==""){
  			$content = array('chat_id' => $chat_id, 'text' => "Devi obbligatoriamente impostare il tuo username nelle impostazioni di Telegram",'disable_web_page_preview'=>true);
  			$telegram->sendMessage($content);
  			$log=$today.",".$todayd. ",nousernameset," .$chat_id.",".$username.",".$user_id."\n";
  			file_put_contents('db/telegram.log', $log, FILE_APPEND | LOCK_EX);
  			$this->create_keyboard($telegram,$chat_id);
  			exit;
  		}else
  		{
  			$lng=$location["longitude"];
  			$lat=$location["latitude"];


  			$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lng."&zoom=18&addressdetails=1";
  			$json_string = file_get_contents($reply);
  			$parsed_json = json_decode($json_string);
  			//var_dump($parsed_json);
  			$temp_c1 =$parsed_json->{'display_name'};
  			if ($parsed_json->{'address'}->{'city'}) {
  			//  $temp_c1 .="\ncittà: ".$parsed_json->{'address'}->{'city'};

  			}

  			$response=$telegram->getData();

  			$bot_request_message_id=$response["message"]["message_id"];
  			$time=$response["message"]["date"]; //registro nel DB anche il tempo unix

  			$h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
  			$hm = $h * 60;
  			$ms = $hm * 60;
  			$timec=gmdate("Y-m-d\TH:i:s\Z", $time+($ms));
  			$timec=str_replace("T"," ",$timec);
  			$timec=str_replace("Z"," ",$timec);
  			//nascondo la tastiera e forzo l'utente a darmi una risposta

  	//		$forcehidek=$telegram->buildKeyBoardHide(true);
  	//	  $content = array('chat_id' => $chat_id, 'text' => "Cosa vuoi comunicarci in questo posto?", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);
  						$content = array('chat_id' => $chat_id, 'text' => "Cosa vuoi comunicarmi in ".$temp_c1."?", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);

  		  $bot_request_message=$telegram->sendMessage($content);

  		      	$forcehide=$telegram->buildForceReply(true);

  		  			//chiedo cosa sta accadendo nel luogo
  		// 		$content = array('chat_id' => $chat_id, 'text' => "[Scrivici cosa sta accadendo qui]", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);

  		        $content = array('chat_id' => $chat_id, 'text' => "[scrivi il tuo messaggio]", 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);

  //			$forcehide=$telegram->buildForceReply(true);

  			//chiedo cosa sta accadendo nel luogo
  	//	$content = array('chat_id' => $chat_id, 'text' => "[Cosa vuoi comunicarmi in questo luogo?".$lat.",".$lng, 'reply_markup' =>$forcehide, 'reply_to_message_id' =>$bot_request_message_id);
  			$bot_request_message=$telegram->sendMessage($content);


  			//memorizzare nel DB
  			$obj=json_decode($bot_request_message);
  			$id=$obj->result;
  			$id=$id->message_id;
 			$temp_c1=str_replace(",","_",$temp_c1);
  			//print_r($id);
  			$statement = "INSERT INTO ". DB_TABLE_GEO. " (lat,lng,user,username,text,bot_request_message,time,file_id,file_path,filename,first_name,luogo) VALUES ('" . $lat . "','" . $lng . "','" . $user_id . "',' ',' ','". $id ."','". $timec ."',' ',' ',' ',' ','" . $temp_c1 . "')";
  			$db->exec($statement);


  	}

 }
  }

  ?>
