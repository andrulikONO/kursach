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

    <div class="catalog-layout">
      <aside class="filters-column">
        <FiltersPanel
          :model="filters"
          :show-category-filter="!currentCategory"
          @update:model="filters = $event"
          @apply="apply"
        />
      </aside>

      <section class="listings-column">
        <div class="card" style="margin-bottom: 14px">
          <div class="card__body">
            <div class="muted" style="font-size: 13px">
              <template v-if="store.total > 0">
                Показано: <b>{{ rangeFrom }}–{{ rangeTo }}</b> из <b>{{ store.total }}</b>
              </template>
              <template v-else-if="!store.loading">Нет объявлений по выбранным условиям</template>
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

        <div v-else class="listings-grid">
          <RouterLink
            v-for="p in store.items"
            :key="p.id"
            class="listing-card"
            :to="`/product/${p.id}`"
          >
            <div class="listing-thumb">
              {{ (p.type || 'Товар').slice(0, 1) }}
            </div>

            <div class="listing-body">
              <div class="listing-title">{{ p.title }}</div>

              <div class="listing-price">{{ formatPrice(p.price) }}</div>

              <div class="listing-tags">
                <span class="tag tag--kind">{{ getListingKindLabel(p.listing_kind) }}</span>
                <span class="tag">{{ p.type || '—' }}</span>
              </div>

              <div class="listing-footer">
                <span class="muted">{{ p.city || 'Город не указан' }}</span>
                <span class="muted">{{ formatDate(p.created_at) }}</span>
              </div>
            </div>
          </RouterLink>
        </div>

        <nav
          v-if="totalPages > 1"
          class="pagination"
          aria-label="Страницы каталога"
        >
          <button
            type="button"
            class="btn pagination__nav"
            :disabled="page <= 1 || store.loading"
            @click="goToPage(page - 1)"
          >
            Назад
          </button>
          <div class="pagination__pages">
            <button
              v-for="n in pageNumbers"
              :key="n"
              type="button"
              class="btn pagination__page"
              :class="{ 'pagination__page--current': n === page }"
              :disabled="store.loading"
              @click="goToPage(n)"
            >
              {{ n }}
            </button>
          </div>
          <button
            type="button"
            class="btn pagination__nav"
            :disabled="page >= totalPages || store.loading"
            @click="goToPage(page + 1)"
          >
            Вперёд
          </button>
        </nav>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import FiltersPanel from '../components/FiltersPanel.vue'
import { getCategoryMeta, getListingKindLabel } from '../lib/catalog'
import { useProductStore } from '../stores/productStore'

const PER_PAGE = 25

const route = useRoute()
const store = useProductStore()
const filters = ref({ q: '', category: '', listingKind: '', minPrice: '', maxPrice: '' })
const page = ref(1)
const currentCategory = computed(() => getCategoryMeta(route.params.category))

const totalPages = computed(() =>
  store.total > 0 ? Math.max(1, Math.ceil(store.total / (store.perPage || PER_PAGE))) : 1
)

const rangeFrom = computed(() => {
  if (store.total === 0) return 0
  return (page.value - 1) * (store.perPage || PER_PAGE) + 1
})

const rangeTo = computed(() => {
  if (store.total === 0) return 0
  return Math.min(page.value * (store.perPage || PER_PAGE), store.total)
})

const pageNumbers = computed(() => {
  const t = totalPages.value
  const p = page.value
  const max = 7
  if (t <= max) {
    return Array.from({ length: t }, (_, i) => i + 1)
  }
  let start = Math.max(1, p - Math.floor(max / 2))
  let end = Math.min(t, start + max - 1)
  start = Math.max(1, end - max + 1)
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

const categoryTitle = computed(() => {
  if (currentCategory.value) {
    return currentCategory.value.name
  }
  return 'Последние объявления'
})

const categoryDescription = computed(() => {
  if (currentCategory.value) {
    return `Все объявления в категории "${categoryTitle.value}"`
  }
  return 'Самые свежие объявления со всех категорий'
})

function buildFilters(model) {
  return {
    q: model.q,
    category: currentCategory.value?.slug || model.category,
    listingKind: model.listingKind,
    minPrice: model.minPrice,
    maxPrice: model.maxPrice,
    page: page.value,
    perPage: PER_PAGE
  }
}

async function apply(model, resetPage = true) {
  if (resetPage) page.value = 1
  await store.loadList(buildFilters(model))
  page.value = store.page
}

async function goToPage(n) {
  const last = totalPages.value
  if (n < 1 || n > last || n === page.value) return
  page.value = n
  await store.loadList(buildFilters(filters.value))
  page.value = store.page
}

async function reload() {
  await store.loadList(buildFilters(filters.value))
  page.value = store.page
}

function formatPrice(v) {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    maximumFractionDigits: 0
  }).format(Number(v || 0))
}

function formatDate(v) {
  if (!v) return '—'
  return new Date(v).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

onMounted(() => {
  apply(filters.value)
})

watch(
  () => route.params.category,
  () => {
    apply(filters.value, true)
  }
)
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

.catalog-layout {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 20px;
  align-items: start;
}

.filters-column {
  position: sticky;
  top: 84px;
}

.listings-column {
  min-width: 0;
}

.listings-grid {
  display: grid;
  grid-template-columns: repeat(5, 240px);
  gap: 18px;
  justify-content: start;
}

.listing-card {
  display: grid;
  grid-template-rows: 190px 1fr;
  width: 240px;
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  background: var(--card-2);
  transition: transform 140ms ease, border-color 140ms ease, box-shadow 140ms ease;
  color: inherit;
  text-decoration: none;
}

.listing-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.28);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
}

.listing-thumb {
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.22), rgba(77, 211, 255, 0.14));
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 40px;
  color: rgba(255, 255, 255, 0.72);
}

.listing-body {
  display: grid;
  gap: 12px;
  padding: 16px;
  align-content: start;
}

.listing-title {
  font-weight: 700;
  line-height: 1.3;
  min-height: 3.9em;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.listing-price {
  font-weight: 800;
  font-size: 19px;
}

.listing-tags {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.listing-footer {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
}

.tag--kind {
  background: rgba(255, 152, 0, 0.2);
  color: #ffb74d;
}

.pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid var(--border);
}

.pagination__pages {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: center;
}

.pagination__nav {
  min-width: 88px;
}

.pagination__page {
  min-width: 40px;
  padding: 8px 10px;
}

.pagination__page--current {
  border-color: rgba(124, 92, 255, 0.55);
  background: rgba(124, 92, 255, 0.22);
}

@media (max-width: 1660px) {
  .listings-grid {
    grid-template-columns: repeat(4, 240px);
  }
}

@media (max-width: 1380px) {
  .catalog-layout {
    grid-template-columns: 260px minmax(0, 1fr);
  }

  .listings-grid {
    grid-template-columns: repeat(3, 240px);
  }
}

@media (max-width: 1080px) {
  .catalog-layout {
    grid-template-columns: 1fr;
  }

  .filters-column {
    position: static;
  }

  .listings-grid {
    grid-template-columns: repeat(2, 240px);
  }
}

@media (max-width: 640px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .listings-grid {
    grid-template-columns: 1fr;
    justify-content: stretch;
  }

  .listing-card {
    width: 100%;
  }
}
</style>
