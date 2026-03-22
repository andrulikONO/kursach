<template>
  <div class="catalog-page">
    <div class="page-header">
      <div>
        <h1 class="title" style="margin: 0 0 4px 0; font-size: 22px">
          {{ categoryTitle }}
        </h1>
        <div class="muted">{{ categoryDescription }}</div>
      </div>
      <button class="btn" type="button" @click="reload">Обновить</button>
    </div>

    <div class="catalog-grid">
      <!-- Левая часть: список объявлений -->
      <section class="listings-section">
        <div class="card" style="margin-bottom: 14px">
          <div class="card__body">
            <div class="muted" style="font-size: 13px">
              Показано: <b>{{ store.items.length }}</b>
              <span v-if="store.loading"> · загрузка...</span>
            </div>
            <div v-if="store.error" class="danger" style="margin-top: 10px">
              {{ store.error }}
            </div>
          </div>
        </div>

        <div v-if="!store.loading && store.items.length === 0" class="card">
          <div class="card__body" style="text-align: center; padding: 40px">
            <div style="font-size: 48px; margin-bottom: 16px">📭</div>
            <p class="muted">Объявлений пока нет</p>
            <RouterLink to="/new" class="btn btn--primary" style="margin-top: 16px">
              Разместить первое
            </RouterLink>
          </div>
        </div>

        <div v-else class="listings-list">
          <RouterLink 
            v-for="p in store.items" 
            :key="p.id" 
            class="listing-item" 
            :to="`/product/${p.id}`"
          >
            <div class="listing-thumb">
              {{ (p.type || 'Товар').slice(0, 1) }}
            </div>
            <div class="listing-content">
              <div class="title">{{ p.title }}</div>
              <div class="listing-meta">
                <div class="price">{{ formatPrice(p.price) }}</div>
                <div class="muted" style="font-size: 12px">#{{ p.id }}</div>
              </div>
              <div class="tags">
                <span class="tag">{{ p.type || '—' }}</span>
                <span class="tag">{{ p.city || 'Город не указан' }}</span>
              </div>
            </div>
          </RouterLink>
        </div>
      </section>

      <!-- Правая часть: фильтры -->
      <aside class="filters-section">
        <FiltersPanel 
          :model="filters" 
          @update:model="filters = $event" 
          @apply="apply" 
        />
      </aside>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { RouterLink } from 'vue-router'
import FiltersPanel from '../components/FiltersPanel.vue'
import { useProductStore } from '../stores/productStore'

const route = useRoute()
const store = useProductStore()
const filters = ref({ q: '', type: '', minPrice: '', maxPrice: '' })

// Заголовок в зависимости от категории
const categoryTitle = computed(() => {
  if (route.params.category) {
    const names = {
      auto: 'Автомобили',
      flats: 'Квартиры',
      phones: 'Телефоны',
      furniture: 'Мебель',
    }
    return names[route.params.category] || 'Каталог'
  }
  return 'Последние объявления'
})

const categoryDescription = computed(() => {
  if (route.params.category) {
    return `Все объявления в категории "${categoryTitle.value}"`
  }
  return 'Самые свежие объявления со всех категорий'
})

function apply(f) {
  const filtersWithCategory = {
    ...f,
    type: route.params.category || f.type
  }
  store.loadList(filtersWithCategory)
}

function reload() {
  apply(filters.value)
}

function formatPrice(v) {
  const n = Number(v || 0)
  return new Intl.NumberFormat('ru-RU', { 
    style: 'currency', 
    currency: 'RUB', 
    maximumFractionDigits: 0 
  }).format(n)
}

onMounted(() => {
  apply(filters.value)
})
</script>

<style scoped>
.catalog-page {
  display: grid;
  gap: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
}

.catalog-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 20px;
  align-items: start;
}

.listings-section {
  min-width: 0;
}

.filters-section {
  position: sticky;
  top: 84px;
}

.listings-list {
  display: grid;
  gap: 14px;
}

.listing-item {
  display: flex;
  gap: 16px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  transition: transform 140ms ease, border-color 140ms ease;
  text-decoration: none;
  color: inherit;
}

.listing-item:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.28);
}

.listing-thumb {
  width: 140px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.22), rgba(77, 211, 255, 0.14));
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 32px;
  color: rgba(255, 255, 255, 0.72);
}

.listing-content {
  padding: 14px;
  display: grid;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.listing-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

@media (max-width: 980px) {
  .catalog-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-section {
    position: static;
  }
}

@media (max-width: 640px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .listing-item {
    flex-direction: column;
  }
  
  .listing-thumb {
    width: 100%;
    height: 140px;
  }
}
</style>