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

readonly final class YandexAlbum {

    public function __construct(
        private string $name,
        private string|int $id,
        private int $year,
        private string $image
    ) {}

    public function getName() : string{
        return $this->name;
    }

    public function getId() : string|int{
        return $this->id;
    }

    public function getYear() : int{
        return $this->year;
    }

    public function getImage() : string{
        return $this->image;
    }
}