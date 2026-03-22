<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card__body">
        <h2 class="title" style="margin: 0 0 20px 0">Регистрация</h2>
        
        <form @submit.prevent="submit" style="display: grid; gap: 16px">
          <div class="row">
            <label class="form-group">
              <span class="muted">Имя</span>
              <input 
                v-model.trim="form.name" 
                class="input" 
                type="text" 
                placeholder="Иван"
                required
              />
            </label>

            <label class="form-group">
              <span class="muted">Фамилия</span>
              <input 
                v-model.trim="form.surname" 
                class="input" 
                type="text" 
                placeholder="Иванов"
                required
              />
            </label>
          </div>

          <label class="form-group">
            <span class="muted">Email</span>
            <input 
              v-model.trim="form.email" 
              class="input" 
              type="email" 
              placeholder="user@example.com"
              required
            />
          </label>

          <label class="form-group">
            <span class="muted">Телефон</span>
            <input 
              v-model.trim="form.phone" 
              class="input" 
              type="tel" 
              placeholder="+7 (999) 123-45-67"
            />
          </label>

          <label class="form-group">
            <span class="muted">Пароль</span>
            <input 
              v-model="form.password" 
              class="input" 
              type="password" 
              placeholder="••••••••"
              minlength="6"
              required
            />
          </label>

          <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer">
            <input type="checkbox" v-model="form.agree" required />
            <span class="muted" style="font-size: 13px; line-height: 1.4">
              Я соглашаюсь с <RouterLink to="/privacy" class="muted">условиями использования</RouterLink> 
              и <RouterLink to="/privacy" class="muted">политикой конфиденциальности</RouterLink>
            </span>
          </label>

          <button 
            class="btn btn--primary" 
            type="submit" 
            :disabled="loading"
            style="width: 100%"
          >
            {{ loading ? 'Регистрация...' : 'Создать аккаунт' }}
          </button>
        </form>

        <div v-if="error" class="danger" style="margin-top: 12px; text-align: center">
          {{ error }}
        </div>

        <div style="text-align: center; margin-top: 16px">
          <span class="muted">Уже есть аккаунт? </span>
          <RouterLink to="/login" class="btn btn--secondary">
            Войти
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
  name: '',
  surname: '',
  email: '',
  phone: '',
  password: '',
  agree: false
})

async function submit() {
  if (!form.value.agree) {
    error.value = 'Необходимо согласиться с условиями'
    return
  }
  
  loading.value = true
  error.value = null
  
  try {
    // DEMO-режим: имитация регистрации
    localStorage.setItem('demoAuth', `Demo user:1 roles:seller,customer`)
    
    // В реальном проекте:
    // await register({ ...form.value })
    
    router.push('/profile')
  } catch (e) {
    error.value = e?.message || 'Ошибка регистрации'
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
  max-width: 500px;
  width: 100%;
}

.form-group {
  display: grid;
  gap: 6px;
}

.danger {
  color: var(--danger);
  text-align: center;
}
</style>