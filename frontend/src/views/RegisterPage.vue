<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card__body">
        <h2 class="title" style="margin: 0 0 20px 0">Регистрация</h2>

        <AuthRegisterForm @success="onSuccess" @error="onError" />

        <div v-if="error" class="danger" style="margin-top: 12px; text-align: center">
          {{ error }}
        </div>

        <div style="text-align: center; margin-top: 16px">
          <span class="muted">Уже есть аккаунт? </span>
          <RouterLink to="/login" class="btn btn--secondary"> Войти </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AuthRegisterForm from '../components/AuthRegisterForm.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { checkAuth } = useAuth()
const error = ref(null)

function onSuccess() {
  error.value = null
  checkAuth()
  router.push('/profile')
}

function onError(msg) {
  error.value = msg
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
  max-width: 560px;
  width: 100%;
}

.danger {
  color: var(--danger);
}
</style>
