<?php


	use GuzzleHttp\Client;

	//myvkid = 321503643

	class vkBot{
		protected $token = "2fd94255f9ef18ea26ea683669ae30d9678438803e940de7f5cb5cfebb1b766d6a33b0da6f1182cdb9bbc";

		//protected $token = "ed81d6b8d8c91823c702099da4d2ec8b8c6b6c9e3af6fd1930b353427f74458cb8f2a396e85d067a7acee";

		//protected $token = "afd26066dbfbb5d508a576f7ae3679f824e042062de8d9f66eef1d5b54cb277491d820381002de6844685";

		protected $updateId;

		protected $isFirstLongPollQuest = true;

		protected $groupId = 172028037; //Адресс группы в вк

		protected $key;

		protected function query($method, $params = [], $selfDefinedServer = false){
			try {

				if($selfDefinedServer){
					$url = "https://";
					$url .= $selfDefinedServer;
				} else { 
					$params["access_token"] = $this->token;
					$url = "https://api.vk.com/method/";
					$url.= $method; 
				}

				if(!empty($params)){
					$url .= "?" . http_build_query($params);
				}

				//debug("Quest!", $url);

				//debug("Encoding", mb_detect_encoding($url));

				$client = new Client([
					'base_uri' => $url
				]);

				$result = $client->request('GET');

				//debug("Result!", $result->getBody());

				return json_decode($result->getBody());
			} catch(Exception $e) {
				print('Выброшено исключение: '.  $e->getMessage(). "\n");
			}

		}

		public function getUpdates(){

			$response = $this->query('empty/selfDefinedServer', [
				'act' => "a_check",
				'ts' => $this->updateId,
				'key' => $this->key,
				'wait' => 0.1,
				'mode' => 10,
				'version' => 3
			], "imv4.vk.com/im0037");

			if($response->failed == 2){
				$this->getNewServer();
				$this->getUpdates();
			}

			if(!empty($response)){

				//debug("responseresult", json_encode($response));
				$this->updateId = $response->ts;
				//print($response);
			}
			return ($response->updates);
		}

		public function sendMessage($chat_id, $text, $answers = NULL){
			if($text[mb_strlen($text) - 1] == "\n") $text = substr($text, 0, mb_strlen($text) - 1);
			$text = $text . ".";
			//debug("called send message" , $chat_id . " " . $text . json_encode($answers));
			$postfields = [
				'user_id' => "$chat_id",
				'message' => "$text",
				//'group_id' => $this->groupId,
				'v' => 5.85,
				'random_id' => rand(0, 10000000000000)
			];
			if($answers){
				$keyboard = array(
						"buttons" => $answers,
						"one_time" => false //можно заменить на FALSE,клавиатура скроется после нажатия кнопки автоматически при True
					);
					$postfields["keyboard"] = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
			}
			$output = $this->query("messages.send", $postfields);
			return $output;
		}

		public function getNewServer(){
			$postfields = [
				"group_id" => $this->groupId,
				"lp_version" => 2,
				"v" => 5.85,

			];
			$answer = $this->query("messages.getLongPollServer", $postfields);
			$this->key = $answer->response->key;
		}

		// public function sendPhoto($chat_id, $photoAdress, $text = NULL, $keyboardType = NULL, $answers = NULL){
		// 	$postfields = array(
		// 		'photo' => $photoAdress,
		// 		'chat_id' => $chat_id,
		// 	);
		// 	if($keyboardType){
		// 		if($keyboardType == "reply"){
		// 			$keyboard = array(
		// 				"keyboard" => $answers,
		// 				"one_time_keyboard" => false, // можно заменить на FALSE,клавиатура скроется после нажатия кнопки автоматически при True
		// 				"resize_keyboard" => true // можно заменить на FALSE, клавиатура будет использовать компактный размер автоматически при True
		// 			);
		// 		};
		// 		if($keyboardType == "inline"){
		// 			$keyboard = array(
		// 				"inline_keyboard" => $answers
		// 			);	
		// 		}
		// 		$postfields['reply_markup'] = json_encode($keyboard);
		// 	};
		// 	if($text){
		// 		$postfields['caption'] = $text;
		// 	}
		// 	$output = $this->query("sendPhoto", $postfields);
		// 	return $output;
		// }

		
	};

?>