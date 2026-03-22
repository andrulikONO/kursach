<template>
  <div class="categories-dropdown" v-click-outside="close">
    <button 
      class="btn btn--catalog" 
      @click="toggle"
      :class="{ active: isOpen }"
    >
      <span>Каталог</span>
      <span class="arrow" :class="{ rotated: isOpen }">▼</span>
    </button>

    <div v-if="isOpen" class="dropdown-menu">
      <div class="dropdown-grid">
        <div v-for="group in categories" :key="group.name" class="dropdown-column">
          <h4 class="dropdown-title">{{ group.name }}</h4>
          <ul class="dropdown-list">
            <li v-for="cat in group.items" :key="cat.name">
              <RouterLink 
                :to="`/catalog/${cat.slug}`" 
                @click="close"
                class="dropdown-link"
              >
                <span class="cat-icon">{{ cat.icon }}</span>
                <span>{{ cat.name }}</span>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const isOpen = ref(false)

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

// Директива для закрытия при клике вне
const vClickOutside = {
  mounted: (el, binding) => {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted: (el) => {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

const categories = [
  {
    name: 'Транспорт',
    items: [
      { name: 'Автомобили', slug: 'auto', icon: '🚗' },
      { name: 'Мотоциклы', slug: 'moto', icon: '🏍️' },
      { name: 'Грузовики', slug: 'trucks', icon: '🚛' },
      { name: 'Запчасти', slug: 'parts', icon: '🔧' },
      { name: 'Велосипеды', slug: 'bikes', icon: '🚲' },
    ]
  },
  {
    name: 'Недвижимость',
    items: [
      { name: 'Квартиры', slug: 'flats', icon: '🏠' },
      { name: 'Дома', slug: 'houses', icon: '🏡' },
      { name: 'Комнаты', slug: 'rooms', icon: '🚪' },
      { name: 'Офисы', slug: 'offices', icon: '🏢' },
      { name: 'Земля', slug: 'land', icon: '🌍' },
    ]
  },
  {
    name: 'Электроника',
    items: [
      { name: 'Телефоны', slug: 'phones', icon: '📱' },
      { name: 'Ноутбуки', slug: 'laptops', icon: '💻' },
      { name: 'Планшеты', slug: 'tablets', icon: '📟' },
      { name: 'ТВ', slug: 'tv', icon: '📺' },
      { name: 'Игры', slug: 'games', icon: '🎮' },
    ]
  },
  {
    name: 'Одежда и обувь',
    items: [
      { name: 'Женская', slug: 'women', icon: '👗' },
      { name: 'Мужская', slug: 'men', icon: '👔' },
      { name: 'Обувь', slug: 'shoes', icon: '👟' },
      { name: 'Детская', slug: 'kids', icon: '🧸' },
      { name: 'Аксессуары', slug: 'accessories', icon: '👜' },
    ]
  },
  {
    name: 'Дом и сад',
    items: [
      { name: 'Мебель', slug: 'furniture', icon: '🛋️' },
      { name: 'Бытовая техника', slug: 'appliances', icon: '🧺' },
      { name: 'Посуда', slug: 'dishes', icon: '🍽️' },
      { name: 'Сад', slug: 'garden', icon: '🌱' },
      { name: 'Ремонт', slug: 'repair', icon: '🔨' },
    ]
  },
  {
    name: 'Услуги',
    items: [
      { name: 'Ремонт', slug: 'repair-services', icon: '🔧' },
      { name: 'Красота', slug: 'beauty', icon: '💄' },
      { name: 'Обучение', slug: 'education', icon: '📚' },
      { name: 'Перевозки', slug: 'transport', icon: '🚚' },
      { name: 'Фото/Видео', slug: 'photo', icon: '📷' },
    ]
  },
]
</script>

<style scoped>
.categories-dropdown {
  position: relative;
}

.btn--catalog {
  display: flex;
  align-items: center;
  gap: 8px;
}

.arrow {
  font-size: 10px;
  transition: transform 0.2s ease;
}

.arrow.rotated {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 8px;
  min-width: 600px;
  /* Убрано: background: var(--card) - теперь сплошной цвет */
  background: #1a1f35;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: 0 12px 48px rgba(0, 0, 0, 0.6);
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.2s ease;
}

.dropdown-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  padding: 20px;
}

.dropdown-column {
  display: grid;
  gap: 10px;
}

.dropdown-title {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--primary-2);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dropdown-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 6px;
}

.dropdown-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 8px;
  color: var(--text);
  text-decoration: none;
  transition: background 0.2s ease;
  font-size: 14px;
}

.dropdown-link:hover {
  background: rgba(124, 92, 255, 0.3);
}

.cat-icon {
  font-size: 18px;
  width: 24px;
  text-align: center;
}

.dropdown-footer {
  border-top: 1px solid var(--border);
  padding: 12px 20px;
  background: rgba(0, 0, 0, 0.2);
}

.dropdown-link-all {
  color: var(--primary-2);
  font-weight: 600;
  text-decoration: none;
  font-size: 14px;
}

.dropdown-link-all:hover {
  text-decoration: underline;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .dropdown-menu {
    min-width: 300px;
    max-width: 90vw;
  }
  
  .dropdown-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .dropdown-grid {
    grid-template-columns: 1fr;
  }
}
</style>