<template>
  <div class="topbar">
    <div class="container topbar__inner">
      <!-- Левая часть: логотип + поддержка -->
      <div class="header-left">
        <RouterLink class="brand" to="/">
          <span class="brand__dot" aria-hidden="true" />
          <span>Объявления</span>
        </RouterLink>
        
        <RouterLink class="support-link" to="/support" title="Служба поддержки">
          <span class="support-icon">🎧</span>
        </RouterLink>
      </div>

      <!-- Правая часть: навигация + тема + пользователь -->
      <nav class="nav">
        <RouterLink class="btn" to="/">Каталог</RouterLink>
        <RouterLink class="btn btn--primary" to="/new">Подать объявление</RouterLink>
        
        <!-- ✅ Переключатель темы (всегда виден) -->
        <button 
          class="btn btn--theme" 
          @click="toggleTheme" 
          :title="`Тема: ${currentThemeLabel}`"
        >
          <span class="theme-icon">{{ themeIcon }}</span>
        </button>
        
        <!-- ✅ Бургер-меню для авторизованных -->
        <template v-if="isAuth && user">
          <div class="user-menu" :class="{ open: isMenuOpen }" v-click-outside="closeMenu">
            <button class="user-btn" @click="toggleMenu">
              <div class="user-avatar">{{ userAvatar }}</div>
              <div class="user-info">
                <span class="user-name">{{ userName }}</span>
                <span class="user-role" :class="`role--${userRole}`">{{ userRoleLabel }}</span>
              </div>
              <span class="menu-arrow" :class="{ rotated: isMenuOpen }">▼</span>
            </button>
            
            <div class="menu-dropdown">
              <RouterLink to="/profile" class="menu-item" @click="closeMenu">
                <span class="menu-icon">👤</span>
                <span>Профиль</span>
              </RouterLink>
              <div class="menu-divider"></div>
              <button class="menu-item menu-logout" @click="handleLogout">
                <span class="menu-icon">🚪</span>
                <span>Выйти</span>
              </button>
            </div>
          </div>
        </template>
        
        <!-- Кнопка входа для гостей -->
        <button v-else class="btn" @click="$emit('open-login')">Войти</button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
// ✅ Импортируем helper для ролей
import { getPrimaryRole, getRoleLabel } from '../lib/roles'

const props = defineProps({
  isAuth: { type: Boolean, default: false },
  user: { 
    type: Object, 
    default: () => ({ 
      firstName: '', 
      lastName: '', 
      login: '', 
      roles: [],
      userId: null  // ✅ Добавляем userId
    }) 
  }
})

const emit = defineEmits(['open-login', 'logout'])

const isMenuOpen = ref(false)

// ✅ Тема оформления
const currentTheme = ref('light')

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark' || saved === 'light' || saved === 'auto') {
    currentTheme.value = saved
    applyTheme(saved)
  }
})

const themeIcon = computed(() => {
  if (currentTheme.value === 'dark') return '🌙'
  else return '☀️'
})

const currentThemeLabel = computed(() => {
  const labels = { light: 'Светлая', dark: 'Тёмная', auto: 'Авто' }
  return labels[currentTheme.value] || 'Светлая'
})

function toggleTheme() {
  const order = ['light', 'dark', 'auto']
  const idx = order.indexOf(currentTheme.value)
  const next = order[(idx + 1) % order.length]
  currentTheme.value = next
  localStorage.setItem('theme', next)
  applyTheme(next)
}

function applyTheme(theme) {
  const root = document.documentElement
  if (theme === 'dark') {
    root.classList.add('dark-theme')
  } else if (theme === 'light') {
    root.classList.remove('dark-theme')
  } else if (theme === 'auto') {
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    root.classList.toggle('dark-theme', isDark)
  }
}

// ✅ Данные пользователя для меню
const userAvatar = computed(() => {
  return props.user.firstName?.[0]?.toUpperCase() || props.user.login?.[0]?.toUpperCase() || 'U'
})

const userName = computed(() => {
  const fn = String(props.user.firstName || '').trim()
  const ln = String(props.user.lastName || '').trim()
  const full = [fn, ln].filter(Boolean).join(' ')
  if (full) return full
  return props.user.login || 'Пользователь'
})

// ✅ ИСПОЛЬЗУЕМ getPrimaryRole с userId для правильного определения роли
const userRole = computed(() => {
  const roles = props.user.roles || []
  const userId = props.user.userId
  return getPrimaryRole(roles, userId)  // ✅ Передаём userId
})

const userRoleLabel = computed(() => getRoleLabel(userRole.value))  // ✅ Используем getRoleLabel

const isAdminUser = computed(() => (props.user.roles || []).includes('admin'))

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function closeMenu() {
  isMenuOpen.value = false
}

function handleLogout() {
  closeMenu()
  emit('logout')
}

// ✅ Директива для закрытия при клике вне меню
const vClickOutside = {
  mounted: (el, binding) => {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted: (el) => {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }

.role--main_admin { 
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.3), rgba(255, 152, 0, 0.3)); 
  color: #ffd700;
  border: 1px solid rgba(255, 215, 0, 0.5);
  font-weight: 700;
}
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

/* ===== Переключатель темы ===== */
.btn--theme {
  padding: 10px 12px;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn--theme:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.theme-icon {
  transition: transform 0.2s ease;
}

.btn--theme:hover .theme-icon {
  transform: rotate(15deg);
}

/* ===== Бургер-меню пользователя ===== */
.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: inherit;
}

.user-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: var(--primary);
}

.user-avatar {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary), var(--primary-2));
  border-radius: 10px;
  font-weight: 700;
  font-size: 16px;
  color: white;
}

.user-info {
  display: grid;
  gap: 2px;
  text-align: left;
}

.user-name {
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.user-role {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
  font-weight: 600;
  width: fit-content;
}

.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }

.menu-arrow {
  font-size: 10px;
  transition: transform 0.2s ease;
  color: var(--muted);
}

.menu-arrow.rotated {
  transform: rotate(180deg);
}

/* ===== Выпадающее меню ===== */
.menu-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 200px;
  background: #1a1f35;
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
  z-index: 1000;
  overflow: hidden;
  animation: slideDown 0.2s ease;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: var(--text);
  text-decoration: none;
  transition: background 0.2s ease;
  font-size: 14px;
  border: none;
  background: transparent;
  width: 100%;
  cursor: pointer;
  text-align: left;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.menu-item.menu-logout {
  color: var(--danger);
}

.menu-item.menu-logout:hover {
  background: rgba(255, 77, 109, 0.15);
}

.menu-icon {
  font-size: 18px;
  width: 24px;
  text-align: center;
}

.menu-divider {
  height: 1px;
  background: var(--border);
  margin: 4px 0;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ===== Адаптив ===== */
@media (max-width: 768px) {
  .user-info { display: none; }
  .user-btn { padding: 6px; }
  .menu-dropdown { right: -10px; }
}

@media (max-width: 640px) {
  .nav .btn:not(.btn--theme) { display: none; }
  .header-left { gap: 12px; }
}
</style>