<?php


	class Admin{
 
		public static $cityOfChange;
		public static $schoolOfChange;
		public static $dayOfChange;
		public static $classOfChange;
		public static $lessonOfChange;

		public static function sendMessageToEverybody($messageText, $premiumLimitation = Null){
			global $photoId;
			$usersList = User::getAllUsers();
			if($premiumLimitation){
				if($photoId){
					while($currentUser = mysqli_fetch_assoc($usersList)){
						if(Premium::checkPermission($currentUser, $premiumLimitation)){
							Action::picToId($currentUser["chatId"], $photoId, $messageText);
						}
					}
				} else {
					while($currentUser = mysqli_fetch_assoc($usersList)){
						if(Premium::checkPermission($currentUser, $premiumLimitation)){
							Action::textToId($currentUser["chatId"], $messageText);
						}
					}
				}
			} else {
				if($photoId){
					while($currentUser = mysqli_fetch_assoc($usersList)){
						Action::picToId($currentUser["chatId"], $photoId, $messageText);
					}
				} else {
					while($currentUser = mysqli_fetch_assoc($usersList)){
						Action::textToId($currentUser["chatId"], $messageText);
					}
				}
			}
		}
	}

?>