<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card__body">
        <h2 class="title" style="margin: 0 0 20px 0">Вход</h2>

        <form @submit.prevent="submit" style="display: grid; gap: 16px">
          <label class="form-group">
            <span class="muted">Логин или email</span>
            <input v-model.trim="form.identifier" class="input" type="text" required autocomplete="username" />
          </label>

          <div class="form-group">
            <span class="muted">Пароль</span>
            <div class="input-row">
              <input
                v-model="form.password"
                class="input"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
              />
              <button type="button" class="btn btn--ghost" tabindex="-1" @click="showPassword = !showPassword">
                {{ showPassword ? 'Скрыть' : 'Показать' }}
              </button>
            </div>
          </div>

          <button class="btn btn--primary" type="submit" :disabled="loading" style="width: 100%">
            {{ loading ? 'Вход...' : 'Войти' }}
          </button>
        </form>

        <div v-if="error" class="danger" style="margin-top: 12px; text-align: center">
          {{ error }}
        </div>

        <div class="auth-divider">
          <span class="muted">или</span>
        </div>

        <div style="text-align: center">
          <span class="muted">Нет аккаунта? </span>
          <RouterLink to="/register" class="btn btn--secondary"> Зарегистрироваться </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login } = useAuth()
const loading = ref(false)
const error = ref(null)
const showPassword = ref(false)

const form = reactive({
  identifier: '',
  password: ''
})

async function submit() {
  loading.value = true
  error.value = null
  try {
    await login(form.identifier, form.password)
    router.push('/')
  } catch (e) {
    error.value = e?.message || 'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 20px;
}

.auth-card {
  max-width: 420px;
  width: 100%;
}

.form-group {
  display: grid;
  gap: 6px;
}

.input-row {
  display: flex;
  gap: 8px;
  align-items: stretch;
}

.input-row .input {
  flex: 1;
}

.btn--ghost {
  flex-shrink: 0;
  white-space: nowrap;
  padding: 8px 10px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
}

.auth-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 20px 0;
}

.auth-divider::before,
.auth-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

.danger {
  color: var(--danger);
  text-align: center;
}
</style>
