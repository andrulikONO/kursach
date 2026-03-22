<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal card">
      <div class="modal__header">
        <h2 class="title" style="margin: 0">Регистрация</h2>
        <button class="btn btn--small" type="button" @click="$emit('close')">✕</button>
      </div>

      <div class="modal__body">
        <AuthRegisterForm @success="onSuccess" @error="onError" />

        <div v-if="error" class="danger" style="margin-top: 12px; text-align: center">
          {{ error }}
        </div>

        <div class="modal__divider">
          <span class="muted">или</span>
        </div>

        <div class="modal__footer">
          <span class="muted">Уже есть аккаунт?</span>
          <button class="btn btn--secondary" type="button" @click="$emit('open-login')">Войти</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AuthRegisterForm from './AuthRegisterForm.vue'
import { useAuth } from '../composables/useAuth'

const emit = defineEmits(['close', 'open-login', 'success'])

const { checkAuth } = useAuth()
const error = ref(null)

function onSuccess() {
  error.value = null
  checkAuth()
  emit('success')
}

function onError(msg) {
  error.value = msg
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
  max-width: 560px;
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
  max-height: min(85vh, 720px);
  overflow-y: auto;
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

.danger {
  color: var(--danger);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
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
