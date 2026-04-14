<template>
  <div class="admin-page">
    <div class="page-header">
      <div>
        <h1 class="title">🛡️ Админ-панель</h1>
        <p class="muted">Управление пользователями и ролями</p>
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
                <th>Роли</th>
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
                  <div class="roles-list">
                    <span 
                      v-for="role in u.roles" 
                      :key="role"
                      class="role-badge"
                      :class="`role--${role}`"
                    >
                      {{ getRoleLabel(role) }}
                      <button 
                        v-if="role !== 'user'" 
                        class="role-remove"
                        @click="removeRole(u.id, role)"
                        title="Удалить роль"
                      >
                        ✕
                      </button>
                    </span>
                  </div>
                </td>
                <td>
                  <span :class="['status-badge', u.is_blocked ? 'status--blocked' : 'status--active']">
                    {{ u.is_blocked ? 'Заблокирован' : 'Активен' }}
                  </span>
                </td>
                <td>
                  <div class="actions">
                    <!-- Выбор роли для назначения -->
                    <select 
                      v-model="selectedRole[u.id]" 
                      class="input select"
                      style="width: 140px; font-size: 13px; padding: 4px 8px"
                    >
                      <option value="">Добавить роль...</option>
                      <option 
                        v-for="role in availableRoles" 
                        :key="role.code"
                        :value="role.code"
                        :disabled="u.roles.includes(role.code)"
                      >
                        {{ role.name }}
                      </option>
                    </select>
                    <button 
                      class="btn btn--primary btn--small"
                      @click="assignRole(u.id)"
                      :disabled="!selectedRole[u.id] || busyId === u.id"
                      title="Назначить роль"
                    >
                      ✓
                    </button>
                    
                    <!-- Блокировка -->
                    <button
                      v-if="!u.is_blocked"
                      type="button"
                      class="btn btn--small btn--danger"
                      :disabled="busyId === u.id"
                      @click="block(u.id)"
                      title="Заблокировать"
                    >
                      🔒
                    </button>
                    <button
                      v-else
                      type="button"
                      class="btn btn--small btn--success"
                      :disabled="busyId === u.id"
                      @click="unblock(u.id)"
                      title="Разблокировать"
                    >
                      🔓
                    </button>
                  </div>
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
import { fetchAdminUsers, fetchAdminRoles, adminAssignRole, adminRemoveRole, adminBlockUser, adminUnblockUser } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const { user, refreshProfile } = useAuth()
const isAdmin = computed(() => (user.value?.roles || []).includes('admin'))

const items = ref([])
const availableRoles = ref([])
const selectedRole = ref({})
const error = ref(null)
const busyId = ref(null)

async function load() {
  error.value = null
  try {
    await refreshProfile()
    if (!isAdmin.value) return
    
    const [usersData, rolesData] = await Promise.all([
      fetchAdminUsers(),
      fetchAdminRoles()
    ])
    
    items.value = usersData.items || []
    availableRoles.value = rolesData.roles || []
  } catch (e) {
    error.value = e?.message || String(e)
  }
}

async function assignRole(userId) {
  const roleCode = selectedRole.value[userId]
  if (!roleCode) return
  
  busyId.value = userId
  try {
    await adminAssignRole(userId, roleCode)
    selectedRole.value[userId] = ''
    await load()
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    busyId.value = null
  }
}

async function removeRole(userId, roleCode) {
  if (!confirm(`Удалить роль "${getRoleLabel(roleCode)}" у пользователя?`)) return
  
  busyId.value = userId
  try {
    await adminRemoveRole(userId, roleCode)
    await load()
  } catch (e) {
    error.value = e?.message || String(e)
  } finally {
    busyId.value = null
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

function getRoleLabel(code) {
  const map = {
    user: 'Пользователь',
    support: 'Поддержка',
    moderator: 'Модератор',
    admin: 'Админ'
  }
  return map[code] || code
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

.roles-list {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.role--user { background: rgba(124, 92, 255, 0.2); color: var(--primary-2); }
.role--support { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.role--moderator { background: rgba(0, 188, 212, 0.2); color: #00bcd4; }
.role--admin { background: rgba(255, 77, 109, 0.2); color: var(--danger); }

.role-remove {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font-size: 14px;
  padding: 0;
  opacity: 0.7;
}

.role-remove:hover {
  opacity: 1;
  color: var(--danger);
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

.danger {
  color: var(--danger);
}
</style>