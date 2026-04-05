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
          <button
            v-if="isAdmin"
            type="button"
            class="btn danger-outline"
            :disabled="deleting"
            @click="removeListing"
          >
            {{ deleting ? 'Удаление...' : 'Удалить объявление (админ)' }}
          </button>
        </div>
      </section>

      <aside class="card">
        <div class="card__body" style="display: grid; gap: 10px">
          <div class="title">Контакты</div>
          <div v-if="store.selected.contact_phone" class="muted">
            Телефон: <b>{{ store.selected.contact_phone }}</b>
          </div>
          <div v-else class="muted">Телефон не указан</div>
          <div class="muted" style="font-size: 13px">ID объявления: {{ store.selected.id }}</div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useProductStore } from '../stores/productStore'
import { deleteProduct } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const props = defineProps({ id: { type: String, required: true } })
const store = useProductStore()
const router = useRouter()
const { refreshProfile, user } = useAuth()
const deleting = ref(false)

const isAdmin = computed(() => (user.value?.roles || []).includes('admin'))

onMounted(() => {
  refreshProfile()
})

function formatPrice(v) {
  const n = Number(v || 0)
  return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(n)
}

async function load() {
  await store.loadOne(props.id)
}

async function removeListing() {
  if (!confirm('Удалить объявление?')) return
  deleting.value = true
  try {
    await deleteProduct(props.id)
    router.push('/')
  } catch (e) {
    alert(e?.message || String(e))
  } finally {
    deleting.value = false
  }
}

onMounted(load)
watch(() => props.id, load)
</script>

<style scoped>
.danger-outline {
  border-color: var(--danger);
  color: var(--danger);
}
</style>
