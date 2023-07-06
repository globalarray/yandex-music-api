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

readonly final class QueueContext {

    public function __construct(
        private ?string $description,
        private ?string $sessionId,
        private ?string $id,
        private ?string $type,
    ) {}

    public function getDescription() : ?string{
        return $this->description;
    }

    public function getSessionId() : ?string{
        return $this->sessionId;
    }

    public function getId() : ?string{
        return $this->id;
    }

    public function getType() : ?string{
        return $this->type;
    }
}