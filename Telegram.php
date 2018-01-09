<?php
/**
 * Telegram Bot Class.
 * based on first version by @author Gabriele Grillo <gabry.grillo@alice.it>
 *
 */

include('settings.php');

class Telegram {
	private $bot_id = TELEGRAM_BOT;
	private $data = array();
	private $updates = array();
	public $inited = false;


	public function KeyboardButton($text, $request_location = null, $request_contact = null)
	{
		$params = compact('text', 'request_location', 'request_contact');
		return $params;
	}
	public function InputTextMessageContent($message_text, $parse_mode = null, $disable_web_page_preview = false)
	{
		$params = compact('message_text', 'parse_mode', 'disable_web_page_preview');
		return $params;
	}

	public function InlineQueryResultArticle($id, $title, $input_message_content,$thumb_url = "", $reply_markup,$url = "", $hide_url = false, $description = "", $thumb_width , $thumb_height)
	{
	    $type = 'article';
	    $params = compact('type', 'id', 'title', 'input_message_content', 'thumb_url','reply_markup','url', 'hide_url', 'description','thumb_width','thumb_height');
	    return $params;
	}

	public function InlineQueryResultLocation($id, $latitude,$longitude,$title )
	{
	    $type = 'location';
	    $params = compact('type', 'id', 'latitude','longitude','title');
	    return $params;
	}

	public function answerInlineQuery(array $content) {
			return $this->endpoint("answerInlineQuery",$content);
	}

 public function __construct($bot_id) {
        $this->bot_id = $bot_id;
        $this->data = $this->getData();
    }
    public function init() {
    	if ($this->inited) {
      	return true;
    	}
    }
    public function getMe() {
        return $this->endpoint("getMe", array(), false);
    }
    public function sendMessage(array $content) {
        return $this->endpoint("sendMessage", $content);
    }
    public function endpoint($api, array $content, $post = true) {
        $url = 'https://api.telegram.org/bot' . $this->bot_id . '/' . $api;
        if ($post)
        {
            return $this->sendAPIRequest($url, $content);
        }
        else
        {
            return $this->sendAPIRequest($url, array(), false);
        }
    }
    public function sendPhoto(array $content) {
        return $this->endpoint("sendPhoto", $content);
    }
    public function sendAudio(array $content) {
        return $this->endpoint("sendAudio", $content);
    }
    public function sendDocument(array $content) {
        return $this->endpoint("sendDocument", $content);
    }
    public function sendSticker(array $content) {
        return $this->endpoint("sendSticker", $content);
    }
    public function sendVideo(array $content) {
        return $this->endpoint("sendVideo", $content);
    }
    public function sendVoice(array $content) {
        return $this->endpoint("sendVoice", $content);
    }
    public function sendLocation(array $content) {
        return $this->endpoint("sendLocation", $content);
    }
    public function sendChatAction(array $content) {
        return $this->endpoint("sendChatAction", $content);
    }
    public function setWebhook($url) {

        $content = array('url' => $url);
        return $this->endpoint("setWebhook", $content);
    }
	public function removeWebhook() {
    	//$this->init();
    	$content = array('url' => '');
    	return $this->endpoint('setWebhook', $content);
  	}
    public function getData() {
        if (empty($this->data)) {
            $rawData = file_get_contents("php://input");
            return json_decode($rawData, true);
        } else {
            return $this->data;
        }
    }
    public function setData(array $data) {
        $this->data = data;
    }
    public function Text() {
        return $this->data["message"]["text"];
    }
    public function ChatID() {
        return $this->data["message"]["chat"]["id"];
    }
    public function Date() {
        return $this->data["message"]["date"];
    }
    public function FirstName() {
        return $this->data["message"]["from"]["first_name"];
    }
    public function LastName() {
        return $this->data["message"]["from"]["last_name"];
    }
    public function Username() {
        return $this->data["message"]["from"]["username"];
    }
    public function User_id(){
    	return $this->data["message"]["from"]["id"];
    }
    public function Location() {
        return $this->data["message"]["location"];
    }
    public function UpdateID() {
        return $this->data["update_id"];
    }
    public function UpdateCount() {
        return count($this->updates["result"]);
    }
	public function ReplyToMessage() {

        return $this->data["message"]["reply_to_message"];
    }
    public function MessageId() {

        return $this->data["message"]["message_id"];
    }
    public function messageFromGroup() {
        if ($this->data["message"]["chat"]["title"] == "") {
            return false;
        }
        return true;
    }

    //gestisce un invio in broadcast a tutti gli utenti registrati in un database
    public function sendMessageAll($type, $user, $content)
    {
		$apiendpoint = ucfirst($type);
		if ($type == 'photo' || $type == "audio" || $type == "video" || $type == "document") {
			$mimetype = mime_content_type($content);
			$content = new CurlFile($content, $mimetype);
		} elseif ($type == "message") {
			$type = 'text';
		}
		print_r($user);
		$ch = curl_init("https://api.telegram.org/bot".$this->bot_id."/send".$apiendpoint);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array(
				'Host: api.telegram.org',
				'Content-Type: multipart/form-data'
			),
			CURLOPT_POSTFIELDS => array(
				'chat_id' => $user,
				$type => $content
			),
			CURLOPT_TIMEOUT => 0,
			CURLOPT_CONNECTTIMEOUT => 6000,
			CURLOPT_SSL_VERIFYPEER => false
		));
		curl_exec($ch);
		curl_close($ch);
    }

    //costruisce la tastiera del servizio
    public function buildKeyBoard(array $options, $onetime = true, $resize = true, $selective = true) {
        $replyMarkup = array(
            'keyboard' => $options,
            'one_time_keyboard' => $onetime,
            'resize_keyboard' => $resize,
            'selective' => $selective
        );
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }
		/// Create a KeyboardButton
			/** This object represents one button of an inline keyboard. You must use exactly one of the optional fields.
			 * \param $text String; Array of button rows, each represented by an Array of Strings
			 * \param $request_contact Boolean Optional. If True, the user's phone number will be sent as a contact when the button is pressed. Available in private chats only
			 * \param $request_location Boolean Optional. If True, the user's current location will be sent when the button is pressed. Available in private chats only
			 * \return the requested button as Array
			 */
			public function buildKeyboardButton($text, $request_contact = false, $request_location = false) {
					$replyMarkup = array(
							'text' => $text,
							'request_contact' => $request_contact,
							'request_location' => $request_location
					);
					if ($url != "") {
							$replyMarkup['url'] = $url;
					} else if ($callback_data != "") {
							$replyMarkup['callback_data'] = $callback_data;
					} else if ($switch_inline_query != "") {
							$replyMarkup['switch_inline_query'] = $switch_inline_query;
					}
					return $replyMarkup;
			}

			/// Set an InlineKeyBoard
		 /** This object represents an inline keyboard that appears right next to the message it belongs to.
			* \param $options Array of Array of InlineKeyboardButton; Array of button rows, each represented by an Array of InlineKeyboardButton
			* \return the requested keyboard as Json
			*/
		 public function buildInlineKeyBoard(array $options) {
				 $replyMarkup = array(
						 'inline_keyboard' => $options,
				 );
				 $encodedMarkup = json_encode($replyMarkup, true);
				 return $encodedMarkup;
		 }
		 /// Create an InlineKeyboardButton
		 /** This object represents one button of an inline keyboard. You must use exactly one of the optional fields.
			* \param $text String; Array of button rows, each represented by an Array of Strings
			* \param $url String Optional. HTTP url to be opened when button is pressed
			* \param $callback_data String Optional. Data to be sent in a callback query to the bot when button is pressed
			* \param $switch_inline_query String Optional. If set, pressing the button will prompt the user to select one of their chats, open that chat and insert the bot‘s username and the specified inline query in the input field. Can be empty, in which case just the bot’s username will be inserted.
			* \return the requested button as Array
			*/
		 public function buildInlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = "") {
				 $replyMarkup = array(
						 'text' => $text
				 );
				 if ($url != "") {
						 $replyMarkup['url'] = $url;
				 } else if ($callback_data != "") {
						 $replyMarkup['callback_data'] = $callback_data;
				 } else if ($switch_inline_query != "") {
						 $replyMarkup['switch_inline_query'] = $switch_inline_query;
				 }
				 return $replyMarkup;
		 }

	 public function buildKeyBoardHide($selective = true) {
        $replyMarkup = array(
            'hide_keyboard' => true,
            'selective' => $selective
        );
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }
    public function buildForceReply($selective = true) {
        $replyMarkup = array(
            'force_reply' => true,
            'selective' => $selective
        );
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }

    public function getUpdates($offset = 0, $limit = 100, $timeout = 0, $update = true) {
        $content = array('offset' => $offset, 'limit' => $limit, 'timeout' => $timeout);
        $reply = $this->endpoint("getUpdates", $content);
        $this->updates = json_decode($reply, true);
        if ($update) {
            $last_element_id = $this->updates["result"][count($this->updates["result"]) - 1]["update_id"] + 1;
            $content = array('offset' => $last_element_id, 'limit' => "1", 'timeout' => $timeout);
            $this->endpoint("getUpdates", $content);
        }
        return $this->updates;
    }
    public function serveUpdate($update) {
        $this->data = $this->updates["result"][$update];
    }

    private function sendAPIRequest($url, array $content, $post = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
/*
				$myFile = "db/logarray.txt";
				$updateArray = print_r($result,TRUE);
				$fh = fopen($myFile, 'a') or die("can't open file");
				fwrite($fh, $updateArray."\n");
				fclose($fh);
			*/
        return $result;
    }
}
// Helper for Uploading file using CURL
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
                . ($postname ? : basename($filename))
                . ($mimetype ? ";type=$mimetype" : '');
    }
}
?>
