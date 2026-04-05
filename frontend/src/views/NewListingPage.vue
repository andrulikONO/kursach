<template>
  <div style="display: grid; gap: 14px; max-width: 820px">
    <div class="split">
      <div>
        <div class="title" style="font-size: 22px">Подача объявления</div>
        <div class="muted">Телефон для связи подставляется из вашего профиля</div>
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
            <input v-model.trim="form.title" class="input" required placeholder="Например: iPhone 13 128GB" />
          </label>

          <label style="display: grid; gap: 6px">
            <span class="muted">Цена (₽)</span>
            <input v-model="form.price" class="input" inputmode="numeric" required placeholder="15000" />
          </label>
        </div>

        <div class="row">
          <TypeSelect v-model="form.type" label="Тип вещи" :types="types" />
          <label style="display: grid; gap: 6px">
            <span class="muted">Город</span>
            <input v-model.trim="form.city" class="input" placeholder="Москва" />
          </label>
        </div>

        <label style="display: grid; gap: 6px">
          <span class="muted">Телефон в объявлении</span>
          <input class="input" type="text" readonly :value="user?.phone || '—'" />
          <span class="muted" style="font-size: 12px">Указывается при регистрации; в форме не редактируется</span>
        </label>

        <label style="display: grid; gap: 6px">
          <span class="muted">Описание</span>
          <textarea v-model.trim="form.description" class="textarea" placeholder="Состояние, комплектация, причина продажи..." />
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
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import TypeSelect from '../components/TypeSelect.vue'
import { createProduct } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const { isAuth, user, refreshProfile } = useAuth()

const types = ['Одежда', 'Обувь', 'Электроника', 'Дом']

const form = ref({
  title: '',
  price: '',
  type: '',
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
      type: form.value.type || null,
      city: form.value.city || null,
      description: form.value.description || null
    }
    const created = await createProduct(payload)
    success.value = created
    form.value = { title: '', price: '', type: '', city: '', description: '' }
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    submitting.value = false
  }
}
</script>
