<template>
  <div class="admin-page">
    <div class="card">
      <div class="card__body">
        <div class="header">
          <div>
            <h1 class="title">Панель управления пользователями</h1>
          </div>
          <button class="btn" @click="load" :disabled="loading">{{ loading ? 'Обновление...' : 'Обновить' }}</button>
        </div>

        <div class="toolbar">
          <input
            v-model.trim="query"
            class="input search"
            placeholder="Поиск: логин или почта"
          />
          <div class="count">
            <span>Найдено: <strong>{{ filteredUsers.length }}</strong></span>
            <span class="muted">Всего: {{ users.length }}</span>
          </div>
        </div>

        <div v-if="error" class="danger">{{ error }}</div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in filteredUsers" :key="u.id">
                <td>#{{ u.id }}</td>
                <td><strong>{{ u.login }}</strong></td>
                <td>{{ u.email }}</td>
                <td>{{ u.phone || '—' }}</td>
                <td>
                  <!-- Главный администратор отображаем как бейдж -->
                  <span v-if="isMainAdmin(u)" class="role role--main">
                    {{ ROLE_LABELS.main_admin }}
                  </span>
                  
                  <!-- Красивый select для остальных ролей -->
                  <div v-else class="custom-select-wrapper" :class="{ 'disabled': isSelf(u) }">
                    <select
                      class="custom-select"
                      :value="getRoleCode(u)"
                      :disabled="isSelf(u) || busyId === u.id"
                      @change="changeRole(u, $event.target.value)"
                    >
                      <option 
                        v-for="(label, code) in EDITABLE_ROLES" 
                        :key="code"
                        :value="code"
                      >
                        {{ label }}
                      </option>
                    </select>
                    <div class="custom-select-arrow"></div>
                    <div v-if="busyId === u.id" class="select-loading"></div>
                  </div>
                 </td>
                <td>
                  <span v-if="isSelf(u)" class="muted">Свой аккаунт</span>
                  <button
                    v-else
                    class="btn btn--danger btn--small"
                    :disabled="busyId === u.id || isMainAdmin(u)"
                    @click="removeUser(u)"
                  >
                    {{ busyId === u.id ? '...' : 'Удалить' }}
                  </button>
                 </td>
              </tr>
              <tr v-if="filteredUsers.length === 0">
                <td colspan="6" class="muted empty">Ничего не найдено</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { adminAssignRole, adminDeleteUser, fetchAdminUsers } from '../lib/api'
import { useAuth } from '../composables/useAuth'

// Константы ролей (без export)
const ROLE_LABELS = {
  guest: '👤 Гость',
  user: '👥 Пользователь',
  support: '🎧 Поддержка',
  moderator: '⚖️ Модератор',
  admin: '👑 Администратор',
  main_admin: '⭐ Главный администратор'  
}

// Роли, которые можно редактировать (без main_admin)
const EDITABLE_ROLES = {
  user: '👥 Пользователь',
  support: '🎧 Поддержка',
  moderator: '⚖️ Модератор',
  admin: '👑 Администратор'
}

const { user } = useAuth()
const users = ref([])
const loading = ref(false)
const error = ref('')
const query = ref('')
const busyId = ref(null)

const currentUserId = computed(() => Number(user.value?.userId || 0))

const filteredUsers = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return users.value
  return users.value.filter((u) => {
    const login = String(u.login || '').toLowerCase()
    const email = String(u.email || '').toLowerCase()
    return login.includes(q) || email.includes(q)
  })
})

function isSelf(u) {
  return Number(u.id) === currentUserId.value
}

function isMainAdmin(u) {
  // Проверяем по логину admin или по роли main_admin
  return u.login === 'admin' || String(u.db_role || '').toLowerCase() === 'main_admin'
}

function getRoleCode(u) {
  if (isMainAdmin(u)) return 'main_admin'
  return (Array.isArray(u.roles) && u.roles[0]) ? u.roles[0] : 'user'
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const data = await fetchAdminUsers()
    users.value = data.items || []
  } catch (e) {
    error.value = e?.message || 'Ошибка загрузки'
  } finally {
    loading.value = false
  }
}

async function changeRole(targetUser, roleCode) {
  if (isSelf(targetUser) || isMainAdmin(targetUser)) return
  
  busyId.value = targetUser.id
  error.value = ''
  try {
    await adminAssignRole(targetUser.id, roleCode)
    await load()
  } catch (e) {
    error.value = e?.message || 'Ошибка изменения роли'
  } finally {
    busyId.value = null
  }
}

async function removeUser(targetUser) {
  if (isSelf(targetUser) || isMainAdmin(targetUser)) return
  
  if (!confirm(`Вы уверены, что хотите удалить пользователя "${targetUser.login}"?`)) {
    return
  }
  
  busyId.value = targetUser.id
  error.value = ''
  try {
    await adminDeleteUser(targetUser.id)
    await load()
  } catch (e) {
    error.value = e?.message || 'Ошибка удаления'
  } finally {
    busyId.value = null
  }
}

onMounted(load)
</script>

<style scoped>
.admin-page { display: grid; gap: 20px; }
.header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
.toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 14px; flex-wrap: wrap; }
.search { min-width: 320px; max-width: 520px; }
.count { display: flex; gap: 10px; align-items: center; }
.table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: 12px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px 10px; border-bottom: 1px solid var(--border); text-align: left; }
.table th { font-size: 12px; text-transform: uppercase; color: var(--muted); letter-spacing: .04em; }
.table tr:hover td { background: rgba(124, 92, 255, 0.04); }
.empty { text-align: center; padding: 18px; }
.danger { color: var(--danger); margin-bottom: 10px; }

/* Красивый кастомный select */
.custom-select-wrapper {
  position: relative;
  display: inline-block;
  min-width: 160px;
}

.custom-select {
  appearance: none;
  -webkit-appearance: none;
  background: var(--bg-primary, #fff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 10px;
  padding: 8px 32px 8px 12px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary, #1e293b);
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  font-family: inherit;
}

.custom-select:hover:not(:disabled) {
  border-color: #7c5cff;
  background: var(--bg-hover, #f8fafc);
}

.custom-select:focus {
  outline: none;
  border-color: #7c5cff;
  box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.1);
}

.custom-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: var(--bg-disabled, #f1f5f9);
}

.custom-select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid var(--muted, #64748b);
  pointer-events: none;
  transition: transform 0.2s ease;
}

.custom-select-wrapper:hover .custom-select-arrow {
  border-top-color: #7c5cff;
}

/* Стили для опций select */
.custom-select option {
  padding: 8px;
  font-size: 13px;
}

.role {
  display: inline-flex;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  gap: 6px;
  align-items: center;
  white-space: nowrap;
}

.role--main {
  background: linear-gradient(135deg, rgba(255, 196, 0, 0.15), rgba(255, 152, 0, 0.1));
  color: #e67e22;
  border: 1px solid rgba(230, 126, 34, 0.3);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn--small {
  padding: 7px 12px;
  font-size: 12px;
}

/* Анимация загрузки для select */
.select-loading {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  border: 2px solid var(--border, #e2e8f0);
  border-top-color: #7c5cff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  pointer-events: none;
  background: var(--bg-primary, #fff);
}

@keyframes spin {
  to { transform: translateY(-50%) rotate(360deg); }
}

/* Адаптивность */
@media (max-width: 768px) {
  .custom-select-wrapper {
    min-width: 130px;
  }
  
  .custom-select {
    font-size: 12px;
    padding: 6px 28px 6px 10px;
  }
  
  .role--main {
    font-size: 11px;
    padding: 4px 8px;
  }
}

/* Стили для бейджа загрузки */
.muted {
  color: var(--muted, #64748b);
}

.btn--danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>