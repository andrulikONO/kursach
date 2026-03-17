<template>
  <div style="display: grid; gap: 14px">
    <div class="split">
      <div>
        <div class="title" style="font-size: 22px">Карточка товара</div>
        <div class="muted">Страница объявления</div>
      </div>
      <RouterLink class="btn" to="/">Назад в каталог</RouterLink>
    </div>

    <div v-if="store.selectedLoading" class="card">
      <div class="card__body">Загрузка...</div>
    </div>

    <div v-else-if="store.selectedError" class="card">
      <div class="card__body">
        <div class="danger">{{ store.selectedError }}</div>
      </div>
    </div>

    <div v-else-if="store.selected" class="grid">
      <section class="card">
        <div class="thumb" style="height: 240px">{{ (store.selected.type || 'Товар').slice(0, 1) }}</div>
        <div class="card__body" style="display: grid; gap: 10px">
          <div class="title" style="font-size: 22px">{{ store.selected.title }}</div>
          <div class="price" style="font-size: 20px">{{ formatPrice(store.selected.price) }}</div>
          <div class="tags">
            <span class="tag">Тип: {{ store.selected.type || '—' }}</span>
            <span class="tag">Город: {{ store.selected.city || '—' }}</span>
          </div>
          <div class="muted" style="white-space: pre-wrap">{{ store.selected.description || 'Описание отсутствует' }}</div>
        </div>
      </section>

      <aside class="card">
        <div class="card__body" style="display: grid; gap: 10px">
          <div class="title">Контакты (скелет)</div>
          <div class="muted">Пока без личных данных — можно расширить позже.</div>
          <div class="split">
            <button class="btn" type="button" disabled>Показать телефон</button>
            <button class="btn" type="button" disabled>Написать</button>
          </div>
          <div class="muted" style="font-size: 13px">ID: {{ store.selected.id }}</div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useProductStore } from '../stores/productStore'

const props = defineProps({ id: { type: String, required: true } })
const store = useProductStore()

function formatPrice(v) {
  const n = Number(v || 0)
  return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(n)
}

async function load() {
  await store.loadOne(props.id)
}

onMounted(load)
watch(() => props.id, load)
</script>

