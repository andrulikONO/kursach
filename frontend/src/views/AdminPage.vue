<template>
  <div class="admin-page">
    <div class="split" style="margin-bottom: 16px">
      <h1 class="title" style="margin: 0">Администрирование</h1>
      <RouterLink class="btn" to="/">На главную</RouterLink>
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
                <th />
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in items" :key="u.id">
                <td>{{ u.id }}</td>
                <td>{{ u.login }}</td>
                <td>{{ u.email }}</td>
                <td>{{ u.phone }}</td>
                <td>{{ (u.roles || []).join(', ') }}</td>
                <td>{{ u.is_blocked ? 'да' : 'нет' }}</td>
                <td>
                  <button
                    v-if="!u.is_blocked"
                    type="button"
                    class="btn btn--small"
                    :disabled="busyId === u.id"
                    @click="block(u.id)"
                  >
                    Заблокировать
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn btn--small btn--primary"
                    :disabled="busyId === u.id"
                    @click="unblock(u.id)"
                  >
                    Разблокировать
                  </button>
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

const items = ref([])
const error = ref(null)
const busyId = ref(null)

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
  max-width: 1100px;
  margin: 0 auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table th,
.table td {
  padding: 10px 8px;
  border-bottom: 1px solid var(--border);
  text-align: left;
}

.btn--small {
  padding: 6px 10px;
  font-size: 13px;
}

.danger {
  color: var(--danger);
}
</style>
