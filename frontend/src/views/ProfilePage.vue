<template>
  <div class="profile-page">
    <div class="profile-header">
      <h1 class="title">Личный кабинет</h1>
    </div>

    <!-- Горизонтальные вкладки -->
    <div class="tabs-container">
      <div class="tabs">
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'about' }" 
          @click="setTab('about')"
        >
          👤 Обо мне
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'ads' }" 
          @click="setTab('ads')"
        >
          📦 Мои объявления
          <span v-if="activeAdsCount > 0" class="tab-badge">{{ activeAdsCount }}</span>
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'favorites' }" 
          @click="setTab('favorites')"
        >
          ❤️ Избранное
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'settings' }" 
          @click="setTab('settings')"
        >
          ⚙️ Настройки
        </button>
      </div>
    </div>

    <!-- Контент вкладок -->
    <div class="tab-content-wrapper">
      <!-- ✅ Обо мне -->
      <div v-if="activeTab === 'about'" class="tab-content">
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
        <div v-else class="card">
          <div class="card__body empty-state">
            <p class="muted">Не удалось отобразить профиль. Попробуйте обновить страницу.</p>
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
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { fetchMe, fetchMyProducts, updateMe } from '../lib/api'
import { getPrimaryRole, getRoleLabel } from '../lib/roles'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const route = useRoute()
const { logout, user, updateUserRealtime } = useAuth()

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
const initialForm = ref({
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
    
    // ✅ Нормализуем данные перед заполнением формы
    editForm.value = {
      firstName: me.first_name?.trim() || '',
      lastName: me.last_name?.trim() || '',
      email: me.email?.trim()?.toLowerCase() || '',  // ✅ trim + lowercase
      phone: me.phone?.trim() || ''  // ✅ trim
    }
    initialForm.value = { ...editForm.value }
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
  
  // Имя
  const firstName = editForm.value.firstName?.trim() || ''
  if (!firstName) {
    errors.value.firstName = 'Введите имя'
  }
  
  // Фамилия
  const lastName = editForm.value.lastName?.trim() || ''
  if (!lastName) {
    errors.value.lastName = 'Введите фамилию'
  }
  
  // ✅ Email: только если поле не пустое + более мягкая проверка
  const email = editForm.value.email?.trim()?.toLowerCase() || ''
  const initialEmail = initialForm.value.email?.trim()?.toLowerCase() || ''
  const emailChanged = email !== initialEmail
  if (emailChanged && email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errors.value.email = 'Введите корректный email'
  }
  
  // ✅ Телефон: нормализуем перед проверкой
  const phone = editForm.value.phone?.trim() || ''
  const initialPhone = initialForm.value.phone?.trim() || ''
  const phoneChanged = phone !== initialPhone
  const phoneClean = phone.replace(/[^\d+]/g, '')
  const phoneValid = /^\+?7\d{10}$/.test(phoneClean) || /^\d{11}$/.test(phoneClean)
  
  if (phoneChanged && phone && !phoneValid) {
    errors.value.phone = 'Введите телефон в формате +7...'
  }
  
  return Object.keys(errors.value).length === 0
}

// ✅ Сохранение изменений
async function saveProfile() {
  if (!validateForm()) return
  
  saving.value = true
  saveError.value = null
  saveSuccess.value = false
  
  try {
    const firstName = editForm.value.firstName?.trim() || ''
    const lastName = editForm.value.lastName?.trim() || ''
    const email = editForm.value.email?.trim()?.toLowerCase() || ''
    const phone = editForm.value.phone?.trim() || ''
    const fio = [firstName, lastName].filter(Boolean).join(' ').trim()

    const updated = await updateMe({ fio, email, phone })

    if (profile.value) {
      profile.value = {
        ...profile.value,
        ...updated,
        first_name: updated.first_name ?? firstName,
        last_name: updated.last_name ?? lastName,
        email: updated.email ?? email,
        phone: updated.phone ?? phone
      }
    }

    updateUserRealtime({
      first_name: updated.first_name ?? firstName,
      last_name: updated.last_name ?? lastName,
      fio: updated.fio ?? fio,
      email: updated.email ?? email,
      phone: updated.phone ?? phone
    })

    editForm.value = {
      firstName: profile.value?.first_name?.trim() || firstName,
      lastName: profile.value?.last_name?.trim() || lastName,
      email: profile.value?.email?.trim()?.toLowerCase() || email,
      phone: profile.value?.phone?.trim() || phone
    }
    initialForm.value = {
      firstName: editForm.value.firstName?.trim() || '',
      lastName: editForm.value.lastName?.trim() || '',
      email: editForm.value.email?.trim()?.toLowerCase() || '',
      phone: editForm.value.phone?.trim() || ''
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
    initialForm.value = { ...editForm.value }
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

/* Вкладки сверху */
.tabs-container {
  border-bottom: 2px solid var(--border);
}

.tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap; /* Вкладки переносятся на новую строку на ПК */
}

/* На мобильных устройствах добавляем скролл только при переполнении */
@media (max-width: 768px) {
  .tabs {
    flex-wrap: nowrap; /* Запрещаем перенос */
    overflow-x: auto; /* Включаем скролл */
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch; /* Плавный скролл на iOS */
  }
  
  /* Кастомизация скролла (делаем красивым) */
  .tabs::-webkit-scrollbar {
    height: 3px;
  }
  
  .tabs::-webkit-scrollbar-track {
    background: var(--border);
    border-radius: 3px;
  }
  
  .tabs::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 3px;
  }
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: transparent;
  color: var(--text);
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s;
  position: relative;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0; /* Запрещаем сжатие кнопок при скролле */
}

/* На ПК кнопки могут занимать равное пространство */
@media (min-width: 769px) {
  .tab-btn {
    flex: 1; /* Равномерно распределяем кнопки */
    justify-content: center;
  }
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--primary);
}

.tab-btn.active {
  color: var(--primary);
  background: rgba(124, 92, 255, 0.1);
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--primary);
  border-radius: 2px;
}

.tab-badge {
  padding: 2px 8px;
  background: rgba(124, 92, 255, 0.2);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: var(--primary);
}

.tab-btn.active .tab-badge {
  background: rgba(124, 92, 255, 0.3);
}

/* Контент вкладок */
.tab-content-wrapper {
  margin-top: 4px;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

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

@keyframes spin { 
  to { transform: rotate(360deg); } 
}

/* Empty state */
.empty-state { 
  text-align: center; 
  padding: 40px; 
}

/* Profile fields */
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

.role--user { 
  background: rgba(124, 92, 255, 0.2); 
  color: var(--primary-2); 
}

.role--admin { 
  background: rgba(255, 77, 109, 0.2); 
  color: var(--danger); 
}

.role--support { 
  background: rgba(255, 152, 0, 0.2); 
  color: #ff9800; 
}

/* Ads list */
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
  cursor: pointer;
  transition: transform 0.2s, border-color 0.2s;
}

.item:hover { 
  transform: translateY(-2px); 
  border-color: var(--primary); 
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

/* Настройки */
.settings-section {
  display: grid;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border);
}

.settings-section:last-child { 
  border-bottom: none; 
  padding-bottom: 0; 
}

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

.danger { 
  color: var(--danger); 
}

/* Мобильная адаптация */
@media (max-width: 768px) {
  .tab-btn {
    padding: 10px 20px;
    font-size: 14px;
    flex: 0 0 auto; /* Фиксированная ширина на мобилках */
  }
  
  .settings-row .input {
    max-width: 100%;
  }
  
  .thumb {
    width: 70px;
    font-size: 18px;
  }
}
</style>
