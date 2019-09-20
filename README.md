# SchoolBot

Бот для телеграмм и вконтакте для отслеживания школьного расписания 

# Запуск приложения:

* [botFuck.php](https://github.com/Odoog/SchoolBot/blob/master/botFuck.php) является обьектом TelegramLightApi и используется для связи приложения с серверами telegram. Именно в botFuck.php необходимо указать свой токен telegram
* [botFuckForVk.php](https://github.com/Odoog/SchoolBot/blob/master/botFuckForVk.php) является обьектом VkLightApi и используется для связи приложения с серверами vk. Именно в botFuckForVk.php необходимо указать свой токен vk
* [index.php](https://github.com/Odoog/SchoolBot/blob/master/index.php) является точкой входа в приложение. Запуск его при созданной предварительно БД запустит бота.
