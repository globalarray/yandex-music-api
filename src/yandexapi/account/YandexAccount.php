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

use yandexapi\YandexAPI;
use yandexapi\RequestType;

class YandexAccount {

    private ?YandexAccountStatus $status = null;

    public function __construct() {
        $this->updateStatus();
    }

    public function updateStatus() : void{
        if (!$statusRaw = YandexAPI::getInstance()->executeMethod("account/status", RequestType::GET)) {
            throw new \RuntimeException("Account status getting failed");
        }
        if (isset($statusRaw['error'])) {
            throw new \RuntimeException("Invalid token passed");
        }
        $this->status = new YandexAccountStatus($statusRaw);
    }

    public function getExperiments() : array|false{
        return YandexAPI::getInstance()->executeMethod("account/experiments", RequestType::GET);
    }

    public function getStatus() : YandexAccountStatus{
        return $this->status;
    }
}