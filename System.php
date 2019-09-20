<?php

	class System{


		public static $addScheduleObject;
		public static $ENDOFDAYHOUR = 19;
		public static $ENDOFDAYMINUTE = 33;

		public static function sendValueToDB($nameOfValue, $value){
			$valueToDB = json_encode($value, JSON_UNESCAPED_UNICODE);
			mysqlQuest("UPDATE 	`system` SET `value` = '$valueToDB' WHERE `name` = '$nameOfValue'");
		}

		public static function getValueFromDB($nameOfValue, $isParsed = false){
			$valueFromDb = mysqlQuest("SELECT `value` FROM `system` WHERE `name` = '$nameOfValue'");
			if($isParsed){
				$valueFromDb = json_decode($valueFromDb);
			}
			return $valueFromDb;
		}

		public static function checkOriginalSheduleToChange(){
			global $originalSheduleBeforeChanges, $scheduleOfLessons;
			$now_time_array = Time::getArrayOfTimeNow();
			$hourNow = $now_time_array["hours"];
			$minuteNow = $now_time_array["minutes"];
			$dayNow = Time::getDayNow();
			if(!($hourNow == System::$ENDOFDAYHOUR && $minuteNow == System::$ENDOFDAYMINUTE)) return;
			$countOfMinutesNow = Time::getCountOfMinutesFromNumbers($now_time_array["hours"], $now_time_array["minutes"]);
			foreach ($originalSheduleBeforeChanges as $cityName => $city) {
				foreach ($city as $schoolName => $school) {
					foreach ($school as $className => $class) {
						foreach ($class as $dayName => $lessons) {
							if($day != $dayNow) continue;
							$params = [
								"city" => $cityName,
								"school" => $schoolName,
								"class" => $className,
								"day" => $dayName
							];
							Lessons::updateScheduleOfLessons($params, Lessons::getOriginalScheduleOfLessons($params));
						}
					}		
				}
			}
			System::sendValueToDB('scheduleOfLessons', $scheduleOfLessons);
		}

		public static function checkAllLunchBreakes(){
			global $scheduleOfCalls, $sessionType, $texts;
			$usersList = User::getAllUsers();
			$currentLesson = 0;
			$now_time_array = Time::getArrayOfTimeNow();
			$scheduleOfCallsToday = $scheduleOfCalls[Time::getDayNow()];

			$countOfMinutesNow = Time::getCountOfMinutesFromNumbers($now_time_array["hours"], $now_time_array["minutes"]);

			while($currentUser = mysqli_fetch_assoc($usersList)){

				$params = [
					"min" => $countOfMinutesNow,
					"timeZone" => $currentUser["city"]
				];

				$countOfMinutesNowTimeZone = Time::timeZone($params);

				$params = [
					"city" => $currentUser["city"],
					"school" => $currentUser["school"],
					"day" => Time::getDayNow()
				];
				$scheduleOfCallsToday = Lessons::getScheduleOfCalls($params);
				if(!$currentUser["lunchBreak"] || $currentUser["lunchBreak"] == 0) continue;

				$currentLesson = $currentUser["lunchBreak"];
				
				$startTimeCurrentLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$currentLesson]['start']);
				$endTimeCurrentLesson = Time::getCountOfMinutesFromString($scheduleOfCallsToday[$currentLesson]['end']);

				$params = [
					"timeZone" => $currentUser["city"]
				];

				Debug::openChapter("checkAllLunchBreakes params");
					Debug::text("timeZone", $currentUser["city"]);
					Debug::text("countOfMinutesNow", $countOfMinutesNow);
					Debug::text("countOfMinutesNowTimeZone", $countOfMinutesNowTimeZone);
					Debug::text("startTimeCurrentLesson", $startTimeCurrentLesson);
					Debug::text("endTimeCurrentLesson", $endTimeCurrentLesson);
				Debug::closeChapter();

				if($currentUser["lastDayOfFirstAllert"] + 100 !== Time::getDayNow($params)){
					if($countOfMinutesNowTimeZone >= $startTimeCurrentLesson && $countOfMinutesNowTimeZone < $endTimeCurrentLesson){
						Debug::text("endTimeCurrentLesson - countOfMinutesNowTimeZone", $endTimeCurrentLesson - $countOfMinutesNowTimeZone);
						if($endTimeCurrentLesson - $countOfMinutesNowTimeZone == 5){
							$sessionType = $currentUser["session"];
							User::updateValue("lastDayOfFirstAllert", Time::getDayNow($params) - 100, $currentUser["chatId"]);				
							Action::textToId($currentUser["chatId"], $texts["break_alert"]);
						}
					}
				}
				$currentLesson ++;

			}
		}

		public static function caseTree(){
			global $user, $messageText, $flagOfRows, $flagOfTable, $allowedClasses, $buttons, $scheduleOfLessons, $scheduleOfCalls, $nick, $texts, $payload, $macros, $allowedSchool, $allowedCities; 

			if(!$user){
				User::newUser();
				$flagOfRows = true;
				Action::text("Вы не зарегистрированы! \n" . $texts['city_undefined'], $buttons['city_undefined']);
				User::updateValue("stage", 'city_undefined');
				return;
			}

			if(!$user["nick"]){
				User::updateValue("nick", $nick);
			}

			if(substr($messageText, 0, 7) == "premium" || substr($messageText, 0, 7) == "Premium"){
				if(Premium::checkCode(substr($messageText, 8, 9))){
					if(mb_strlen($messageText) > 17){ //Переданы дополнительные параметры
						Premium::useCode(substr($messageText, 8, 9), substr($messageText, 18));
					} else {
						Premium::useCode(substr($messageText, 8));
					}
					Action::text($texts['accepted_code'] . "\n" . Texts::generateMainContent(), $buttons['main']);
					User::updateValue("stage", "main");
					return;
				} else {
					Action::text($texts['denied_code'] . "\n" . Texts::generateMainContent(), $buttons['main']);
					User::updateValue("stage", "main");
				}
				return;
			}

			if($messageText == "Админ 3822"){
				$flagOfRows = true;
				Action::text($texts['admin_main'], $buttons['admin_main']);
				User::updateValue("stage", 'admin_main');
				return;
			}

			if($messageText == "Юзер"){
				$flagOfRows = true;
				Action::text(Texts::generateMainContent(), $buttons['main']);
				User::updateValue("stage", 'main');
				return;
			}

			User::updateValue("lastUsingDay", Time::getArrayOfTimeNow()["mday"]);


			switch ($user['stage']){

				case 'error':

					$flagOfRows = true;
					Action::text(Texts::generateMainContent(), $buttons['main']);
					User::updateValue("stage", 'main');
					break;

				case 'admin_main':

					switch ($messageText) {

						case "Добавить расписание":
							$flagOfRows = true;
							Action::text($texts["admin_add_shedule_cities"], array_merge(System::getValueFromDB("allowedCities", true), $buttons["admin_add_city"], $buttons["Назад"]));
							User::updateValue("stage", "admin_add_shedule_cities");
							break;

						case "Разослать уведомление":
							Action::text($texts["admin_waiting_for_new_message"], $buttons['admin_waiting_for_new_message']);
							User::updateValue("stage", "admin_waiting_for_new_message");
							break;

						case "Изменить расписание":
							$flagOfRows = true;
							Action::text($texts["admin_waiting_for_new_city"], $buttons['admin_waiting_for_new_city']);
							User::updateValue("stage", "admin_waiting_for_new_city");
							break;

						case "Сгенерировать код":
							$flagOfRows = true;
							Premium::$currentArrayOfPermission = [];
							Action::text($texts["admin_generate_code"], array_diff($buttons['admin_generate_code'], Premium::$currentArrayOfPermission));
							User::updateValue("stage", 'admin_generate_code');
							break;

						case "Премиум опции":
							$flagOfRows = true;
							Action::text($texts['admin_premium_options'], $buttons['admin_premium_options']);
							User::updateValue("stage", 'admin_premium_options');
							break;
						
						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;
					}

					break;

				case 'admin_add_shedule_cities':

					switch ($messageText) {
						case 'Добавить город':
							Action::text($texts['admin_add_schedule_new_city'], $buttons["admin_add_schedule_new_city"]);
							User::updateValue("stage", "admin_add_schedule_new_city");
							break;

						case 'Назад':
							$flagOfRows = true;
							Action::text($texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							return;

						default:
							$allowedCities = System::getValueFromDB("allowedCities", true);
							if(in_array($messageText, $allowedCities)){
								
							} else {
								$flagOfRows = true;
								Action::text($texts['admin_add_schedule_cities_error'] . $texts["admin_add_shedule_cities"], array_merge(System::getValueFromDB("allowedCities", true), $buttons["admin_add_city"]));
								User::updateValue("stage", "admin_add_shedule_cities");
								break;
							}
					}

				case 'admin_add_schedule_new_city':

					switch ($messageText) {
						case 'Назад':
							$flagOfRows = true;
							Action::text($texts["admin_add_shedule_cities"], array_merge(System::getValueFromDB("allowedCities", true), $buttons["admin_add_city"], $buttons["Назад"]));
							User::updateValue("stage", "admin_add_shedule_cities");
							break;
						
						default:
							System::$addScheduleObject["cityName"] = $messageText;
							Action::texts($texts["admin_add_schedule_new_timezone"], $buttons["admin_add_schedule_new_timezone"]);
							User::updateValue("stage", "admin_add_schedule_new_timezone");
					}

				case "admin_add_schedule_new_timezone":

					switch ($messageText) {
						case 'Назад':
							$flagOfRows = true;
							Action::text($texts["admin_add_shedule_cities"], array_merge(System::getValueFromDB("allowedCities", true), $buttons["admin_add_city"], $buttons["Назад"]));
							User::updateValue("stage", "admin_add_shedule_cities");
							break;
						
						default:
							System::$addScheduleObject["timeZone"] = int($messageText);
							break;
					}

				case 'admin_premium_options':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;

						case "Разослать шутку":
							Action::text($texts['admin_waiting_for_new_joke'], $buttons["admin_waiting_for_new_joke"]);
							User::updateValue("stage", 'admin_waiting_for_new_joke');
							break;

						case "Разослать мем":
							
							break;
						
						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;
					}

					break;

				case 'admin_waiting_for_new_joke':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts["admin_premium_options"], $buttons["admin_premium_options"]);
							User::updateValue("stage", "admin_premium_options");	
							break;
						
						default:
							Admin::sendMessageToEverybody($messageText, "Шутки");
							Action::text($texts["successful_message_sending"] . "\n" . $texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
					}

					break;

				case 'admin_generate_code':

					switch ($messageText) {
						case "Подтвердить":
							$flagOfRows = true;
							Action::text($texts["code_created"] . " " . Premium::makeSecretCode(Premium::$currentArrayOfPermission) . "\n" . $texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
						
						default:
							Premium::$currentArrayOfPermission[] = $messageText;
							$flagOfRows = true;
							Action::text($texts["admin_generate_code"] . "\n" . json_encode(Premium::$currentArrayOfPermission, JSON_UNESCAPED_UNICODE), array_diff($buttons['admin_generate_code'], Premium::$currentArrayOfPermission));
							break;
					}

					break;

				case 'admin_waiting_for_new_city':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
						
						default:
							Admin::$cityOfChange = $messageText;
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_school'], array_merge($allowedSchool[Admin::$cityOfChange], [$macros["Назад"]]));
							User::updateValue("stage", 'admin_waiting_for_new_school');
							break;
					}

					break;

				case 'admin_waiting_for_new_school':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts["admin_waiting_for_new_city"], $buttons['admin_waiting_for_new_city']);
							User::updateValue("stage", "admin_waiting_for_new_city");
							break;
						
						default:
							Admin::$schoolOfChange = $messageText;
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_day'], $buttons['admin_waiting_for_new_day']);
							User::updateValue("stage", 'admin_waiting_for_new_day');
							break;
					}

					break;
	
				case 'admin_waiting_for_new_day':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
						
						default:
							Admin::$dayOfChange = array_search($messageText, $buttons['admin_waiting_for_new_day']);
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_class'], array_merge($allowedClasses[Admin::$schoolOfChange], [$macros["Назад"]]));
							User::updateValue("stage", 'admin_waiting_for_new_class');
							break;
					}

					break;

				case 'admin_waiting_for_new_class':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['admin_waiting_for_new_day'], $buttons['admin_waiting_for_new_day']);
							User::updateValue("stage", 'admin_waiting_for_new_day');
							break;
						
						default:
							Admin::$classOfChange = $messageText;
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_lesson'], Lessons::makeTableOfLessonsToChange());
							User::updateValue("stage", "admin_waiting_for_new_lesson");
							break;
					}

					break;

				case 'admin_waiting_for_new_lesson':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_class'], $buttons['admin_waiting_for_new_class']);
							User::updateValue("stage", 'admin_waiting_for_new_class');
							break;

						case '+':
							Action::text($texts["admin_waiting_for_new_lesson_to_add"]);
							User::updateValue("stage", "admin_waiting_for_new_lesson_to_add");
							break;
						
						case '-':
							Admin::$lessonOfChange = (int)$payload;
							Debug::text("lessonTOChange", $lessonOfChange);
							$params = [
								"city" => Admin::$cityOfChange,
								"school" => Admin::$schoolOfChange,
								"class" => Admin::$classOfChange,
								"day" => Admin::$dayOfChange,
								"name" => $texts['no_lesson'],
								"num" => Admin::$lessonOfChange
							];
							Lessons::updateLesson($params);
							$flagOfTable = 2;
							Action::text($texts['admin_waiting_for_new_lesson'], Lessons::makeTableOfLessonsToChange());
							break;

						default:
							Admin::$lessonOfChange = $payload;
							Action::text($texts["admin_waiting_for_new_lesson_to_change"]);
							User::updateValue("stage", "admin_waiting_for_new_lesson_to_change");
							break;
					}

					break;
			
				case 'admin_waiting_for_new_lesson_to_add':

					Lessons::addLesson(Admin::$classOfChange, Admin::$dayOfChange, $messageText);
					$flagOfTable = 2;
					Action::text($texts["successful_lesson_add"] . "\n" . $texts['admin_waiting_for_new_class'], array_merge($allowedClasses[Admin::$schoolOfChange], [$macros["Назад"]]));
					User::updateValue("stage", 'admin_waiting_for_new_class');
					break;

				case 'admin_waiting_for_new_lesson_to_change':

					$params = [
						"city" => Admin::$cityOfChange,
						"school" => Admin::$schoolOfChange,
						"class" => Admin::$classOfChange,
						"day" => Admin::$dayOfChange,
						"num" => Admin::$lessonOfChange,
						"name" => $messageText
					];

					Lessons::updateLesson($params);
					$flagOfTable = 2;
					Action::text($texts["successful_lesson_change"] . "\n" . $texts['admin_waiting_for_new_class'], array_merge($allowedClasses[Admin::$schoolOfChange], [$macros["Назад"]]));
					User::updateValue("stage", 'admin_waiting_for_new_class');
					break;

				case 'admin_waiting_for_new_message':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
						
						default:
							Admin::sendMessageToEverybody($messageText);
							$flagOfRows = true;
							Action::text($texts["successful_message_sending"] . "\n" . $texts['admin_main'], $buttons['admin_main']);
							User::updateValue("stage", 'admin_main');
							break;
					}

					break;

				case 'city_undefined':

					$isAllowedCityInput = false;

					foreach ($allowedCities as $key => $allowedCityValue) {
						if($messageText == $allowedCityValue){
							$isAllowedCityInput = true;
							break;
						}
					}

					if($isAllowedCityInput){
						User::updateValue("city", $messageText);
						Action::text($texts["school_undefined"], $allowedSchool[$user["city"]]);
						User::updateValue("stage", 'school_undefined');
					} else {
						Action::text($texts["not_allowed_city_input"]);
					}

					break;

				case 'school_undefined':

					$isAllowedSchoolInput = false;

					foreach ($allowedSchool[$user["city"]] as $key => $allowedSchoolValue) {
						Debug::text("school input", $messageText);
						Debug::text("school array", $allowedSchoolValue);
						if($messageText == $allowedSchoolValue){
							$isAllowedSchoolInput = true;
							break;
						}
					}

					if($isAllowedSchoolInput){
						User::updateValue("school", $messageText);
						$flagOfTable = 4;
						Action::text($texts["class_undefined"], $allowedClasses[$user["city"]][$user["school"]]);
						User::updateValue("stage", 'class_undefined');
					} else {
						Action::text($texts["not_allowed_school_input"]);
					}

					break;

				case 'class_undefined':

					$isAllowedClassInput = false;

					foreach ($allowedClasses[$user["city"]][$user["school"]] as $key => $allowedClass) {
						if($messageText == $allowedClass){
							$isAllowedClassInput = true;
							break;
						}
					}

					if($isAllowedClassInput){
						User::updateValue("class", $messageText);
						$flagOfRows = true;
						Action::text(Texts::generateMainContent(), $buttons['main']);
						User::updateValue("stage", 'main');
					} else {
						Action::text($texts["not_allowed_class_input"]);
					}

					break;

				case 'main':

					$answer = "";

					switch ($messageText) {
						case $macros["Настройки"][0]:
							$flagOfRows = true;
							Action::text($texts['setting'], $buttons['setting']);
							User::updateValue("stage", "setting");
							break;
						
						case $macros["Обновить"][0]:
							$flagOfRows = true;
							if(Premium::checkPermission($user, "Смайлики")){
								Action::text($user["premiumPermissionsParams"] . "\n" . Texts::generateMainContent(), $buttons['main']);
							} else {
								Action::text(Texts::generateMainContent(), $buttons['main']);
							}
							break;

						case $macros["Расписание"][0]:
							$flagOfTable = 2;
							User::updateValue("stage", "day_of_week_undefined");
							Action::text($texts["day_of_week_undefined"], $buttons["day_of_week_undefined"]);
							break;

						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;

					}

					break;

				case 'setting':

					switch ($messageText) {
						case 'Изменить информацию обо мне':
							$flagOfRows = true;
							Action::text($texts["city_undefined"], $buttons["city_undefined"]);
							User::updateValue("stage", "city_undefined");
							break;
						
						case "Установить обеденную перемену":
							Action::text($texts["waiting_for_new_break"]);
							User::updateValue("stage", "waiting_for_new_break");
							break;

						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text(Texts::generateMainContent(), $buttons['main']);
							User::updateValue("stage", "main");
							break;

						case 'Оставить отзыв':
							$flagOfRows = true;
							Action::text($texts['waiting_for_review'], $buttons['waiting_for_review']);
							User::updateValue("stage", 'waiting_for_review');
							break;

						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;
					}

					break;

				case 'waiting_for_review':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text($texts['setting'], $buttons['setting']);
							User::updateValue("stage", "setting");
							break;
						
						default:
							Reviews::sendReview($messageText);
							$flagOfRows = true;
							Action::text($texts["successful_review_send"] . "\n" . $texts['setting'], $buttons['setting']);
							User::updateValue("stage", "setting");
							break;
					}

					break;

				case 'waiting_for_new_break':

					if(is_numeric($messageText)){
						User::updateValue("lunchBreak", $messageText);
						$flagOfRows = true;
						User::updateValue("stage", "main");
						Action::text($texts["successful_lunch_break_set"] . "\n" . Texts::generateMainContent(), $buttons['main']);
					} else {
						Action::text($texts["failed_lunch_break_set"]);
					}

					break;

				case 'waiting_for_new_class':

					$isAllowedClassInput = false;

					foreach ($allowedClasses as $key => $allowedClass) {
						if($messageText == $allowedClass){
							$isAllowedClassInput = true;
							break;
						}
					}

					if($isAllowedClassInput){
						User::updateValue("class", $messageText);
						$flagOfRows = true;
						Action::text($texts["successful_class_change"] . "\n" . Texts::generateMainContent(), $buttons['main']);
						User::updateValue("stage", 'main');
					} else {
						Action::text($texts["not_allowed_class_input"]);
					}
					break;

				case "day_of_week_undefined":

					$targetDay = -1;

					if($messageText == $macros["Назад"][0]){
						$flagOfRows = true;
						Action::text(Texts::generateMainContent(), $buttons['main']);
						$flagOfRows = true;
						User::updateValue("stage", "main");
						break;
					}

					switch($messageText){
						case "Пн":
							$targetDay = 0;
							break;
						case "Вт":
							$targetDay = 1;
							break;
						case "Ср":
							$targetDay = 2;
							break;
						case "Чт":
							$targetDay = 3;
							break;
						case "Пт":
							$targetDay = 4;
							break;
						case "Сб":
							$targetDay = 5;
							break;

						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;
					}

					if($targetDay == -1) break; //invalid input

					$answer = "Расписание на выбранный день: \n";

					$params = [
						"day" => $targetDay
					];

					for($i = 1; $i <= Lessons::getNumOfLastLesson($params); $i++){
						$nameOfLesson = Lessons::getScheduleOfLessons(["day" => $targetDay, "num" => $i]);
						$answer .= $i . " " . $nameOfLesson;
						$answer .= " (" . Lessons::getScheduleOfCalls(["day" => $targetDay, "num" => $i])["start"] . "-";
						$answer .= Lessons::getScheduleOfCalls(["day" => $targetDay, "num" => $i])["end"] . ")\n";
					}

					User::updateValue("stage", "lessons");
					Action::text($answer, $buttons["lessons"]);

					break;

				case 'lessons':

					switch ($messageText) {
						case $macros["Назад"][0]:
							$flagOfRows = true;
							Action::text(Texts::generateMainContent(), $buttons['main']);
							User::updateValue("stage", 'main');
							break;
						
						default:
							Action::text($texts["error_message"], $buttons["error_buttons"]);
							User::updateValue("stage", "error");
							break;
					}

					break;
			}
		}
	}

?>