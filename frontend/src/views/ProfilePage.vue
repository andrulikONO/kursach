<template>
  <div class="profile-page">
    <div class="profile-header">
      <h1 class="title">Личный кабинет</h1>
      <button class="btn btn--secondary" @click="logout">Выйти</button>
    </div>

    <div class="grid">
      <!-- Боковое меню -->
      <aside class="sidebar card">
        <nav class="profile-nav">
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'ads' }"
            @click="activeTab = 'ads'"
          >
            📦 Мои объявления
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'favorites' }"
            @click="activeTab = 'favorites'"
          >
            ❤️ Избранное
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'messages' }"
            @click="activeTab = 'messages'"
          >
            💬 Сообщения
          </button>
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'settings' }"
            @click="activeTab = 'settings'"
          >
            ⚙️ Настройки
          </button>
        </nav>
      </aside>

      <!-- Контент вкладки -->
      <main class="content">
        <!-- Мои объявления -->
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
                  Просмотров: {{ ad.views || 0 }} · {{ formatDate(ad.created_at) }}
                </div>
              </div>
              <div style="display: grid; gap: 8px; padding: 12px">
                <RouterLink :to="`/edit/${ad.id}`" class="btn btn--secondary">
                  ✏️
                </RouterLink>
                <button class="btn btn--secondary" @click="deleteAd(ad.id)">
                  🗑️
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Избранное -->
        <div v-if="activeTab === 'favorites'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Избранное</h2>
          <div class="card">
            <div class="card__body" style="text-align: center; padding: 40px">
              <div style="font-size: 48px; margin-bottom: 16px">❤️</div>
              <p class="muted">Список избранного пуст</p>
              <RouterLink to="/" class="btn btn--primary" style="margin-top: 16px">
                Перейти в каталог
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Сообщения -->
        <div v-if="activeTab === 'messages'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Сообщения</h2>
          <div class="card">
            <div class="card__body" style="text-align: center; padding: 40px">
              <div style="font-size: 48px; margin-bottom: 16px">💬</div>
              <p class="muted">Чаты появятся после первых сообщений</p>
            </div>
          </div>
        </div>

        <!-- Настройки -->
        <div v-if="activeTab === 'settings'" class="tab-content">
          <h2 class="title" style="margin: 0 0 16px 0">Настройки профиля</h2>
          <form class="card" @submit.prevent="saveSettings" style="display: grid; gap: 16px">
            <div class="card__body" style="display: grid; gap: 12px">
              <div class="row">
                <label class="form-group">
                  <span class="muted">Имя</span>
                  <input v-model="settings.name" class="input" />
                </label>
                <label class="form-group">
                  <span class="muted">Фамилия</span>
                  <input v-model="settings.surname" class="input" />
                </label>
              </div>
              <label class="form-group">
                <span class="muted">Email</span>
                <input v-model="settings.email" class="input" type="email" />
              </label>
              <label class="form-group">
                <span class="muted">Телефон</span>
                <input v-model="settings.phone" class="input" type="tel" />
              </label>
              <button class="btn btn--primary" type="submit">Сохранить изменения</button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()
const activeTab = ref('ads')

// Демо-данные (в реальном проекте — загрузка из API)
const userAds = ref([
  { id: 1, title: 'iPhone 13 128GB', price: 50000, type: 'Электроника', status: 'active', views: 42, created_at: '2024-01-15' },
  { id: 2, title: 'Кроссовки Nike', price: 8000, type: 'Обувь', status: 'sold', views: 18, created_at: '2024-01-10' },
])

const settings = ref({
  name: 'Иван',
  surname: 'Иванов',
  email: 'ivan@example.com',
  phone: '+7 (999) 123-45-67'
})

function formatPrice(v) {
  return new Intl.NumberFormat('ru-RU', { 
    style: 'currency', 
    currency: 'RUB', 
    maximumFractionDigits: 0 
  }).format(Number(v || 0))
}

function formatDate(v) {
  return new Date(v).toLocaleDateString('ru-RU')
}

function statusLabel(status) {
  const labels = { active: 'Активно', sold: 'Продано', draft: 'Черновик' }
  return labels[status] || status
}

function logout() {
  localStorage.removeItem('demoAuth')
  router.push('/login')
}

function deleteAd(id) {
  if (confirm('Удалить это объявление?')) {
    userAds.value = userAds.value.filter(ad => ad.id !== id)
  }
}

function saveSettings() {
  alert('Настройки сохранены (демо)')
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

.tag.sold {
  background: rgba(220, 53, 69, 0.2);
  color: var(--danger);
}

.form-group {
  display: grid;
  gap: 6px;
}
</style>