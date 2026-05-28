<?php
/**
 * @var string $title
 */
?><!doctype html>
<html lang="fr" class="lg:text-[16px] text-[14px]">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= $title ?? "Default " ?></title>
  
  <link rel="stylesheet" href="/assets/styles/tailwind.css">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Icons -->
  <!-- <link rel="stylesheet" href="/assets/styles/iconoir.css" />
  <link rel="stylesheet" href="/assets/styles/phosphor-icons.css" /> -->
  
  <!-- Leaflet -->
  <link rel="stylesheet" href="/assets/styles/leaflet.css" />
  <script src="/assets/scripts/leaflet.js"></script>
  
  <!-- Lodash -->
  <!-- <script src="/assets/scripts/lodash.js"></script> -->
  
  <!-- Assets -->
  <link rel="stylesheet" href="/assets/styles/main.css" />
  <script src="/assets/scripts/map.js" defer></script>
</head>
<body style="font-family: 'Barlow', sans-serif">
