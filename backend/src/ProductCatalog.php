<?php
declare(strict_types=1);

namespace Kursach;

final class ProductCatalog
{
  /**
   * @return array<string, string>
   */
  public static function categoryMap(): array
  {
    return [
      'auto' => 'Автомобили',
      'moto' => 'Мотоциклы',
      'trucks' => 'Грузовики',
      'parts' => 'Запчасти',
      'bikes' => 'Велосипеды',
      'flats' => 'Квартиры',
      'houses' => 'Дома',
      'rooms' => 'Комнаты',
      'offices' => 'Офисы',
      'land' => 'Земля',
      'phones' => 'Телефоны',
      'laptops' => 'Ноутбуки',
      'tablets' => 'Планшеты',
      'tv' => 'ТВ',
      'games' => 'Игры',
      'women' => 'Женская одежда',
      'men' => 'Мужская одежда',
      'shoes' => 'Обувь',
      'kids' => 'Детская одежда',
      'accessories' => 'Аксессуары',
      'furniture' => 'Мебель',
      'appliances' => 'Бытовая техника',
      'dishes' => 'Посуда',
      'garden' => 'Сад',
      'repair' => 'Ремонт для дома',
      'repair-services' => 'Ремонтные услуги',
      'beauty' => 'Красота',
      'education' => 'Обучение',
      'transport' => 'Перевозки',
      'photo' => 'Фото и видео',
    ];
  }

  public static function getCategoryName(string $slug): ?string
  {
    $slug = trim($slug);
    if ($slug === '') {
      return null;
    }

    $map = self::categoryMap();
    return $map[$slug] ?? null;
  }

  public static function normalizeListingKind(?string $value): string
  {
    $kind = strtolower(trim((string)$value));
    return in_array($kind, ['sale', 'buy'], true) ? $kind : 'sale';
  }
}
