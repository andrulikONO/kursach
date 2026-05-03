<template>
  <div class="admin-page">
    <div class="page-header">
      <div>
        <h1 class="title">🛡️ Админ-панель</h1>
        <p class="muted">Управление пользователями</p>
      </div>
      <RouterLink to="/profile" class="btn btn--secondary">← В профиль</RouterLink>
    </div>

    <div v-if="!isAdmin" class="card">
      <div class="card__body danger">Доступ только для роли admin</div>
    </div>

    <div v-else>
      <div v-if="error" class="danger" style="margin-bottom: 12px">{{ error }}</div>
      
      <div class="card">
        <div class="card__body" style="overflow-x: auto">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Блокировка</th>
                <th>Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in items" :key="u.id">
                <td>#{{ u.id }}</td>
                <td><strong>{{ u.login }}</strong></td>
                <td>{{ u.email }}</td>
                <td>{{ u.phone }}</td>
                <td>
                  <!-- ✅ Показываем только одну роль -->
                  <span 
                    v-if="u.id === 1"
                    class="role-badge role--main-admin"
                  >
                    👑 Главный админ
                  </span>
                  <span 
                    v-else
                    class="role-badge" 
                    :class="`role--${getMainRole(u.roles)}`"
                  >
                    {{ getRoleLabel(getMainRole(u.roles)) }}
                  </span>
                </td>
                <td>
                  <span :class="['status-badge', u.is_blocked ? 'status--blocked' : 'status--active']">
                    {{ u.is_blocked ? 'Заблокирован' : 'Активен' }}
                  </span>
                </td>
                <td>
                  <!-- ✅ Пустое поле для главного админа (ID=1) -->
                  <template v-if="u.id === 1">
                    <span class="main-admin-badge"></span>
                  </template>
                  
                  <!-- ✅ Для всех остальных -->
                  <template v-else>
                    <div class="actions">
                      <button
                        v-if="!u.is_blocked && u.id !== currentUserId"
                        type="button"
                        class="btn btn--small btn--danger"
                        :disabled="busyId === u.id"
                        @click="block(u.id)"
                        title="Заблокировать"
                      >
                        🔒
                      </button>
                      <button
                        v-else-if="u.is_blocked && u.id !== currentUserId"
                        type="button"
                        class="btn btn--small btn--success"
                        :disabled="busyId === u.id"
                        @click="unblock(u.id)"
                        title="Разблокировать"
                      >
                        🔓
                      </button>
                      
                      <!-- Индикатор если это текущий пользователь -->
                      <span v-if="u.id === currentUserId" class="current-user-badge">Вы</span>
                    </div>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { fetchAdminUsers, adminBlockUser, adminUnblockUser } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const { user, refreshProfile } = useAuth()
const isAdmin = computed(() => (user.value?.roles || []).includes('admin'))
const currentUserId = computed(() => user.value?.userId)

const items = ref([])
const error = ref(null)
const busyId = ref(null)

// ✅ Получаем основную роль (первую из списка, кроме 'user')
function getMainRole(roles) {
  if (!roles || roles.length === 0) return 'user'
  // Если есть admin — это основная роль
  if (roles.includes('admin')) return 'admin'
  // Иначе первая роль
  return roles[0]
}

function getRoleLabel(code) {
  const map = {
    user: 'Пользователь',
    support: 'Поддержка',
    moderator: 'Модератор',
    admin: 'Админ'
  }
  return map[code] || code
}

async function load() {
  error.value = null
  try {
    await refreshProfile()
    if (!isAdmin.value) return
    
    const data = await fetchAdminUsers()
    items.value = data.items || []
  } catch (e) {
    error.value = e?.message || String(e)
  }
}

async function block(id) {
  busyId.value = id
  try {
    await adminBlockUser(id)
    await load()
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    busyId.value = null
  }
}

async function unblock(id) {
  busyId.value = id
  try {
    await adminUnblockUser(id)
    await load()
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    busyId.value = null
  }
}

onMounted(load)
</script>

<style scoped>
.admin-page {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h1 {
  margin: 0 0 4px 0;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table th,
.table td {
  padding: 12px 8px;
  border-bottom: 1px solid var(--border);
  text-align: left;
}

.table th {
  font-weight: 600;
  color: var(--muted);
  font-size: 12px;
  text-transform: uppercase;
}

.table tr:hover {
  background: rgba(255, 255, 255, 0.02);
}

.role-badge {
  display: inline-flex;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }

/* ✅ Главный админ */
.role--main-admin {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.3), rgba(255, 152, 0, 0.3));
  color: #ffd700;
  border: 1px solid rgba(255, 215, 0, 0.5);
}

.status-badge {
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status--active {
  background: rgba(0, 170, 102, 0.2);
  color: #00aa66;
}

.status--blocked {
  background: rgba(255, 77, 109, 0.2);
  color: var(--danger);
}

.actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn--small {
  padding: 6px 10px;
  font-size: 13px;
}

.btn--success {
  background: rgba(0, 170, 102, 0.2);
  border-color: #00aa66;
  color: #00aa66;
}

.btn--danger {
  background: rgba(255, 77, 109, 0.2);
  border-color: var(--danger);
  color: var(--danger);
}

.main-admin-badge {
  font-size: 18px;
  color: #ffd700;
}

.current-user-badge {
  padding: 4px 10px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: var(--muted);
}

.danger {
  color: var(--danger);
}
</style>