<?php

/**
 *
 *
 *  ___               _     _____
 * |  _ \            ( )_ _(_   _)
 * | (_) )  _     _  |  _)_) | |    __    _ _  ___ ___
 * |    / / _ \ / _ \| | | | | |  / __ \/ _  )  _   _  \
 * | |\ \( (_) ) (_) ) |_| | | | (  ___/ (_| | ( ) ( ) |
 * (_) (_)\___/ \___/ \__)_) (_)  \____)\__ _)_) (_) (_)
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author RootiTeam
 * @link https://github.com/RootiTeam
 * @author David Minaev
 * @link https://github.com/ddosnikgit
 *
 *
 */

declare(strict_types=1);

namespace yandexapi;

use yandexapi\account\YandexToken;
use function curl_init;
use function curl_setopt;
use function http_build_query;
use const CURLOPT_URL;
use const CURLOPT_HTTPGET;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_POSTFIELDS;

final class YandexAPI {

    private const DEFAULT_HEADERS = [
        'X-Yandex-Music-Client: YandexMusicAPI',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.67',
        'Connection: Keep-Alive',
    ];
    private const BASE_URL = "https://api.music.yandex.net/";
    private const OAUTH_URL = "https://oauth.yandex.ru/";
    private const CLIENT_ID = "23cabbbdc6cd418abb4b39c32c41195d";
    private const CLIENT_SECRET = "53bc75238f0c4d08a118e51fe9203300";

    public static ?self $instance = null;
    private array $finalHeaders = self::DEFAULT_HEADERS;

    public function __construct(
        private ?YandexToken $token = null,
    ) {
        self::$instance = $this;
        if ($this->token !== null) array_push($this->finalHeaders, "Authorization: OAuth " . (string)$token);
    }

    public function generateTokenFromCredentials(string $login, string $password) : ?YandexToken{
        $response = $this->executeMethod(
            "token",
            RequestType::POST,
            [
                'grant_type' => 'password',
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'username' => $login,
                'password' => $password
            ],
            [],
            self::OAUTH_URL
        );

        var_dump($response);

        if (isset($response['error'])) return null;

        return new YandexToken($response['access_token']);
    }

    public static function getInstance() : ?self{
        return self::$instance;
    }

    public function executeMethod(string $method, string $type = RequestType::POST, array $postFields = [], array $headers = [], string $url = self::BASE_URL) : array|false{
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url . $method);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        if ($type === RequestType::POST) {
            curl_setopt($cURL, CURLOPT_POST, true);
            curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array_merge($this->finalHeaders, $headers));
        $result = curl_exec($cURL);
        curl_close($cURL);
        return $result ? json_decode($result, true) : false;
    }

    public function download(string $url, string $trackName, string $path) : bool{
        return file_put_contents($path . $trackName . ".mp3", fopen($url, 'r'));
    }

    public function xml(string $url) : \SimpleXMLElement|false{
        return simplexml_load_file($url);
    }
}