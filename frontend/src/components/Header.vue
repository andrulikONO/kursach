<template>
  <div class="topbar">
    <div class="container topbar__inner">
      <div class="header-left">
        <RouterLink class="brand" to="/">
          <span class="brand__dot" aria-hidden="true" />
          <span>Объявления</span>
        </RouterLink>

        <CategoriesDropdown />

        <RouterLink class="support-link" to="/support" title="Служба поддержки">
          <span class="support-icon">🎧</span>
        </RouterLink>
      </div>

      <nav class="nav">
        <RouterLink class="btn btn--primary" to="/new">Подать объявление</RouterLink>

        <template v-if="isAuth && user">
          <div class="user-info">
            <span class="user-login">{{ user.login }}</span>
            <span class="user-role" :class="`role--${user.primaryRole}`">
              {{ user.primaryRoleLabel }}
            </span>
          </div>

          <RouterLink
            v-if="user.roles?.includes('admin')"
            class="btn btn--support"
            to="/admin"
          >
            Админ
          </RouterLink>

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
  isAuth: { type: Boolean, default: false },
  user: {
    type: Object,
    default: () => ({
      login: '',
      roles: [],
      primaryRole: '',
      primaryRoleLabel: ''
    })
  }
})

defineEmits(['open-login', 'logout'])
</script>

<style scoped>
.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
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
}

.nav {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border: 1px solid var(--border);
}

.user-login {
  font-weight: 600;
  font-size: 14px;
}

.user-role {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
  font-weight: 600;
}

.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }

.btn--support {
  background: rgba(124, 92, 255, 0.2);
  border-color: var(--primary);
  color: var(--primary-2);
}
</style>
