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

namespace yandexapi\queue;

readonly final class Queue {

    public function __construct(
        private string $id,
        private QueueContext $context,
        private string $from,
        private array $tracks,
        private int $currentIndex,
        private string $modified
    ) {
        //NOOP
    }

    public function getId() : string{
        return $this->id;
    }

    public function getContext() : QueueContext{
        return $this->context;
    }

    public function getFrom() : string{
        return $this->from;
    }

    public function getTracks() : array{
        return $this->tracks;
    }

    public function getCurrentIndex() : int{
        return $this->currentIndex;
    }

    public function getModified() : string{
        return $this->modified;
    }
}