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
      🛡️ Панель админа
    </RouterLink>

    <div class="grid">
      <aside class="sidebar card">
        <nav class="profile-nav">
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'about' }" 
            @click="setTab('about')"
          >
            👤 Обо мне
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'ads' }" 
            @click="setTab('ads')"
          >
            📦 Мои объявления
            <span v-if="activeAdsCount > 0" class="nav-badge">{{ activeAdsCount }}</span>
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'favorites' }" 
            @click="setTab('favorites')"
          >
            ❤️ Избранное
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'settings' }" 
            @click="setTab('settings')"
          >
            ⚙️ Настройки
          </button>
        </nav>
      </aside>

      <main class="content">
        <!-- ✅ Обо мне -->
        <div v-if="activeTab === 'about'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Информация о профиле</h2>
          <div v-if="loading" class="card">
            <div class="card__body loading-state">
              <div class="spinner"></div>
              <span class="muted">Загрузка...</span>
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
              <span class="role-badge" :class="`role--${primaryRole}`">
                {{ roleLabel }}
              </span>
            </div>
              <div class="profile-row">
                <span class="muted" style="min-width: 120px">Аккаунт создан:</span>
                <span>{{ formatDate(profile.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Мои объявления -->
        <div v-if="activeTab === 'ads'" class="tab-content">
          <div class="split" style="margin-bottom: 16px">
            <h2 class="title" style="margin: 0">Мои объявления</h2>
            <RouterLink to="/new" class="btn btn--primary">+ Добавить</RouterLink>
          </div>
          <div v-if="activeAds.length === 0" class="card">
            <div class="card__body empty-state">
              <div style="font-size: 48px; margin-bottom: 16px">📭</div>
              <p class="muted">У вас пока нет объявлений</p>
              <RouterLink to="/new" class="btn btn--primary" style="margin-top: 16px">
                Разместить первое
              </RouterLink>
            </div>
          </div>
          <div v-else class="list">
            <div v-for="ad in activeAds" :key="ad.id" class="item">
              <div class="thumb">{{ (ad.type || '?').slice(0, 1) }}</div>
              <div style="padding: 12px; flex: 1">
                <div class="split">
                  <div class="title">{{ ad.title }}</div>
                  <span :class="['tag', ad.status]">{{ statusLabel(ad.status) }}</span>
                </div>
                <div class="price" style="margin: 4px 0">{{ formatPrice(ad.price) }}</div>
                <div class="muted" style="font-size: 13px">{{ formatDate(ad.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Избранное -->
        <div v-if="activeTab === 'favorites'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Избранное</h2>
          <div class="card">
            <div class="card__body empty-state">
              <div style="font-size: 48px; margin-bottom: 16px">❤️</div>
              <p class="muted">Список избранного пуст</p>
              <RouterLink to="/" class="btn btn--primary" style="margin-top: 16px">
                Перейти в каталог
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- ✅ Настройки (только личные данные) -->
        <div v-if="activeTab === 'settings'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Настройки профиля</h2>
          
          <div class="card">
            <div class="card__body" style="display: grid; gap: 20px">
              <!-- Личные данные -->
              <div class="settings-section">
                <h3 class="settings-title">👤 Личная информация</h3>
                
                <div class="settings-row">
                  <label class="settings-label">
                    <span class="muted">Имя</span>
                  </label>
                  <input 
                    v-model="editForm.firstName" 
                    class="input" 
                    :class="{ error: errors.firstName }"
                    placeholder="Ваше имя"
                  />
                </div>
                
                <div class="settings-row">
                  <label class="settings-label">
                    <span class="muted">Фамилия</span>
                  </label>
                  <input 
                    v-model="editForm.lastName" 
                    class="input" 
                    :class="{ error: errors.lastName }"
                    placeholder="Ваша фамилия"
                  />
                </div>
                
                <div class="settings-row">
                  <label class="settings-label">
                    <span class="muted">Email</span>
                  </label>
                  <input 
                    v-model="editForm.email" 
                    class="input" 
                    type="email"
                    :class="{ error: errors.email }"
                    placeholder="your@email.com"
                  />
                </div>
                
                <div class="settings-row">
                  <label class="settings-label">
                    <span class="muted">Телефон</span>
                  </label>
                  <input 
                    v-model="editForm.phone" 
                    class="input" 
                    type="tel"
                    :class="{ error: errors.phone }"
                    placeholder="+7 (999) 123-45-67"
                  />
                </div>
              </div>

              <!-- Кнопки -->
              <div class="settings-actions">
                <button 
                  class="btn btn--primary" 
                  @click="saveProfile"
                  :disabled="saving"
                >
                  {{ saving ? 'Сохранение...' : 'Сохранить изменения' }}
                </button>
                <button 
                  class="btn btn--secondary" 
                  @click="resetForm"
                  :disabled="saving"
                >
                  Отмена
                </button>
              </div>

              <!-- Сообщения -->
              <div v-if="saveError" class="danger">{{ saveError }}</div>
              <div v-if="saveSuccess" class="success-message">
                ✅ Данные успешно обновлены!
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { fetchMe, fetchMyProducts } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const route = useRoute()
const { logout, user } = useAuth()

// ✅ Вкладка по умолчанию: 'about', если в URL нет ?tab=settings
const activeTab = ref(route.query.tab === 'settings' ? 'settings' : 'about')
const loading = ref(false)
const profile = ref(null)
const allUserAds = ref([])

// ✅ Форма редактирования
const editForm = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: ''
})
const errors = ref({})
const saving = ref(false)
const saveError = ref(null)
const saveSuccess = ref(false)

// ✅ Отслеживаем изменения в URL
watch(() => route.query.tab, (newTab) => {
  if (['about', 'ads', 'favorites', 'settings'].includes(newTab)) {
    activeTab.value = newTab
  }
})

// ✅ Переключение вкладок с обновлением URL
function setTab(tab) {
  activeTab.value = tab
  router.replace({ query: { ...route.query, tab } })
}

// ✅ Активные объявления (без удалённых)
const activeAds = computed(() => {
  return allUserAds.value.filter(ad => ad.status !== 'deleted')
})
const activeAdsCount = computed(() => activeAds.value.length)

const isAdmin = computed(() => {
  const roles = profile.value?.roles || user.value?.roles || []
  return roles.includes('admin')
})

const primaryRole = computed(() => {
  const roles = profile.value?.roles || user.value?.roles || []
  const userId = profile.value?.id || user.value?.userId
  return getPrimaryRole(roles, userId)  // ✅ userId вторым параметром
})

const roleLabel = computed(() => getRoleLabel(primaryRole.value))

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
  const labels = { active: 'Активно', deleted: 'Удалено', draft: 'Черновик' }
  return labels[status] || status
}

// ✅ Загрузка профиля
async function loadProfile() {
  loading.value = true
  try {
    const [me, myProducts] = await Promise.all([
      fetchMe(),
      fetchMyProducts()
    ])
    profile.value = me
    allUserAds.value = myProducts.items || []
    
    // Заполняем форму редактирования
    editForm.value = {
      firstName: me.first_name || '',
      lastName: me.last_name || '',
      email: me.email || '',
      phone: me.phone || ''
    }
  } catch (e) {
    console.error('Failed to load profile:', e)
    router.push('/login')
  } finally {
    loading.value = false
  }
}

// ✅ Валидация формы
function validateForm() {
  errors.value = {}
  
  if (!editForm.value.firstName.trim()) {
    errors.value.firstName = 'Введите имя'
  }
  if (!editForm.value.lastName.trim()) {
    errors.value.lastName = 'Введите фамилию'
  }
  if (!editForm.value.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(editForm.value.email)) {
    errors.value.email = 'Введите корректный email'
  }
  if (!editForm.value.phone.trim() || !/^\+7\d{10}$/.test(editForm.value.phone.replace(/\D/g, ''))) {
    errors.value.phone = 'Введите телефон в формате +7...'
  }
  
  return Object.keys(errors.value).length === 0
}

// ✅ Сохранение изменений (демо-режим)
async function saveProfile() {
  if (!validateForm()) return
  
  saving.value = true
  saveError.value = null
  saveSuccess.value = false
  
  try {
    // TODO: API для обновления профиля
    // await api.updateProfile(editForm.value)
    
    // Демо: обновляем локально
    if (profile.value) {
      profile.value.first_name = editForm.value.firstName
      profile.value.last_name = editForm.value.lastName
      profile.value.email = editForm.value.email
      profile.value.phone = editForm.value.phone
    }
    
    saveSuccess.value = true
    setTimeout(() => saveSuccess.value = false, 3000)
  } catch (e) {
    saveError.value = e?.message || 'Ошибка при сохранении'
  } finally {
    saving.value = false
  }
}

function resetForm() {
  if (profile.value) {
    editForm.value = {
      firstName: profile.value.first_name || '',
      lastName: profile.value.last_name || '',
      email: profile.value.email || '',
      phone: profile.value.phone || ''
    }
  }
  errors.value = {}
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
  .grid { grid-template-columns: 1fr; }
}

.sidebar { height: fit-content; }

.profile-nav { display: grid; gap: 4px; }

.nav-item {
  padding: 12px 16px;
  border: none;
  background: transparent;
  color: var(--text);
  text-align: left;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.nav-item:hover { background: rgba(255, 255, 255, 0.08); }
.nav-item.active { background: var(--primary); color: white; }

.nav-badge {
  padding: 2px 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.tab-content { display: grid; gap: 16px; }

/* Loading */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px;
}
.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty state */
.empty-state { text-align: center; padding: 40px; }

/* Profile fields */
.profile-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid var(--border);
}
.profile-row:last-child { border-bottom: none; }

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

/* Ads list */
.list { display: grid; gap: 12px; }
.item {
  display: flex;
  gap: 12px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, border-color 0.2s;
}
.item:hover { transform: translateY(-2px); border-color: var(--primary); }
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
.tag.active { background: rgba(0, 170, 102, 0.2); color: #00aa66; }

/* ===== Настройки ===== */
.settings-section {
  display: grid;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border);
}
.settings-section:last-child { border-bottom: none; padding-bottom: 0; }

.settings-title {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.settings-row {
  display: grid;
  gap: 8px;
}

.settings-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
}

.settings-row .input {
  max-width: 400px;
}

.settings-row .input.error {
  border-color: var(--danger);
}

.settings-actions {
  display: flex;
  gap: 12px;
  padding-top: 16px;
}

.success-message {
  padding: 12px 16px;
  background: rgba(0, 170, 102, 0.15);
  border: 1px solid rgba(0, 170, 102, 0.3);
  border-radius: 8px;
  color: #00aa66;
  font-weight: 500;
}

.danger { color: var(--danger); }
</style>