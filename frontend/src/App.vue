<template>
  <div class="app">
    <!-- Передаем user в Header для отображения логина и роли -->
    <Header 
      :is-auth="isAuth" 
      :user="user"
      @open-login="openLoginModal" 
      @logout="handleLogout" 
    />

    <main class="container" style="padding: 18px 16px 28px">
      <RouterView />
    </main>

    <Footer />

    <LoginModal 
      v-if="showLoginModal" 
      @close="closeLoginModal" 
      @open-register="openRegisterModal" 
      @success="handleAuthSuccess" 
    />

    <RegisterModal 
      v-if="showRegisterModal" 
      @close="closeRegisterModal" 
      @open-login="openLoginModal" 
      @success="handleAuthSuccess" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'
import LoginModal from './components/LoginModal.vue'
import RegisterModal from './components/RegisterModal.vue'
import { useAuth, initAuth } from './composables/useAuth'

const route = useRoute()
const router = useRouter()
const { isAuth, user, logout } = useAuth()


onMounted(() => {
  initAuth()
})

const showLoginModal = ref(false)
const showRegisterModal = ref(false)

function openLoginModal() {
  showLoginModal.value = true
  showRegisterModal.value = false
}

function closeLoginModal() {
  showLoginModal.value = false
  if (route.query?.login != null && route.query.login !== '') {
    const q = { ...route.query }
    delete q.login
    router.replace({ query: q })
  }
}

function openRegisterModal() {
  showRegisterModal.value = true
  showLoginModal.value = false
}

function closeRegisterModal() {
  showRegisterModal.value = false
}

async function handleAuthSuccess() {
  closeLoginModal()
  closeRegisterModal()
  if (typeof localStorage !== 'undefined') {
    localStorage.removeItem('userProfile')
  }
  await initAuth()
}

function handleLogout() {
  logout()
  window.location.reload()
}
</script>

<style scoped>
.app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>