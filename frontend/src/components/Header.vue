<template>
  <div class="topbar">
    <div class="container topbar__inner">
      <!-- Левая часть: логотип + поддержка -->
      <div class="header-left">
        <!-- Логотип - ведёт на главную (последние объявления) -->
        <RouterLink class="brand" to="/" title="Последние объявления">
          <span class="brand__dot" aria-hidden="true" />
          <span>Объявления</span>
        </RouterLink>
        
        <!-- Иконка техподдержки -->
        <RouterLink 
          class="support-link" 
          to="/support" 
          title="Служба поддержки"
        >
          <span class="support-icon">🎧</span>
        </RouterLink>
      </div>

      <!-- Правая часть: навигация -->
      <nav class="nav">
        <CategoriesDropdown />
        <RouterLink class="btn btn--primary" to="/new">Подать объявление</RouterLink>
        
        <template v-if="isAuth">
          <RouterLink class="btn" to="/profile">Профиль</RouterLink>
          <button class="btn" @click="$emit('logout')">Выйти</button>
        </template>
        <button v-else class="btn" @click="$emit('open-login')">Войти</button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import CategoriesDropdown from './CategoriesDropdown.vue'

defineProps({
  isAuth: { type: Boolean, default: false }
})

defineEmits(['open-login', 'logout'])
</script>

<style scoped>
.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.support-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border);
  transition: all 0.2s ease;
  text-decoration: none;
  color: inherit;
}

.support-link:hover {
  background: rgba(124, 92, 255, 0.2);
  border-color: var(--primary);
  transform: translateY(-1px);
}

.support-icon {
  font-size: 20px;
  filter: grayscale(0.2);
}

.support-link:hover .support-icon {
  filter: grayscale(0);
}

.nav {
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>