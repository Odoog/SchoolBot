<?php

	class Debug{


		public static $doSpeedTest;
		public static $doTextDebug;
		public static $lastSpeedTestTime;
		public static $debugMode;
		public static $spaceCount;

		public static function openChapter($chapterName){
			if(gettype($chapterName) != "string") $chapterName = json_encode($chapterName, JSON_UNESCAPED_UNICODE);
			$chapterString = "";
			for($i = 0; $i < Debug::$spaceCount; $i ++) $chapterString .= " ";
			file_put_contents("debug.txt", $chapterString . $chapterName . " { " . "\n", FILE_APPEND);
			Debug::$spaceCount += 5;
		}

		public static function closeChapter(){
			Debug::$spaceCount -= 5;
			$chapterString = "";
			for($i = 0; $i < Debug::$spaceCount; $i ++) $chapterString .= " ";
			file_put_contents("debug.txt", $chapterString . " } " . "\n", FILE_APPEND);
		}		

		public static function text($debugName, $debugText){
			if(!Debug::$doTextDebug) return;
			if(gettype($debugText) != "string") $debugText = json_encode($debugText, JSON_UNESCAPED_UNICODE);
			$chapterString = "";
			for($i = 0; $i < Debug::$spaceCount; $i ++) $chapterString .= " ";
			file_put_contents("debug.txt", $chapterString . $debugName . " : " . $debugText . "\n\n", FILE_APPEND);
		}

		public static function speedTest($testName){
			if(!Debug::$doSpeedTest) return;
			Debug::text("speedTest period (" . $testName . ")", time() - Debug::$lastSpeedTestTime);
			Debug::$lastSpeedTestTime = time();
		}

	}

?>