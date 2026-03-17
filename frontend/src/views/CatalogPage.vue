<template>
  <div style="display: grid; gap: 14px">
    <div class="split" style="align-items: flex-end">
      <div>
        <div class="title" style="font-size: 22px">Каталог</div>
        <div class="muted">Скелет: список + фильтры + переход на карточку</div>
      </div>
      <button class="btn" type="button" @click="reload">Обновить</button>
    </div>

    <div class="grid">
      <section>
        <div class="card" style="margin-bottom: 14px">
          <div class="card__body">
            <div class="muted" style="font-size: 13px">
              Показано: <b>{{ store.items.length }}</b>
              <span v-if="store.loading"> · загрузка...</span>
            </div>
            <div v-if="store.error" class="danger" style="margin-top: 10px">{{ store.error }}</div>
          </div>
        </div>

        <div class="list">
          <RouterLink v-for="p in store.items" :key="p.id" class="item" :to="`/product/${p.id}`">
            <div class="thumb">{{ (p.type || 'Товар').slice(0, 1) }}</div>
            <div style="padding: 12px; display: grid; gap: 8px">
              <div class="title">{{ p.title }}</div>
              <div class="split">
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

      <aside>
        <FiltersPanel :model="filters" @update:model="filters = $event" @apply="apply" />
      </aside>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import FiltersPanel from '../components/FiltersPanel.vue'
import { useProductStore } from '../stores/productStore'

const store = useProductStore()
const filters = ref({ q: '', type: '', minPrice: '', maxPrice: '' })

function apply(f) {
  store.loadList(f)
}

function reload() {
  apply(filters.value)
}

function formatPrice(v) {
  const n = Number(v || 0)
  return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(n)
}

onMounted(() => {
  apply(filters.value)
})
</script>

