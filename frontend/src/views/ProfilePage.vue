<template>
  <div class="profile-page">
    <div class="profile-header">
      <h1 class="title">Личный кабинет</h1>
      <button class="btn btn--secondary" type="button" @click="doLogout">Выйти</button>
    </div>

    <RouterLink
      v-if="isAdmin"
      to="/admin"
      class="btn btn--primary"
      style="margin-bottom: 20px; display: inline-flex"
    >
      Панель админа
    </RouterLink>

    <div class="grid">
      <aside class="sidebar card">
        <nav class="profile-nav">
          <button class="nav-item" :class="{ active: activeTab === 'about' }" @click="activeTab = 'about'">
            Обо мне
          </button>
          <button class="nav-item" :class="{ active: activeTab === 'ads' }" @click="activeTab = 'ads'">
            Мои объявления
          </button>
          <button class="nav-item" :class="{ active: activeTab === 'favorites' }" @click="activeTab = 'favorites'">
            Избранное
          </button>
        </nav>
      </aside>

      <main class="content">
        <div v-if="activeTab === 'about'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Информация о профиле</h2>

          <div v-if="loading" class="card">
            <div class="card__body" style="text-align: center; padding: 40px">
              <div class="muted">Загрузка...</div>
            </div>
          </div>

          <div v-else-if="profile" class="card">
            <div class="card__body" style="display: grid; gap: 16px">
              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Логин:</span>
                <strong>{{ profile.login }}</strong>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">ФИО:</span>
                <span>{{ profile.first_name }} {{ profile.last_name }}</span>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Email:</span>
                <span>{{ profile.email }}</span>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Телефон:</span>
                <span>{{ profile.phone }}</span>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Пол:</span>
                <span>{{ profile.gender === 'MALE' ? 'Мужской' : 'Женский' }}</span>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Роль:</span>
                <span class="role-badge" :class="`role--${profile.primaryRole}`">
                  {{ profile.primaryRoleLabel }}
                </span>
              </div>

              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Аккаунт создан:</span>
                <span>{{ formatDate(profile.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'ads'" class="tab-content">
          <div class="split" style="margin-bottom: 16px">
            <h2 class="title" style="margin: 0">Мои объявления</h2>
            <RouterLink to="/new" class="btn btn--primary">+ Добавить</RouterLink>
          </div>

          <div v-if="userAds.length === 0" class="card">
            <div class="card__body" style="text-align: center; padding: 40px">
              <div style="font-size: 48px; margin-bottom: 16px">📭</div>
              <p class="muted">У вас пока нет объявлений</p>
              <RouterLink to="/new" class="btn btn--primary" style="margin-top: 16px">
                Разместить первое
              </RouterLink>
            </div>
          </div>

          <div v-else class="list">
            <div v-for="ad in userAds" :key="ad.id" class="item">
              <div class="thumb">{{ (ad.type || '?').slice(0, 1) }}</div>
              <div style="padding: 12px; flex: 1">
                <div class="split">
                  <div class="title">{{ ad.title }}</div>
                  <span :class="['tag', ad.status]">{{ statusLabel(ad.status) }}</span>
                </div>
                <div class="price" style="margin: 4px 0">{{ formatPrice(ad.price) }}</div>
                <div class="muted" style="font-size: 13px">
                  {{ formatDate(ad.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'favorites'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Избранное</h2>
          <div class="card">
            <div class="card__body" style="text-align: center; padding: 40px">
              <div style="font-size: 48px; margin-bottom: 16px">❤</div>
              <p class="muted">Список избранного пуст</p>
              <RouterLink to="/" class="btn btn--primary" style="margin-top: 16px">
                Перейти в каталог
              </RouterLink>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { fetchMe, fetchMyProducts } from '../lib/api'
import { useAuth } from '../composables/useAuth'
import { getPrimaryRole, getRoleLabel, sortRoles } from '../lib/roles'

const router = useRouter()
const { logout, user, refreshProfile } = useAuth()
const activeTab = ref('about')
const loading = ref(false)
const profile = ref(null)
const userAds = ref([])

const isAdmin = computed(() => {
  const roles = profile.value?.roles || user.value?.roles || []
  return roles.includes('admin')
})

function formatPrice(v) {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    maximumFractionDigits: 0
  }).format(Number(v || 0))
}

function formatDate(v) {
  if (!v) return '—'
  return new Date(v).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function statusLabel(status) {
  const labels = { active: 'Активно', deleted: 'Удалено' }
  return labels[status] || status
}

async function loadProfile() {
  loading.value = true
  try {
    const [me, authProfile, myProducts] = await Promise.all([
      fetchMe(),
      refreshProfile(),
      fetchMyProducts()
    ])

    const roles = sortRoles(me.roles || authProfile?.roles || [])
    const primaryRole = getPrimaryRole(roles)

    profile.value = {
      ...me,
      roles,
      primaryRole,
      primaryRoleLabel: getRoleLabel(primaryRole)
    }
    userAds.value = myProducts.items || []
  } catch {
    router.push('/login')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProfile()
})

function doLogout() {
  logout()
  router.push('/')
}
</script>

<style scoped>
.profile-page {
  display: grid;
  gap: 20px;
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.grid {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
  }
}

.sidebar {
  height: fit-content;
}

.profile-nav {
  display: grid;
  gap: 4px;
}

.nav-item {
  padding: 12px 16px;
  border: none;
  background: transparent;
  color: var(--text);
  text-align: left;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.2s;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.nav-item.active {
  background: var(--primary);
  color: white;
}

.tab-content {
  display: grid;
  gap: 16px;
}

.profile-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid var(--border);
}

.profile-row:last-child {
  border-bottom: none;
}

.role-badge {
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }

.list {
  display: grid;
  gap: 12px;
}

.item {
  display: flex;
  gap: 12px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

.thumb {
  width: 100px;
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.22), rgba(77, 211, 255, 0.14));
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 24px;
}

.tag {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
}

.tag.active {
  background: rgba(0, 170, 102, 0.2);
  color: #00aa66;
}
</style>
