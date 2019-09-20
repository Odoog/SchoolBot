<?php
	class Reviews{
		public static function sendReview($reviewText){
			mysqlQuest("INSERT INTO `reviews` (`text`) VALUES ('$reviewText')");
		}
	}
?>