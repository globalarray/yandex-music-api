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

use yandexapi\account\YandexAccount;
use yandexapi\account\YandexToken;
use yandexapi\queue\Queue;
use yandexapi\queue\QueueContext;
use yandexapi\queue\QueueTrack;
use yandexapi\utils\Logger;

final class Client {
    private const DEVICE = 'os=php; os_version=8.2; manufacturer=ddosnikgit; model=Yandex Music API; clid=; device_id=random; uuid=random';

	private YandexAccount $account;
	private YandexToken $token;
	private YandexAPI $yandexApi;

    /**
     * Получение информации о текущем аккаунте
     *
     * @return YandexAccount
     */
    public function getAccount() : YandexAccount{
        return $this->account;
    }

    /**
     * Client constructor.
     * @param string $token
     */
    public function __construct(string $token = "") {
        try {
            $this->token = new YandexToken($token);
            $this->yandexApi = new YandexAPI($this->token);
            $this->initAccount();
        } catch (\Throwable $e) {
            Logger::message('Произошла ошибка: ' . $e->getMessage()); //TODO: add Logger.
        }
    }

    public function getApi() : YandexAPI{
        return $this->yandexApi;
    }

    /**
     * Инициализация аккаунта
     */
    private function initAccount() : void{
        $this->account = new YandexAccount();
        Logger::message('Authorized account: ' . $this->getAccount()->getStatus()->getLogin());
    }

    public function settings() : array|false{
        return YandexAPI::getInstance()->executeMethod("settings", RequestType::GET);
    }

    public function permissionAlert() : array|false{
        return YandexAPI::getInstance()->executeMethod("permission-alerts", RequestType::GET);
    }

    /**
     * Активация промокода
     */
    public function consumePromo(string $code, string $lang = "ru") : array|false{
        return YandexAPI::getInstance()->executeMethod("account/consume-promo-code", RequestType::POST, [
            "code" => $code,
            "language" => $lang,
        ]);
    }

    /**
     * Получение потока информации (фида) подобранного под пользователя.
     * Содержит умные плейлисты.
     */
    public function feed() : array|false{
        return YandexAPI::getInstance()->executeMethod("feed", RequestType::GET);
    }

    public function feedWizardIsPassed() {
        return YandexAPI::getInstance()->executeMethod("feed/wizard/is-passed", RequestType::GET);
    }

    /**
     * Получение лендинг-страницы содержащий блоки с новыми релизами,
     * чартами, плейлистами с новинками и т.д.
     *
     * Поддерживаемые типы блоков: personalplaylists, promotions, new-releases, new-playlists,
     * mixes, chart, artists, albums, playlists, play_contexts.
     */

    public function landing(array|string $blocks) : array|false{
        return YandexAPI::getInstance()->executeMethod("feed" . is_array($blocks) ? implode(',', $blocks) : $blocks, RequestType::GET);
    }

    /**
     * Получение жанров музыки
     */
    public function genres() : array|false{
        return YandexAPI::getInstance()->executeMethod("genres", RequestType::GET);
    }

    /**
     * Получение информации о доступных вариантах загрузки трека
     *
     * @param string|int $trackId Уникальный идентификатор трека
     * @param bool $getDirectLinks Получить ли при вызове метода прямую ссылку на загрузку
     */
    public function tracksDownloadInfo(string|int $trackId, bool $getDirectLinks = false) : array{
        $result = [];
        $response = YandexAPI::getInstance()->executeMethod("tracks/" . $trackId . "/download-info", RequestType::GET);

        if (!isset($response['error'])) {
            if ($getDirectLinks) {
                foreach ($response['result'] as $item) {
                    if ($item['codec'] === 'mp3') {
                        $item['directLink'] = $this->getDirectLink($item['downloadInfoUrl'], $item['codec'], $item['bitrateInKbps']);
                        unset($item['downloadInfoUrl']);
                        $result[] = $item;
                    }
                }
            }
        } else {
            $result['error'] = $response['error'];
        }
        return $result;
    }

    /**
     * Получение прямой ссылки на загрузку из XML ответа
     *
     * Метод доступен только одну минуту с момента
     * получения информациио загрузке, иначе 410 ошибка!
     *
     * TODO: перенести загрузку файла в другую функию
     *
     * @param string $url xml-файл с информацией
     * @param string $codec Кодек файла
     *
     * @return string Прямая ссылка на загрузку трека
     */
    public function getDirectLink(string $url, string $codec = "mp3", string $suffix = '1') : string{
        $response = YandexAPI::getInstance()->xml($url);
        if (!$response) return "";
        $hash = md5('XGRlBW9FXlekgbPrRHuSiA' . substr($response['path'], 1) . $response['s']);
        return "https://" . $response["host"] . "/get-" . $codec . "/" . $hash . "/" . $response['ts'] . $response['path'];
    }

    /**
     * Метод для отправки текущего состояния прослушиваемого трека
     *
     * TODO: метод не был протестирован!
     *
     * @param string|int $trackId Уникальный идентификатор трека
     * @param string $from Наименования клиента
     * @param string|int $albumId Уникальный идентификатор альбома
     * @param int $playlistId Уникальный идентификатор плейлиста, если таковой прослушивается.
     * @param bool $fromCache Проигрывается ли трек с кеша
     * @param string $playId Уникальный идентификатор проигрывания
     * @param int $trackLengthSeconds Продолжительность трека в секундах
     * @param int $totalPlayedSeconds Сколько было всего воспроизведено трека в секундах
     * @param int $endPositionSeconds Окончательное значение воспроизведенных секунд
     *
     * @return array|false
     */
    private function playAudio(
        string|int $trackId,
        string $from,
        string|int $albumId,
        int $playlistId = null,
        bool $fromCache = false,
        ?string $playId = null,
        int $trackLengthSeconds = 0,
        int $totalPlayedSeconds = 0,
        int $endPositionSeconds = 0,
    ) : array|false{
        return YandexAPI::getInstance()->executeMethod("play-audio", RequestType::POST, [
            'track-id' => $trackId,
            'from-cache' => $fromCache,
            'from' => $from,
            'play-id' => $playId,
            'uid' => $this->account->uid,
            'timestamp' => (new \DateTime())->format(DateTime::ATOM),
            'track-length-seconds' => $trackLengthSeconds,
            'total-played-seconds' => $totalPlayedSeconds,
            'end-position-seconds' => $endPositionSeconds,
            'album-id' => $albumId,
            'playlist-id' => $playlistId,
            'client-now' => (new \DateTime())->format(DateTime::ATOM)
        ]);
    }

    /**
     * Получение всех очередей треков с разных устройств для синхронизации между ними.
     */
    public function queues_list(string $device = self::DEVICE) : array|false{
        return YandexAPI::getInstance()->executeMethod("queues", RequestType::GET, [], [
            'X-Yandex-Music-Device: ' . $device
        ]);
    }

    /**
     * Получение информации об очереди треков и самих треков в ней.
     */
    public function queue(string|int $queueId) : ?Queue{
        $response = YandexAPI::getInstance()->executeMethod("queues/" . $queueId, RequestType::GET);

        if (!$response) return null;
        if (isset($response['error'])) return null;
        $tracks = [];

        foreach (($queueData = $response['result'])['tracks'] as $track) {
            $tracks[] = new QueueTrack(
                $track['trackId'],
                $track['albumId'],
                $track['from'],
            );
        }

        return new Queue(
            $queueData['id'],
            new QueueContext(
                ($contextData = $queueData['context'])['description'] ?? null,
                $contextData['sessionId'] ?? null,
                $contextData['id'] ?? null,
                $contextData['type'] ?? null
            ),
            $queueData['from'],
            $tracks,
            $queueData['currentIndex'],
            $queueData['modified']
        );
    }

    /**
     * Получение трека/треков.
     */
    public function tracks(array|string $track_ids, bool $with_positions = true) : array|false{
        $response = YandexAPI::getInstance()->executeMethod("tracks", RequestType::POST, [
            'trackIds' => is_array($track_ids) ? implode(',', $track_ids) : $track_ids,
            'with_positions' => (string)$with_positions,
        ]);

        if (!$response) return false;

        /** @var YandexTrack[] $tracks */
        $tracks = [];

        foreach ($response['result'] as $track) {
            /** @var YandexArtist[] $artists */
            $artists = [];
            /** @var YandexAlbum[] $albums */
            $albums = [];
            foreach ($track['artists'] as $artist) {
                $artists[] = new YandexArtist(
                    $artist['name'],
                    $artist['id'],
                );
            }
            foreach ($track['albums'] as $album) {
                $albums[] = new YandexAlbum(
                    $album['title'],
                    $album['id'],
                    $album['year'],
                    $album['ogImage']
                );
            }
            $tracks[] = new YandexTrack(
                $track['title'],
                $track['id'],
                $artists,
                $track['ogImage'],
            );
        }

        return $tracks;
    }

    /**
     * Получение альбома по его уникальному идентификатору вместе с треками
     *
     * @param string|int $albumId Уникальный идентификатор альбома
     */
    /*public function albumsWithTracks($albumId) {
        $url = $this->baseUrl."/albums/$albumId/with-tracks";

        $response = json_decode($this->get($url))->result;

        return $response;
    }*/

    /**
     * Осуществление поиска по запросу и типу, получение результатов
     *
     * @param string $text Текст запроса
     * @param bool $noCorrect Без исправлений?
     * @param string $type Среди какого типа искать (трек, плейлист, альбом, исполнитель)
     * @param int $page Номер страницы
     * @param bool $playlistInBest Выдавать ли плейлисты лучшим вариантом поиска
     *
     * @return mixed parsed json
     */
    /*public function search($text,
                           $noCorrect = false,
                           $type = 'all',
                           $page = 0,
                           $playlistInBest = true
    ) {
        $url = $this->baseUrl."/search"
            ."?text=$text"
            ."&nocorrect=$noCorrect"
            ."&type=$type"
            ."&page=$page"
            ."&playlist-in-best=$playlistInBest";

        $response = json_decode($this->get($url))->result;

        return $response;
    }*/

    /**
     * Получение подсказок по введенной части поискового запроса.
     *
     * @param string $part Часть поискового запроса
     *
     * @return mixed parsed json
     */
    /*public function searchSuggest($part) {
        $url = $this->baseUrl."/search/suggest?part=$part";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * Получение плейлиста или списка плейлистов по уникальным идентификаторам
     *
     * TODO: метод не был протестирован!
     *
     * @param string|int|array $kind Уникальный идентификатор плейлиста
     * @param int $userId Уникальный идентификатор пользователя владеющего плейлистом
     *
     * @return mixed parsed json
     */
   /* public function usersPlaylists($kind, $userId = null) {
        if ($userId == null) {
            $userId = $this->account->uid;
        }

        $url = $this->baseUrl."/users/$userId/playlists";

        $data = array(
            'kind' => $kind
        );

        $response = json_decode($this->post($url, $data));

        return $response;
    }

    /**
     * Создание плейлиста
     *
     * @param string $title Название
     * @param string $visibility Модификатор доступа
     *
     * @return mixed parsed json
     */
    /*public function usersPlaylistsCreate($title, $visibility = 'public') {
        $url = $this->baseUrl."/users/".$this->account->uid."/playlists/create";

        $data = array(
            'title' => $title,
            'visibility' => $visibility
        );

        $response = json_decode($this->post($url, $data))->result;

        return $response;
    }

    /**
     * Удаление плейлиста
     *
     * @param string|int $kind Уникальный идентификатор плейлиста
     *
     * @return mixed decoded json
     */
    /*public function usersPlaylistsDelete($kind) {
        $url = $this->baseUrl."/users/".$this->account->uid."/playlists/$kind/delete";

        $result = json_decode($this->post($url))->result;

        return $result;
    }

    /**
     * Изменение названия плейлиста
     *
     * @param string|int $kind Уникальный идентификатор плейлиста
     * @param string $name Новое название
     *
     * @return mixed decoded json
     */
    /*public function usersPlaylistsNameChange($kind, $name) {
        $url = $this->baseUrl."/users/".$this->account->uid."/playlists/$kind/name";

        $data = array(
            'value' => $name
        );

        $result = json_decode($this->post($url, $data))->result;

        return $result;
    }

    /**
     * Изменение плейлиста.
     *
     * TODO: функция не готова, необходим воспомогательный класс для получения отличий
     *
     * @param string|int $kind Уникальный идентификатор плейлиста
     * @param string $diff JSON представления отличий старого и нового плейлиста
     * @param int $revision TODO
     *
     * @return mixed parsed json
     */
    /*private function usersPlaylistsChange($kind, $diff, $revision = 1) {
        $url = $this->baseUrl."/users/".$this->account->uid."/playlists/$kind/change";

        $data = array(
            'kind' => $kind,
            'revision' => $revision,
            'diff' => $diff
        );

        $response = json_decode($this->post($url, $data))->result;

        return $response;
    }

    /**
     * Добавление трека в плейлист
     *
     * TODO: функция не готова, необходим воспомогательный класс для получения отличий
     *
     * @param string|int $kind Уникальный идентификатор плейлиста
     * @param string|int $trackId Уникальный идентификатор трека
     * @param string|int $albumId Уникальный идентификатор альбома
     * @param int $at Индекс для вставки
     * @param int $revision TODO
     *
     * @return mixed parsed json
     */
    /*public function usersPlaylistsInsertTrack($kind, $trackId, $albumId, $at = 0, $revision = null) {
        if($revision == null)
            $revision = $this->usersPlaylists($kind)->result[0]->revision;

        $oprs = json_encode(array(
            [
            'op' => "insert",
            'at' => $at,
            'tracks'  => [['id' => $trackId, 'albumId' => $albumId]]
        ]));
        
        return $this->usersPlaylistsChange($kind, $oprs, $revision);
    }

    /* ROTOR FUNC HERE */

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @return mixed parsed json
     */
    /*public function rotorAccountStatus() {
        $url = $this->baseUrl."/rotor/account/status";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @return mixed parsed json
     */
    /*public function rotorStationsDashboard() {
        $url = $this->baseUrl."/rotor/stations/dashboard";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $lang Язык ответа API в ISO 639-1
     *
     * @return mixed parsed json
     */
    /*public function rotorStationsList($lang = 'en') {
        $url = $this->baseUrl."/rotor/stations/list?language=".$lang;

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $genre Жанр
     * @param string $type
     * @param string $from
     * @param string|int $batchId
     * @param string $trackId
     *
     * @return mixed parsed json
     *
     * @throws Exception
     */
    /*public function rotorStationGenreFeedback($genre, $type, $from = null, $batchId = null, $trackId = null) {
        $url = $this->baseUrl."/rotor/station/genre:$genre/feedback";
        if ($batchId != null) {
            $url .= "?batch-id=".$batchId;
        }

        $data = array(
            'type' => $type,
            'timestamp' => (new \DateTime())->format(DateTime::ATOM)
        );
        if ($from != null) {
            $data['from'] = $from;
        }
        if ($trackId != null) {
            $data['trackId'] = $trackId;
        }

        $response = json_decode($this->post($url, $data))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $genre
     * @param string $from
     *
     * @return mixed parsed json
     *
     * @throws Exception
     */
    /*public function rotorStationGenreFeedbackRadioStarted($genre, $from) {
        return $this->rotorStationGenreFeedback($genre, 'radioStarted', $from);
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $genre
     * @param string $from
     *
     * @return mixed parsed json
     *
     * @throws Exception
     */
    /*public function rotorStationGenreFeedbackTrackStarted($genre, $from) {
        return $this->rotorStationGenreFeedback($genre, 'trackStarted', $from);
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $genre
     *
     * @return mixed parsed json
     */
    /*public function rotorStationGenreInfo($genre) {
        $url = $this->baseUrl."/rotor/station/genre:$genre/info";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $genre
     *
     * @return mixed parsed json
     */
   /* public function rotorStationGenreTracks($genre) {
        $url = $this->baseUrl."/rotor/station/genre:$genre/tracks";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /* ROTOR FUNC END */

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string|int $artistId
     *
     * @return mixed parsed json
     */
    /*public function artistsBriefInfo($artistId) {
        $url = $this->baseUrl."/artists/$artistId/brief-info";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $objectType
     * @param string|int|array $ids
     * @param bool $remove
     *
     * @return mixed parsed json
     */
    /*
    private function likeAction($objectType, $ids, $remove = false) {
        $action = 'add-multiple';
        if ($remove) {
            $action = 'remove';
        }
        $url = $this->baseUrl."/users/".$this->account->uid."/likes/".$objectType."s/$action";

        $data = array(
            $objectType.'-ids' => $ids
        );

        $response = json_decode($this->post($url, $data))->result;

        if ($objectType == 'track') {
            $response = $response->revision;
        }

        return $response;
    }

    public function usersLikesTracksAdd($trackIds) {
        return $this->likeAction('track', $trackIds);
    }

    public function usersLikesTracksRemove($trackIds) {
        return $this->likeAction('track', $trackIds, true);
    }

    public function usersLikesArtistsAdd($artistIds) {
        return $this->likeAction('artist', $artistIds);
    }

    public function usersLikesArtistsRemove($artistIds) {
        return $this->likeAction('artist', $artistIds, true);
    }

    public function usersLikesPlaylistsAdd($playlistIds) {
        return $this->likeAction('playlist', $playlistIds);
    }

    public function usersLikesPlaylistsRemove($playlistIds) {
        return $this->likeAction('playlist', $playlistIds, true);
    }

    public function usersLikesAlbumsAdd($albumIds) {
        return $this->likeAction('album', $albumIds);
    }

    public function usersLikesAlbumsRemove($albumIds) {
        return $this->likeAction('album', $albumIds, true);
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string $objectType
     * @param string|int|array $ids
     *
     * @return mixed parsed json
     */
    /*private function getList($objectType, $ids) {
        $url = $this->baseUrl."/".$objectType."s";
        if ($objectType == 'playlist') {
            $url .= "/list";
        }

        $data = array(
            $objectType.'-ids' => $ids
        );

        $response = json_decode($this->post($url, $data))->result;

        return $response;
    }

    public function artists($artistIds) {
        return $this->getList('artist', $artistIds);
    }

    public function albums($albumIds) {
        return $this->getList('album', $albumIds);
    }

    public function playlistsList($playlistIds) {
        return $this->getList('playlist', $playlistIds);
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @return mixed parsed json
     */
    /*public function usersPlaylistsList() {
        $url = $this->baseUrl."/users/".$this->account->uid."/playlists/list";

        $response = json_decode($this->get($url))->result;

        return $response;
    }

    /**
     * Получения списка лайков
     *
     * @param string $objectType track, album, artist, playlist
     *
     * @return mixed decoded json
     */
    /*private function getLikes($objectType) {
        $url = $this->baseUrl."/users/".$this->account->uid."/likes/".$objectType."s";

        $response = json_decode($this->get($url))->result;

        if ($objectType == "track") {
            return $response->library;
        }

        return $response;
    }

    public function getLikesTracks() {
        return $this->getLikes('track');
    }

    public function getLikesAlbums() {
        return $this->getLikes('album');
    }

    public function getLikesArtists() {
        return $this->getLikes('artist');
    }

    public function getLikesPlaylists() {
        return $this->getLikes('playlist');
    }

    /**
     * TODO: Описание функции
     *
     * @param int $ifModifiedSinceRevision
     *
     * @return mixed parsed json
     */
   /* public function usersDislikesTracks($ifModifiedSinceRevision = 0) {
        $url = $this->baseUrl."/users/".$this->account->uid."/dislikes/tracks"
            .'?if_modified_since_revision='.$ifModifiedSinceRevision;

        $response = json_decode($this->get($url))->result->library;

        return $response;
    }

    /**
     * TODO: Описание функции
     *
     * TODO: метод не был протестирован!
     *
     * @param string|int|array $ids
     * @param bool $remove
     *
     * @return mixed parsed json
     */
    /*private function dislikeAction($ids, $remove = false) {
        $action = 'add-multiple';
        if ($remove) {
            $action = 'remove';
        }
        $url = $this->baseUrl."/users/".$this->account->uid."/dislikes/tracks/$action";

        $data = array(
            'track-ids-ids' => $ids
        );

        $response = json_decode($this->post($url, $data))->result;

        return $response;
    }

    public function users_dislikes_tracks_add($trackIds) {
        return $this->dislikeAction($trackIds);
    }

    public function users_dislikes_tracks_remove($trackIds) {
        return $this->dislikeAction($trackIds, true);
    }

    private function post($url, $data = null) {
        return $this->requestYandexAPI->post($url, $data);
    }

    private function get($url) {
        return $this->requestYandexAPI->get($url);
    }
}*/
}