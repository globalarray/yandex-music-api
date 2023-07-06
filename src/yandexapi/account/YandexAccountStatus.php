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

readonly final class YandexAccountStatus {

    private string $login;
    private bool $hasPlus;
    private string $fullName;
    private string $displayName;

    public function __construct(array $rawData) {
        $this->login = ($accountData = $rawData["result"]["account"])["login"];
        $this->hasPlus = $rawData["result"]["plus"]["hasPlus"];
        $this->fullName = $accountData["fullName"];
        $this->displayName = $accountData["displayName"];
    }

    public function getLogin() : string{
        return $this->login;
    }

    public function isPlus() : bool{
        return $this->hasPlus;
    }

    public function getFullname() : string{
        return $this->fullName;
    }

    public function getDisplayName() : string{
        return $this->displayName;
    }
}