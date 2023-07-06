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

namespace yandexapi\account;

class YandexToken {

    public function __construct(
        private string $rawToken,
    ) {
        if (!$this->isValid($this->rawToken)) {
            throw new \InvalidArgumentException("Token is invalid");
        }
    }

    public function isValid(string $token) : bool{
        $token = trim(preg_replace('/\s+/', ' ', $token));
        return strlen($token) === 58;
    }

    public function __toString() : string{
        return $this->rawToken;
    }
}