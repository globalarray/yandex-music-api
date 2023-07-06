<?php

namespace yandexapi\utils;

class Logger {

	//2019-09-12 22:32:21 /Applications/MAMP/htdocs/index.php [tag]: message
	public static function message(string $message, string $importance = "INFO") : void{
		date_default_timezone_set('Europe/Moscow');
		$message = date('Y-m-d H:i:s').' '.$importance.': '.$message.PHP_EOL;
        echo $message;
		file_put_contents('work.log', $message, FILE_APPEND);
	}

	//2019-09-13 00:07:02 Logger [Download]: Log downloaded by ::1
	//OS: Mac OS X
	public static function download() {
		$msg = "Log downloaded by ".$_SERVER['REMOTE_ADDR'].PHP_EOL.'OS: '.Utils::getOS();
		Logger::message($msg, "Download");
		header('Content-disposition: attachment;filename=work.log');
		readfile("work.log");
	}

	//2019-09-12 23:50:58 Logger [getPlatformInfo]: Yandex Music Fisher 0.0.1
	//OS: Mac OS X
	public static function getPlatformInfo() {
		global $config;
		$msg = $config['title'].' '.$config['version'].PHP_EOL.'OS: '.Utils::getOS();
		Logger::message($msg, "getPlatformInfo");
	}
}