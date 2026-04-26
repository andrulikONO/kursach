<template>
  <div style="display: grid; gap: 14px; max-width: 820px">
    <div class="split">
      <div>
        <div class="title" style="font-size: 22px">Подача объявления</div>
        <div class="muted">Телефон для связи подтягивается из профиля и будет скрыт до нажатия кнопки в карточке</div>
      </div>
      <RouterLink class="btn" to="/">Каталог</RouterLink>
    </div>

    <div v-if="!isAuth" class="card">
      <div class="card__body">
        <p class="muted">Войдите, чтобы разместить объявление.</p>
        <RouterLink class="btn btn--primary" to="/login">Войти</RouterLink>
      </div>
    </div>

    <div v-else-if="user?.isBlocked" class="card">
      <div class="card__body danger">
        Аккаунт заблокирован. Создание новых объявлений недоступно.
      </div>
    </div>

    <form v-else class="card" @submit.prevent="submit">
      <div class="card__body" style="display: grid; gap: 12px">
        <div class="row">
          <label style="display: grid; gap: 6px">
            <span class="muted">Заголовок</span>
            <input v-model.trim="form.title" class="input" required placeholder="Например: Куплю iPhone 13 или продам диван" />
          </label>

          <label style="display: grid; gap: 6px">
            <span class="muted">Цена (₽)</span>
            <input v-model="form.price" class="input" inputmode="numeric" required placeholder="15000" />
          </label>
        </div>

        <div class="row">
          <CategorySelect
            v-model="form.categorySlug"
            :include-all="false"
            empty-label="Выберите категорию"
          />
          <ListingKindSelect
            v-model="form.listingKind"
            :include-all="false"
            empty-label="Выберите тип"
          />
        </div>

        <div class="row">
          <CitySelect v-model="form.city" empty-label="Все города" />

          <label style="display: grid; gap: 6px">
            <span class="muted">Телефон в объявлении</span>
            <input class="input" type="text" readonly :value="user?.phone || '—'" />
          </label>
        </div>

        <label style="display: grid; gap: 6px">
          <span class="muted">Описание</span>
          <textarea v-model.trim="form.description" class="textarea" placeholder="Состояние, детали, условия сделки, что именно ищете или продаете..." />
        </label>

        <div class="split">
          <button class="btn btn--primary" type="submit" :disabled="submitting || !user?.phone">
            {{ submitting ? 'Отправка...' : 'Опубликовать' }}
          </button>
        </div>

        <div v-if="error" class="danger">{{ error }}</div>
        <div v-if="success" class="card" style="box-shadow: none">
          <div class="card__body">
            Готово. Создано объявление: <b>#{{ success.id }}</b>.
            <RouterLink class="btn" :to="`/product/${success.id}`" style="margin-left: 10px">Открыть</RouterLink>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import CategorySelect from '../components/CategorySelect.vue'
import CitySelect from '../components/CitySelect.vue'
import ListingKindSelect from '../components/ListingKindSelect.vue'
import { createProduct } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const { isAuth, user, refreshProfile } = useAuth()

const form = ref({
  title: '',
  price: '',
  categorySlug: '',
  listingKind: 'sale',
  city: '',
  description: ''
})

const submitting = ref(false)
const error = ref(null)
const success = ref(null)

onMounted(async () => {
  if (isAuth.value) {
    await refreshProfile()
  }
})

async function submit() {
  submitting.value = true
  error.value = null
  success.value = null
  try {
    const payload = {
      title: form.value.title,
      price: Number(form.value.price),
      categorySlug: form.value.categorySlug,
      listingKind: form.value.listingKind || 'sale',
      city: form.value.city || null,
      description: form.value.description || null
    }
    const created = await createProduct(payload)
    success.value = created
    form.value = {
      title: '',
      price: '',
      categorySlug: '',
      listingKind: 'sale',
      city: '',
      description: ''
    }
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    submitting.value = false
  }
}
</script>
