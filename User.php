<?php

	class User{
		public static $currentStage;

		public static function updateValue($valueName, $value, $localUserId = false){
			global $userId, $user, $sessionType;
			if(gettype($value) == "array") $value = json_encode($value, JSON_UNESCAPED_UNICODE);
			if($localUserId){
				mysqlQuest("UPDATE `users` SET `$valueName` = '$value' WHERE `id` = $localUserId AND `session` = '$sessionType'");
			} else {
				mysqlQuest("UPDATE `users` SET `$valueName` = '$value' WHERE `id` = $userId AND `session` = '$sessionType'");
			}
			$user[$valueName] = $value;
		}

		public static function updateLastDayOfFirstAllert($userId, $newlastDayOfFirstAllert){
			global $user, $sessionType;
			mysqlQuest("UPDATE `users` SET `lastDayOfFirstAllert` = '$newlastDayOfFirstAllert' WHERE `id` = $userId AND `session` = '$sessionType'");
			$user["lastDayOfFirstAllert"] = $newlastDayOfFirstAllert;
		}

		public static function newUser(){
			global $userId, $chatId, $sessionType;
			mysqlQuest("INSERT INTO `users`(`id`, `chatId`, `stage`, `session`) VALUES ('$userId', '$chatId', 'registration', '$sessionType')");
		}
		
		public static function getUser($userId, $sessionType){

			$answer = mysqlQuest("SELECT * FROM `users` WHERE `id` = $userId AND `session` = '$sessionType'");
			return $answer;
		}

		public static function getAllUsers(){
			$answer = mysqlQuest("SELECT * FROM `users`", "Group");
			return $answer;	
		}

	}

?>