<?php

namespace App\Services\Hotel;

use App\Common\FilterException;
use App\Common\SingletonTrait;
use App\Entities\HotelEntity;
use App\Services\Room\RoomService;
use Exception;
use PDO;

class UnoptimizedHotelService extends AbstractHotelService
{
  use SingletonTrait;

  private ?PDO $db = null;

  protected function __construct()
  {
    parent::__construct(new RoomService());
  }

  protected function getDB(): PDO
  {
    if ($this->db === null) {
      $this->db = new PDO(
        "mysql:host=db;dbname=tp;charset=utf8mb4",
        "root",
        "root",
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
      );
    }

    return $this->db;
  }

  protected function computeDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo): float|int
  {
    return 111.111 * rad2deg(
      acos(
        min(
          1.0,
          cos(deg2rad($latitudeTo))
            * cos(deg2rad($latitudeFrom))
            * cos(deg2rad($longitudeTo - $longitudeFrom))
            + sin(deg2rad($latitudeTo))
            * sin(deg2rad($latitudeFrom))
        )
      )
    );
  }

  protected function getMetasByHotelIds(array $hotelIds): array
  {
    $keys = [
      'address_1',
      'address_2',
      'address_city',
      'address_zip',
      'address_country',
      'geo_lat',
      'geo_lng',
      'coverImage',
      'phone',
    ];

    $hotelPlaceholders = implode(',', array_fill(0, count($hotelIds), '?'));
    $keyPlaceholders = implode(',', array_fill(0, count($keys), '?'));

    $stmt = $this->getDB()->prepare("
      SELECT user_id, meta_key, meta_value
      FROM wp_usermeta
      WHERE user_id IN ($hotelPlaceholders)
      AND meta_key IN ($keyPlaceholders)
    ");

    $stmt->execute([
      ...$hotelIds,
      ...$keys,
    ]);

    $rawMetas = [];

    foreach ($stmt->fetchAll() as $row) {
      $hotelId = (int) $row['user_id'];
      $rawMetas[$hotelId][$row['meta_key']] = $row['meta_value'];
    }

    $result = [];

    foreach ($hotelIds as $hotelId) {
      $metas = array_merge(
        array_fill_keys($keys, null),
        $rawMetas[$hotelId] ?? []
      );

      $result[$hotelId] = [
        'address' => [
          'address_1' => $metas['address_1'],
          'address_2' => $metas['address_2'],
          'address_city' => $metas['address_city'],
          'address_zip' => $metas['address_zip'],
          'address_country' => $metas['address_country'],
        ],
        'geo_lat' => $metas['geo_lat'],
        'geo_lng' => $metas['geo_lng'],
        'coverImage' => $metas['coverImage'],
        'phone' => $metas['phone'],
      ];
    }

    return $result;
  }

  protected function getReviewsByHotelIds(array $hotelIds): array
  {
    $placeholders = implode(',', array_fill(0, count($hotelIds), '?'));

    $stmt = $this->getDB()->prepare("
      SELECT 
        wp_posts.post_author AS hotel_id,
        ROUND(AVG(CAST(wp_postmeta.meta_value AS UNSIGNED))) AS rating,
        COUNT(*) AS count
      FROM wp_posts
      INNER JOIN wp_postmeta
        ON wp_postmeta.post_id = wp_posts.ID
        AND wp_postmeta.meta_key = 'rating'
      WHERE wp_posts.post_type = 'review'
      AND wp_posts.post_author IN ($placeholders)
      GROUP BY wp_posts.post_author
    ");

    $stmt->execute($hotelIds);

    $result = [];

    foreach ($stmt->fetchAll() as $row) {
      $result[(int) $row['hotel_id']] = [
        'rating' => (int) $row['rating'],
        'count' => (int) $row['count'],
      ];
    }

    return $result;
  }

  protected function getCheapestRoomIdsByHotelIds(array $hotelIds, array $args = []): array
  {
    $hotelPlaceholders = implode(',', array_fill(0, count($hotelIds), '?'));

    $sql = "
      SELECT 
        wp_posts.post_author AS hotel_id,
        wp_posts.ID AS room_id,
        CAST(price_meta.meta_value AS UNSIGNED) AS price
      FROM wp_posts
      INNER JOIN wp_postmeta price_meta
        ON price_meta.post_id = wp_posts.ID
        AND price_meta.meta_key = 'price'
      LEFT JOIN wp_postmeta surface_meta
        ON surface_meta.post_id = wp_posts.ID
        AND surface_meta.meta_key = 'surface'
      LEFT JOIN wp_postmeta rooms_meta
        ON rooms_meta.post_id = wp_posts.ID
        AND rooms_meta.meta_key = 'rooms'
      LEFT JOIN wp_postmeta bathrooms_meta
        ON bathrooms_meta.post_id = wp_posts.ID
        AND bathrooms_meta.meta_key = 'bathRooms'
      LEFT JOIN wp_postmeta type_meta
        ON type_meta.post_id = wp_posts.ID
        AND type_meta.meta_key = 'type'
      WHERE wp_posts.post_type = 'room'
      AND wp_posts.post_author IN ($hotelPlaceholders)
    ";

    $params = $hotelIds;

    if (isset($args['surface']['min'])) {
      $sql .= " AND CAST(surface_meta.meta_value AS UNSIGNED) >= ?";
      $params[] = $args['surface']['min'];
    }

    if (isset($args['surface']['max'])) {
      $sql .= " AND CAST(surface_meta.meta_value AS UNSIGNED) <= ?";
      $params[] = $args['surface']['max'];
    }

    if (isset($args['price']['min'])) {
      $sql .= " AND CAST(price_meta.meta_value AS UNSIGNED) >= ?";
      $params[] = $args['price']['min'];
    }

    if (isset($args['price']['max'])) {
      $sql .= " AND CAST(price_meta.meta_value AS UNSIGNED) <= ?";
      $params[] = $args['price']['max'];
    }

    if (isset($args['rooms'])) {
      $sql .= " AND CAST(rooms_meta.meta_value AS UNSIGNED) >= ?";
      $params[] = $args['rooms'];
    }

    if (isset($args['bathRooms'])) {
      $sql .= " AND CAST(bathrooms_meta.meta_value AS UNSIGNED) >= ?";
      $params[] = $args['bathRooms'];
    }

    if (isset($args['types']) && !empty($args['types'])) {
      $typePlaceholders = implode(',', array_fill(0, count($args['types']), '?'));
      $sql .= " AND type_meta.meta_value IN ($typePlaceholders)";
      $params = [...$params, ...$args['types']];
    }

    $sql .= " ORDER BY wp_posts.post_author ASC, price ASC";

    $stmt = $this->getDB()->prepare($sql);
    $stmt->execute($params);

    $result = [];

    foreach ($stmt->fetchAll() as $row) {
      $hotelId = (int) $row['hotel_id'];

      if (!isset($result[$hotelId])) {
        $result[$hotelId] = (int) $row['room_id'];
      }
    }

    return $result;
  }

  /**
   * @throws Exception
   * @return HotelEntity[]
   */
  public function list(array $args = []): array
  {
    $search = $args['search'] ?? null;

    $sql = "
      SELECT ID, display_name
      FROM wp_users
    ";

    $params = [];

    if (!empty($search)) {
      $sql .= " WHERE display_name LIKE ?";
      $params[] = '%' . $search . '%';
    }

    $stmt = $this->getDB()->prepare($sql);
    $stmt->execute($params);

    $hotelsData = $stmt->fetchAll();

    if (empty($hotelsData)) {
      return [];
    }

    $hotelIds = array_map(
      fn(array $row) => (int) $row['ID'],
      $hotelsData
    );

    $metasByHotelId = $this->getMetasByHotelIds($hotelIds);
    $reviewsByHotelId = $this->getReviewsByHotelIds($hotelIds);
    $cheapestRoomIdsByHotelId = $this->getCheapestRoomIdsByHotelIds($hotelIds, $args);

    $results = [];

    foreach ($hotelsData as $row) {
      $hotelId = (int) $row['ID'];

      if (!isset($cheapestRoomIdsByHotelId[$hotelId])) {
        continue;
      }

      $metas = $metasByHotelId[$hotelId] ?? null;

      if ($metas === null) {
        continue;
      }

      $hotel = (new HotelEntity())
        ->setId($hotelId)
        ->setName($row['display_name'])
        ->setAddress($metas['address'])
        ->setGeoLat($metas['geo_lat'])
        ->setGeoLng($metas['geo_lng'])
        ->setImageUrl($metas['coverImage'])
        ->setPhone($metas['phone']);

      $reviews = $reviewsByHotelId[$hotelId] ?? [
        'rating' => 0,
        'count' => 0,
      ];

      $hotel->setRating($reviews['rating']);
      $hotel->setRatingCount($reviews['count']);

      if (isset($args['lat'], $args['lng'], $args['distance'])) {
        $hotel->setDistance(
          $this->computeDistance(
            floatval($args['lat']),
            floatval($args['lng']),
            floatval($hotel->getGeoLat()),
            floatval($hotel->getGeoLng())
          )
        );

        if ($hotel->getDistance() > $args['distance']) {
          continue;
        }
      }

      $hotel->setCheapestRoom(
        $this->getRoomService()->get($cheapestRoomIdsByHotelId[$hotelId])
      );

      $results[] = $hotel;
    }

    return $results;
  }
}
