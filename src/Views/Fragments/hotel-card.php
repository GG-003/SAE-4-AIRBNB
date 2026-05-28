<?php
/**
 * @var HotelEntity $hotel
 */

use App\Entities\HotelEntity;

?>
<div
  class="hotel-card rounded-lg overflow-hidden border border-solid border-slate-100"
  data-lat="<?= $hotel->getGeoLat(); ?>"
  data-lng="<?= $hotel->getGeoLng(); ?>"
>
  <div class="bg-sky-50 aspect-video relative">
    
  <picture>
    <source srcset="<?= preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $hotel->getImageUrl()) ?>" type="image/webp">
    <img
      src="<?= $hotel->getImageUrl(); ?>"
      alt="Image de l'hôtel"
      class="w-full aspect-video object-cover object-center"
      loading="lazy"
      decoding="async"
    />
  </picture>

    <?php if ($hotel->hasDistance()) : ?>
      <div class="bg-white absolute right-2 top-2 text-sm text-slate-600 rounded-lg p-2">
        <?= round($hotel->getDistance(), 2); ?>Km
      </div>
    <?php endif; ?>
  </div>

  <div class="p-4">
    <div class="flex flex-row justify-between items-center text-slate-600 mb-2">
      <p class="text-lg">
        <?= $hotel->getCheapestRoom()->getPrice(); ?>€<span class="text-sm">/nuit</span>
      </p>

      <p class="flex flex-row items-center">
        <span class="mr-1">❤️</span>
        <?= $hotel->getRating(); ?> (<?= $hotel->getRatingCount() ?>)
      </p>
    </div>

    <header class="text-lg font-bold text-slate-900">
      <?= $hotel->getName(); ?>
    </header>

    <p><?= $hotel->getAddress()['address_city']; ?></p>
    <p><?= $hotel->getAddress()['address_country']; ?></p>

    <footer class="mt-2">
      <ul class="flex flex-row justify-between items-center">
        <li class="flex flex-row items-center text-slate-400">
          <span class="mr-1">🛏️</span>
          <?= $hotel->getCheapestRoom()->getBedRoomsCount() ?>
        </li>

        <li class="flex flex-row items-center text-slate-400">
          <span class="mr-1">🚿</span>
          <?= $hotel->getCheapestRoom()->getBathRoomsCount() ?>
        </li>

        <li class="flex flex-row items-center text-slate-400">
          <span class="mr-1">📐</span>
          <?= $hotel->getCheapestRoom()->getSurface() ?>m²
        </li>
      </ul>
    </footer>
  </div>
</div>