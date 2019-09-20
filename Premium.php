<?php

	class Premium{

		public static $currentArrayOfPermission; 

		public static function checkPermission($user, $permisson){
			return (array_search($permisson, json_decode($user["premiumPermissions"], true)) !== NULL);
		}

		public static function makeSecretCode($arrayOfPermission){
			$oldSecretCodeArray = json_decode(mysqlQuest("SELECT `value` FROM `system` WHERE `name` = 'secretCodes'")['value'], true);
			$secretCode = rand(100000000, 900000000);
			$oldSecretCodeArray[$secretCode] = $arrayOfPermission;
			$newSecretCodeArray = json_encode($oldSecretCodeArray , JSON_UNESCAPED_UNICODE);
			mysqlQuest("UPDATE `system` SET `value` = '$newSecretCodeArray' WHERE `name` = 'secretCodes'");
			return $secretCode;
		}

		public static function checkCode($code){
			$secretCodeArray = json_decode(mysqlQuest("SELECT `value` FROM `system` WHERE `name` = 'secretCodes'")['value'], true);
			return $secretCodeArray[$code];
		}

		public static function useCode($code, $params = NULL){
			global $sessionType, $chatId;
			debug("code" , $code);
			debug("params" , $params);
			$oldSecretCodeArray = json_decode(mysqlQuest("SELECT `value` FROM `system` WHERE `name` = 'secretCodes'")['value'], true);
			$arrayOfPermission = $oldSecretCodeArray[$code];
			unset($oldSecretCodeArray[$code]);
			$newSecretCodeArray = json_encode($oldSecretCodeArray, JSON_UNESCAPED_UNICODE);
			mysqlQuest("UPDATE `system` SET `value` = '$newSecretCodeArray' WHERE `name` = 'secretCodes'");
			User::updatePremiumPermissions($arrayOfPermission);
			if($params){
				User::updatePremiumPermissionsParams($params);
			}
		}

	}

?>