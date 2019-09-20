<?php

	class Lessons{

		public static function getScheduleOfLessons($params){
			global $scheduleOfLessons, $user;

			//Debug::text("req getScheduleOfLessons params:", json_encode($params, JSON_UNESCAPED_UNICODE));

			if(array_key_exists('num', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
				if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
				if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();
			}

			if(array_key_exists('day', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
				if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
			}

			if(array_key_exists('class', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			}

			if(array_key_exists('school', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			}

			if(array_key_exists('city', $params)){
				if(array_key_exists('school', $params)){
					if(array_key_exists('class', $params)){
						if(array_key_exists('day', $params)){
							if(array_key_exists('num', $params)){
								return $scheduleOfLessons[$params['city']][$params['school']][$params['class']][$params['day']][$params['num']];
							} else {
								return $scheduleOfLessons[$params['city']][$params['school']][$params['class']][$params['day']];
							}
						} else {
							return $scheduleOfLessons[$params['city']][$params['school']][$params['class']];
						}
					} else {
						return $scheduleOfLessons[$params['city']][$params['school']];
					}
				} else {
					return $scheduleOfLessons[$params['city']];
				}
			} else {
				return $scheduleOfLessons;
			}
		}

		public static function getOriginalScheduleOfLessons($params){
			global $originalSheduleBeforeChanges, $user;

			if(array_key_exists('num', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
				if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
				if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();
			}

			if(array_key_exists('day', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
				if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
			}

			if(array_key_exists('class', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			}

			if(array_key_exists('school', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			}


			if(array_key_exists('city', $params)){
				if(array_key_exists('school', $params)){
					if(array_key_exists('class', $params)){
						if(array_key_exists('day', $params)){
							if(array_key_exists('num', $params)){
								return $originalSheduleBeforeChanges[$params['city']][$params['school']][$params['class']][$params['day']][$params['num']];
							} else {
								return $originalSheduleBeforeChanges[$params['city']][$params['school']][$params['class']][$params['day']];
							}
						} else {
							return $originalSheduleBeforeChanges[$params['city']][$params['school']][$params['class']];
						}
					} else {
						return $originalSheduleBeforeChanges[$params['city']][$params['school']];
					}
				} else {
					return $originalSheduleBeforeChanges[$params['city']];
				}
			} else {
				return $originalSheduleBeforeChanges;
			}
		}

		public static function getScheduleOfCalls($params){
			global $scheduleOfCalls, $user;

			if(array_key_exists('num', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
				if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();
			}

			if(array_key_exists('day', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
				if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			}

			if(array_key_exists('school', $params)){
				if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			}

		if(array_key_exists('city', $params)){
			if(array_key_exists('school', $params)){
					if(array_key_exists('day', $params)){
						if(array_key_exists('num', $params)){
							return $scheduleOfCalls[$params["city"]][$params["school"]][$params["day"]][$params["num"]];
						} else {
							return $scheduleOfCalls[$params["city"]][$params["school"]][$params["day"]];
						}
					} else {
						return $scheduleOfCalls[$params["city"]][$params["school"]];
					}
				} else {
					return $scheduleOfCalls[$params["city"]];
				}
			} else {
				return $scheduleOfCalls;
			}
		}

		public static function updateScheduleOfLessons($params, $new){
			global $scheduleOfLessons;
			if(array_key_exists('city', $params)){
				if(array_key_exists('school', $params)){
					if(array_key_exists('class', $params)){
						if(array_key_exists('day', $params)){
							if(array_key_exists('num', $params)){
								$scheduleOfLessons[$params['city']][$params['school']][$params['class']][$params['day']][$params['num']] = $new;
							} else {
								$scheduleOfLessons[$params['city']][$params['school']][$params['class']][$params['day']] = $new;
							}
						} else {
							$scheduleOfLessons[$params['city']][$params['school']][$params['class']] = $new;
						}
					} else {
						$scheduleOfLessons[$params['city']][$params['school']] = $new;
					}
				} else {
					$scheduleOfLessons[$params['city']] = $new;
				}
			} else {
				$scheduleOfLessons = $new;
			}
			System::sendValueToDB('scheduleOfLessons', $scheduleOfLessons);

		}

		public static function makeTableOfLessonsToChange(){
			$params = [
				"city" => Admin::$cityOfChange,
				"school" => Admin::$schoolOfChange,
				"class" => Admin::$classOfChange,
				"day" => Admin::$dayOfChange
			];
			$startLessonsTable = Lessons::getLessonsArray($params);
			$answerLessonsTable = [];
			foreach ($startLessonsTable as $key => $value) {
				$answerLessonsTable[] = [$value, $key + 1];
				$answerLessonsTable[] = ["-", $key + 1, "negative"];
			}
			$answerLessonsTable[] = ["+", false, "positive"];
			return $answerLessonsTable;

		}

		public static function getNumOfLastLesson($params){
			global $user;

			if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
			if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();

			return sizeof(Lessons::getScheduleOfLessons($params));
		}

		public static function getLesson($params){
			global $user;

			if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
			if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();
			if(!array_key_exists('num', $params)) $params["num"] = $user["num"];
	
			return Lessons::getScheduleOfLessons($params);
		}


		public static function getLessonsArray($params){
			global $user;
			if(!array_key_exists('city', $params)) $params["city"] = $user["city"];
			if(!array_key_exists('school', $params)) $params["school"] = $user["school"];
			if(!array_key_exists('class', $params)) $params["class"] = $user["class"];
			if(!array_key_exists('day', $params)) $params["day"] = Time::getDayNow();

			$targetShedule = Lessons::getScheduleOfLessons($params);
			$answer = [];
			foreach ($targetShedule as $numOfLesson => $lesson) {
				$answer[] = $numOfLesson . ") " . $lesson;
			}
			return $answer;
		}

		public static function updateLesson($params){
			global $scheduleOfLessons;
			Debug::text("params", $params);
			//if(!($params["city"] && $params["school"] && $params["class"] && $params["day"] && $params["num"] && $params["name"])) Debug::errorMessage("Lessons -> updateLesson", "missing parameter");


			Lessons::updateScheduleOfLessons($params, $params["name"]);
		}

		public static function addLesson($params){
			global $scheduleOfLessons;
			//if(!($params["city"] && $params["school"] && $params["class"] && $params["day"] && $params["name"])) Debug::errorMessage("Lessons -> addLesson", "missing parameter");
			$params["num"] = Lessons::getNumOfLastLessonByDayAndClass($params) + 1;
			Lessons::updateLesson($params);
		}
	}

?>