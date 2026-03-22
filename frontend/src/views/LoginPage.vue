<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card__body">
        <h2 class="title" style="margin: 0 0 20px 0">Вход</h2>
        
        <form @submit.prevent="submit" style="display: grid; gap: 16px">
          <label class="form-group">
            <span class="muted">Email или телефон</span>
            <input 
              v-model="form.identifier" 
              class="input" 
              type="text" 
              placeholder="user@example.com"
              required
            />
          </label>

          <label class="form-group">
            <span class="muted">Пароль</span>
            <input 
              v-model="form.password" 
              class="input" 
              type="password" 
              placeholder="••••••••"
              required
            />
          </label>

          <div class="split" style="align-items: center">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer">
              <input type="checkbox" v-model="form.remember" />
              <span class="muted" style="font-size: 14px">Запомнить меня</span>
            </label>
            <RouterLink to="/forgot-password" class="muted" style="font-size: 14px">
              Забыли пароль?
            </RouterLink>
          </div>

          <button 
            class="btn btn--primary" 
            type="submit" 
            :disabled="loading"
            style="width: 100%"
          >
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
          <RouterLink to="/register" class="btn btn--secondary">
            Зарегистрироваться
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const error = ref(null)

const form = ref({
  identifier: '',
  password: '',
  remember: false
})

async function submit() {
  loading.value = true
  error.value = null
  
  try {
    // DEMO-режим: просто сохраняем в localStorage
    localStorage.setItem('demoAuth', `Demo user:1 roles:seller,customer`)
    
    // В реальном проекте здесь был бы API-запрос:
    // await login({ email: form.value.identifier, password: form.value.password })
    
    // Редирект в профиль или на главную
    router.push('/profile')
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