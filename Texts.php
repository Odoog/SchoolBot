<?php

	class Texts{
		public static function generateMainContent(){
			global $scheduleOfCalls, $user;
			$answer = ""; 
			$currentLesson = 0;
			$now_time_array = Time::getArrayOfTimeNow(["timeZone" => $user["city"]]);
			$params = [
				"city" => $user["city"],
				"school" => $user["school"],
				"day" => Time::getDayNow(["timeZone" => $user["city"]])
			];
			$scheduleOfCallsToday = Lessons::getScheduleOfCalls($params);
			$countOfMinutesNow = Time::getCountOfMinutesFromNumbers($now_time_array["hours"], $now_time_array["minutes"]);
			if($countOfMinutesNow < Time::getCountOfMinutesFromString($scheduleOfCallsToday[1]['start'])){
				$answer .= "Первый урок ещё не начался \n";
				$params = [
					"num" => 1
				];
				$answer .= "Следующий урок: " . Lessons::getLesson($params) . "\n";
				
			} else {
				if($countOfMinutesNow > Time::getCountOfMinutesFromString($scheduleOfCallsToday[Lessons::getNumOfLastLesson()]['end'])){
					$answer .= "Последний урок на сегодня уже закончился \n";
					
				} else {
					$currentLesson = 1;
					$nextLesson = 2;
					while(True){
						$startTimeCurrentLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$currentLesson]['start']);
						$startTimeNextLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$nextLesson]['start']);

						$endTimeCurrentLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$currentLesson]['end']);
						$endTimeNextLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$nextLesson]['end']);

						if($countOfMinutesNow >= $startTimeCurrentLesson && $countOfMinutesNow < $endTimeCurrentLesson){
							$params = [
								"num" => $currentLesson
							];
							$answer .= "Урок: " . Lessons::getLesson($params) . "\n";
							

							$answer .= "Закончится через " . ($endTimeCurrentLesson - $countOfMinutesNow) . " минут \n";
							
							break;
						}
						if($countOfMinutesNow >= $endTimeCurrentLesson && $countOfMinutesNow < $startTimeNextLesson){
							$answer .= "Cейчас идет перемена \n";
							
							$answer .= "Закончится через " . ($startTimeNextLesson - $countOfMinutesNow) . " минут \n";	
							
							break;
						}
						$currentLesson ++;
						$nextLesson ++;
					}
					$params = [
						"num" => $nextLesson
					];
					$answer .= "Следующий урок: " . Lessons::getLesson($params) . "\n";
					
				}
			}
			
			return $answer;
		}
	}

?>