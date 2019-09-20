<?php

	class Time{

		public static function timeZone($params){
			global $timeZones;
			if($params["min"]){
				return $params["min"] + $timeZones[$params["timeZone"]] * 60;
			}
		}

		public static function getCountOfMinutesFromString($timeString){
			$time_array["hours"] = explode(":", $timeString)[0]; 
			$time_array["minutes"] = explode(":", $timeString)[1];
			return $time_array["hours"] * 60 + $time_array["minutes"];
		}

		public static function getCountOfMinutesFromNumbers($hours, $minutes){
			return $hours * 60 + $minutes;
		}

		public static function getDayNow($params = NULL){
			global $timeZones;
			if($params["timeZone"]){
				$timeStamp = time() + $timeZones[$params["timeZone"]] * 60 * 60;
			} else {
				$timeStamp = time();
			}
			$now_time_array = getdate($timeStamp);
			
			return ($now_time_array["wday"] - 1) % 7; //В wday отсчет дней идет с воскресенья
		}

		public static function getArrayOfTimeNow($params = NULL){
			global $timeZones;
			if($params["timeZone"]){
				$timeStamp = time() + $timeZones[$params["timeZone"]] * 60 * 60;
			} else {
				$timeStamp = time();
			}
			
			return getdate($timeStamp);
		}
	}


?>