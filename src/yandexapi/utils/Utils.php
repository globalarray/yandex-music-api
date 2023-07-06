<?php

//declare(strict_types=1);

namespace yandexapi\utils;

class Utils {

    /**
     * pretty-print var_dump
     *
     * @param mixed $value
     */
	public static function dump($value) {
		print '<pre>'.print_r($value, true).'</pre>';
	}

    /**
     * pretty-print json
     *
     * @param mixed $data
     */
	public static function jsonEncode($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

	public static function getOS() { 
		$user_agent = $_SERVER["HTTP_USER_AGENT"];
    	$os_platform = "Unknown OS Platform";
    	$os_array = array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );
    	foreach ($os_array as $regex => $value) { 
        	if (preg_match($regex, $user_agent)) {
            	$os_platform    =   $value;
        	}
    	}
    	return $os_platform;
    }

}