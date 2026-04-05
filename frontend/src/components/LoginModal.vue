<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal card">
      <div class="modal__header">
        <h2 class="title" style="margin: 0">Вход</h2>
        <button class="btn btn--small" @click="$emit('close')">✕</button>
      </div>

      <div class="modal__body">
        <form @submit.prevent="submit" style="display: grid; gap: 16px">
          <label class="form-group">
            <span class="muted">Логин или email</span>
            <input
              v-model.trim="form.identifier"
              class="input"
              type="text"
              placeholder="admin или user@mail.ru"
              required
              autocomplete="username"
            />
          </label>

          <div class="form-group">
            <span class="muted">Пароль</span>
            <div class="input-row">
              <input
                v-model="form.password"
                class="input"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                autocomplete="current-password"
              />
              <button type="button" class="btn btn--ghost" tabindex="-1" @click="showPassword = !showPassword">
                {{ showPassword ? 'Скрыть' : 'Показать' }}
              </button>
            </div>
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

        <div class="modal__divider">
          <span class="muted">или</span>
        </div>

        <div class="modal__footer">
          <span class="muted">Нет аккаунта?</span>
          <button class="btn btn--secondary" @click="$emit('open-register')">
            Зарегистрироваться
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuth } from '../composables/useAuth'

const emit = defineEmits(['close', 'open-register', 'success'])

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
    emit('success')
  } catch (e) {
    error.value = e?.message || 'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  animation: fadeIn 0.2s ease;
}

.modal {
  max-width: 420px;
  width: 100%;
  background: #1a1f35;
  animation: slideUp 0.3s ease;
}

.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}

.modal__body {
  padding: 20px;
}

.modal__divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 20px 0;
}

.modal__divider::before,
.modal__divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

.modal__footer {
  display: grid;
  gap: 10px;
  text-align: center;
}

.btn--small {
  padding: 6px 10px;
  font-size: 14px;
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

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 480px) {
  .modal-overlay {
    padding: 10px;
    align-items: flex-end;
  }
  
  .modal {
    max-width: 100%;
    border-radius: 16px 16px 0 0;
  }
}
</style>