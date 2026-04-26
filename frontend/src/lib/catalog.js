export const CATEGORY_GROUPS = [
  {
    name: 'Транспорт',
    items: [
      { name: 'Автомобили', slug: 'auto', icon: '🚗' },
      { name: 'Мотоциклы', slug: 'moto', icon: '🏍️' },
      { name: 'Грузовики', slug: 'trucks', icon: '🚛' },
      { name: 'Запчасти', slug: 'parts', icon: '🔧' },
      { name: 'Велосипеды', slug: 'bikes', icon: '🚲' }
    ]
  },
  {
    name: 'Недвижимость',
    items: [
      { name: 'Квартиры', slug: 'flats', icon: '🏠' },
      { name: 'Дома', slug: 'houses', icon: '🏡' },
      { name: 'Комнаты', slug: 'rooms', icon: '🚪' },
      { name: 'Офисы', slug: 'offices', icon: '🏢' },
      { name: 'Земля', slug: 'land', icon: '🌍' }
    ]
  },
  {
    name: 'Электроника',
    items: [
      { name: 'Телефоны', slug: 'phones', icon: '📱' },
      { name: 'Ноутбуки', slug: 'laptops', icon: '💻' },
      { name: 'Планшеты', slug: 'tablets', icon: '📟' },
      { name: 'ТВ', slug: 'tv', icon: '📺' },
      { name: 'Игры', slug: 'games', icon: '🎮' }
    ]
  },
  {
    name: 'Одежда и обувь',
    items: [
      { name: 'Женская', slug: 'women', icon: '👗' },
      { name: 'Мужская', slug: 'men', icon: '👔' },
      { name: 'Обувь', slug: 'shoes', icon: '👟' },
      { name: 'Детская', slug: 'kids', icon: '🧸' },
      { name: 'Аксессуары', slug: 'accessories', icon: '👜' }
    ]
  },
  {
    name: 'Дом и сад',
    items: [
      { name: 'Мебель', slug: 'furniture', icon: '🛋️' },
      { name: 'Бытовая техника', slug: 'appliances', icon: '🧺' },
      { name: 'Посуда', slug: 'dishes', icon: '🍽️' },
      { name: 'Сад', slug: 'garden', icon: '🌱' },
      { name: 'Ремонт', slug: 'repair', icon: '🔨' }
    ]
  },
  {
    name: 'Услуги',
    items: [
      { name: 'Ремонт', slug: 'repair-services', icon: '🔧' },
      { name: 'Красота', slug: 'beauty', icon: '💄' },
      { name: 'Обучение', slug: 'education', icon: '📚' },
      { name: 'Перевозки', slug: 'transport', icon: '🚚' },
      { name: 'Фото/Видео', slug: 'photo', icon: '📷' }
    ]
  }
]

export const CATEGORY_OPTIONS = CATEGORY_GROUPS.flatMap((group) =>
  group.items.map((item) => ({
    label: `${group.name} - ${item.name}`,
    name: item.name,
    slug: item.slug
  }))
)

const CATEGORY_MAP = Object.fromEntries(
  CATEGORY_OPTIONS.map((item) => [item.slug, item])
)

export function getCategoryMeta(slug) {
  return slug ? CATEGORY_MAP[slug] || null : null
}

export function getCategoryName(slug) {
  return getCategoryMeta(slug)?.name || ''
}

export const LISTING_KIND_OPTIONS = [
  { value: 'sale', label: 'Продажа' },
  { value: 'buy', label: 'Покупка' }
]

export function getListingKindLabel(value) {
  return LISTING_KIND_OPTIONS.find((item) => item.value === value)?.label || 'Продажа'
}
