<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal card">
      <div class="modal__header">
        <h2 class="title" style="margin: 0">Регистрация</h2>
        <button class="btn btn--small" @click="$emit('close')">✕</button>
      </div>

      <div class="modal__body">
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

        <div class="modal__divider">
          <span class="muted">или</span>
        </div>

        <div class="modal__footer">
          <span class="muted">Уже есть аккаунт?</span>
          <button class="btn btn--secondary" @click="$emit('open-login')">
            Войти
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuth } from '../composables/useAuth'

const emit = defineEmits(['close', 'open-login', 'success'])

const { register } = useAuth()

const loading = ref(false)
const error = ref(null)

const form = reactive({
  name: '',
  surname: '',
  email: '',
  password: ''
})

async function submit() {
  loading.value = true
  error.value = null

  try {
    await register(form)
    emit('success')
  } catch (e) {
    error.value = e?.message || 'Ошибка регистрации'
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
  max-width: 500px;
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