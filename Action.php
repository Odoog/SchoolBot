<?php

	class Action{
		public static function text($message, $buttons = NULL){
			global $chatId, $telegramApi, $vkApi, $sessionType;
			
			if($buttons){ 
				if($sessionType == "vk"){
					$vkApi->sendMessage($chatId, $message, makeAnswerArrayForVk($buttons));
				} else {
					$telegramApi->sendMessage($chatId, $message, makeAnswerArrayForTelegram($buttons));
				}
			} else {
				if($sessionType == "vk"){
					$vkApi->sendMessage($chatId, $message);
				} else {
					$telegramApi->sendMessage($chatId, $message);
				}
			}

		}
		public static function textToId($chatId, $message, $buttons = NULL){
			global $telegramApi, $sessionType, $vkApi;
			
			if($buttons){
				if($sessionType == "vk"){
					$vkApi->sendMessage($chatId, $message, makeAnswerArrayForVk($buttons));
				} else {
					$telegramApi->sendMessage($chatId, $message, makeAnswerArrayForTelegram($buttons));
				}
			} else {
				if($sessionType == "vk"){
					$vkApi->sendMessage($chatId, $message);
				} else {
					$telegramApi->sendMessage($chatId, $message);
				}
			}

		}

		public static function picToId($chatId, $picId, $message = NULL, $buttons = NULL){
			global $telegramApi;
			//pic не работает с vk
			if($buttons){
				$sendMessageObject = $telegramApi->sendPhoto($chatId, $picId, $message, makeAnswerArrayForTelegram($buttons));
			} else {
				$sendMessageObject = $telegramApi->sendPhoto($chatId, $picId, $message);
			}
			return $sendMessageObject->result->message_id;
		}

		public static function pic($picId, $message = NULL, $buttons = NULL){
			global $chatId, $telegramApi;
			//pic не работает с vk
			if($buttons){
				$sendMessageObject = $telegramApi->sendPhoto($chatId, $picId, $message, makeAnswerArrayForTelegram($buttons));
			} else {
				$sendMessageObject = $telegramApi->sendPhoto($chatId, $picId, $message);
			}
			return $sendMessageObject->result->message_id;
		}
	}

?>