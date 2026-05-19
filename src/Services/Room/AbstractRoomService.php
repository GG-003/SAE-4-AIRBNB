<?php

namespace App\Services\Room;

use App\Entities\RoomEntity;

abstract class AbstractRoomService {
  
  /**
   * @return array{
   *     Appartement: int,
   *     Maison: int,
   *     Chambre: int
   * }
   */
  abstract public function getCountByType() : array;
  abstract public function get(int $id): RoomEntity;
}