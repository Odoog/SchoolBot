<?php

	require __DIR__ . '/vendor/autoload.php';

	include('Lessons.php');
	include('Texts.php');
	include('Action.php');
	include('Admin.php');
	include('System.php');
	include('Time.php');
	include('User.php'); 
	include('botFuck.php');
	include('botFuckForVk.php');
	include('Debug.php');
	include('Premium.php');
	include('Constants.php');
	include('Reviews.php');

	Debug::$doSpeedTest = false;
	Debug::$doTextDebug = false;
	Debug::$debugMode = false;
	Debug::$spaceCount = 0;
	Debug::text("time", Time::getArrayOfTimeNow());

	$flagOfRows = false;
	$sessionType = "";
	$payload = "";

	$telegramApi = new TelegramBot();
	$vkApi = new vkBot();

	$scheduleOfLessons = json_decode(mysqlQuest("SELECT `value` FROM `system` WHERE `name` = 'scheduleOfLessons'")['value'], true);
	$originalSheduleBeforeChanges = json_decode(mysqlQuest("SELECT `value` FROM `system` WHERE `name` = 'originalSheduleBeforeChanges'")['value'], true);


	function makeAnswerArrayForTelegram($answerArray){
		global $flagOfRows;
		if($flagOfRows){
			$flagOfRows = false;
			$updateAnswerArray = [];
			foreach ($answerArray as $ind => $value) {
				$updateAnswerArray[] = [["text" => $value]];
			}
			return $updateAnswerArray;
		} else {
			$updateAnswerArray = [];
			foreach ($answerArray as $ind => $value) {
				$updateAnswerArray[] = ["text" => $value];
			}
			return [$updateAnswerArray];
		}
	}

	function makeAnswerArrayForVk($answerArray){
		global $flagOfRows, $flagOfTable;

		if($flagOfRows){
			$flagOfRows = false;
			$flagOfTable = 1;
		}

		if(!$flagOfTable && !$flagOfRows){
			$flagOfTable = 10000;
		}

		if($flagOfTable){
			$line = [];
			$answer = [];
			$period = 0;
			foreach ($answerArray as $ind => $value) {
				if($period == $flagOfTable){
					$answer[] = $line;
					$period = 0;
					$line = [];
				}
				Debug::text("button", $value);
				if(gettype($value) == "array"){
					if($value[1]){
					//В кнопке есть скрытое значение
						$buttonsObject = [
							"action" => [
								"type" => "text",
          						"label" => $value[0],
          						"payload" => "$value[1]"
          						
							]
						];
					} else {
						$buttonsObject = [
							"action" => [
								"type" => "text",
          						"label" => $value[0]
							]
						];
					}
				} else {
					$buttonsObject = [
						"action" => [
							"type" => "text",
          					"label" => $value
						]
					];
				}
				if(gettype($value) == "array" && $value[2]){
					$buttonsObject["color"] = $value[2];
				} else {
					$buttonsObject["color"] = "default";
				}
				$line[] = $buttonsObject;
				$period ++;
			}
			$answer[] = $line;
			$flagOfTable = 0;
			return $answer;
		}
	}

	function mysqlQuest($quest, $type = "Single"){
		$connection = mysqli_connect('127.0.0.1', "root", '', "schoolBot");
		$connection->set_charset('utf8mb4');
		if(Debug::$debugMode){	//When its debug mode we change table users in bd for table usersDebug
			$findIndex = strpos($quest, "users");
			if($findIndex !== false) $quest = substr($quest, 0, $findIndex) . "usersDebug" . substr($quest, $findIndex + 5);
		}
		$answer = mysqli_query($connection, $quest);
		//Debug::text("req mysqlQuest params", $quest);
		if($answer){
			if($type == "Single") $answer = mysqli_fetch_assoc($answer);
			return $answer;
		} else {
			return false;
		} 
	}

	while(true){
		
		System::checkOriginalSheduleToChange();
		System::checkAllLunchBreakes();

		$sessionType = "tg";

		// debug("Start get telegram query", time() - $debugTime);
		// $debugTime = time();

		//$updatesTelegram = $telegramApi->getUpdates();

		// debug("End get telegram query", time() - $debugTime);
		// $debugTime = time();

		// foreach($updatesTelegram as $update){


		// 	$nick = $update->message->from->username;
		// 	$chatId = $update->message->chat->id;
		// 	$userId = $update->message->from->id;
		// 	$messageText = $update->message->text;
		// 	$photoId = $update->message->photo[0]->file_id;
		// 	if($photoId){
		// 		$messageText = $update->message->caption;
		// 	}

		// 	print_r($update);

		// 	print_r($photoId);

		// 	$user = User::getUser($userId, $sessionType);

		// 	System::caseTree();

		// };

		$sessionType = "vk";

		Debug::speedTest("Start get vk query");

		$updatesVk = $vkApi->getUpdates();

		Debug::speedTest("End get vk query");		

		foreach($updatesVk as $update){

			Debug::speedTest("Update processing");

			//print_r($update);

			$code = $update[0];

			$chatId = $update[3];
			$userId = $update[3];
			$messageText = $update[5];
			if($update[6]->payload){
				$payload = $update[6]->payload;
			} else {
				$payload = "";
			}

			if($code != 4) continue; //4 - код получения нового сообщения
			if($messageText[mb_strlen($messageText) - 1] == ".") continue;

			$user = User::getUser($userId, $sessionType);

			System::caseTree();

		};
	};
?>