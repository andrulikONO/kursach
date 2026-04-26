<template>
  <div class="categories-dropdown" v-click-outside="close">
    <button
      class="btn btn--catalog"
      @click="toggle"
      :class="{ active: isOpen }"
    >
      <span>Каталог</span>
      <span class="arrow" :class="{ rotated: isOpen }">▾</span>
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
import { CATEGORY_GROUPS } from '../lib/catalog'

const isOpen = ref(false)
const categories = CATEGORY_GROUPS

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

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
