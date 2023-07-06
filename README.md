# yandex-music-api
Неофициальное API для Yandex Music на PHP 8.2+. Огромная благодарность @MarshalX, @LuckyWins

## Простой пример

```
$client = new Client($token);
$queues = $client->queues_list();

if (count($queues = $queues["result"]["queues"]) > 0) {
    $lastQueue = $client->queue($queues[0]['id']);
    if (!count($lastQueue->getTracks())) {
        exit('Сейчас играет: . $lastQueue->getContext()->getDescription()');
    }

    $track = $client->tracks($lastQueue->getTracks()[$lastQueue->getCurrentIndex()]->getId())[0];
    $artists = array_map(function (YandexArtist $artist) : string{
        return $artist->getName();
    }, $track->getArtists());

    exit("Сейчас играет трек: " . implode(', ', $artists) . ' - ' . $track->getName());
}

exit("Сейчас ничего не играет");
```
