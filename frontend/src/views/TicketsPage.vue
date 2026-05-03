<template>
  <div class="tickets-page">
    <div class="page-header">
      <h1 class="title">🎫 Панель тикетов</h1>
      <p class="muted">Управление обращениями пользователей</p>
    </div>

    <div class="tickets-layout">
      <aside class="tickets-list card">
        <div class="list-header">
          <h3>Обращения</h3>
          <div class="tickets-status-select">
            <CustomSelect v-model="filterStatus" :options="STATUS_FILTER_OPTIONS" placeholder="Все статусы" />
          </div>
        </div>

        <div v-if="loading" class="loading">Загрузка...</div>
        <div v-else-if="error" class="error danger">{{ error }}</div>
        
        <div v-else class="tickets-items">
          <div 
            v-for="ticket in filteredTickets" 
            :key="ticket.id"
            class="ticket-item"
            :class="{ active: selectedTicket?.id === ticket.id }"
            @click="selectTicket(ticket)"
          >
            <div class="ticket-meta">
              <span class="ticket-id">#{{ ticket.id }}</span>
              <span class="ticket-status" :class="`status--${ticket.status}`">
                {{ statusLabel(ticket.status) }}
              </span>
            </div>
            <div class="ticket-subject">{{ ticket.subject }}</div>
            <div class="ticket-user">
              <span class="muted">{{ ticket.user_login || 'Пользователь' }}</span>
              <span class="muted">· {{ formatDate(ticket.created_at) }}</span>
            </div>
          </div>
        </div>
      </aside>

      <main class="ticket-detail card" v-if="selectedTicket">
        <div class="detail-header">
          <div>
            <h2 class="title">{{ selectedTicket.subject }}</h2>
            <div class="ticket-info">
              <span class="tag">#{{ selectedTicket.id }}</span>
              <span class="tag" :class="`status--${selectedTicket.status}`">
                {{ statusLabel(selectedTicket.status) }}
              </span>
              <span class="muted">От: {{ selectedTicket.user_login }}</span>
              <span class="muted">{{ formatDate(selectedTicket.created_at) }}</span>
            </div>
          </div>
          <button class="btn btn--secondary" @click="selectedTicket = null">← Назад</button>
        </div>

        <div class="message user-message">
          <div class="message-header">
            <strong>{{ selectedTicket.user_login }}</strong>
            <span class="muted">{{ formatDate(selectedTicket.created_at) }}</span>
          </div>
          <div class="message-body">{{ selectedTicket.message }}</div>
        </div>

        <div v-for="resp in responses" :key="resp.id" 
             class="message" 
             :class="resp.user_id === selectedTicket.user_id ? 'user-message' : 'support-message'">
          <div class="message-header">
            <strong>{{ resp.author_login || 'Поддержка' }}</strong>
            <span class="muted">{{ formatDate(resp.created_at) }}</span>
            <span v-if="resp.is_internal" class="tag tag--internal">Внутреннее</span>
          </div>
          <div class="message-body">{{ resp.message }}</div>
        </div>

        <div class="response-form">
          <label class="form-group">
            <span class="muted">Ваш ответ</span>
            <textarea 
              v-model="responseText" 
              class="textarea" 
              placeholder="Напишите ответ..."
              rows="4"
            />
          </label>
          <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px">
            <input type="checkbox" v-model="responseInternal" />
            <span class="muted">Внутренняя заметка (не видна пользователю)</span>
          </label>
          <div class="split" style="margin-top: 12px">
            <button 
              class="btn btn--primary" 
              @click="sendResponse"
              :disabled="!responseText.trim() || sending"
            >
              {{ sending ? 'Отправка...' : 'Отправить ответ' }}
            </button>
          </div>
        </div>
      </main>

      <main class="ticket-detail card" v-else>
        <div style="text-align: center; padding: 60px 20px">
          <div style="font-size: 48px; margin-bottom: 16px">🎫</div>
          <p class="muted">Выберите тикет из списка слева для просмотра</p>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CustomSelect from '../components/CustomSelect.vue'
import {
  fetchTickets,
  createTicket,
  fetchTicketById,
  respondToTicket
} from '../lib/api'

const STATUS_FILTER_OPTIONS = [
  { value: '', label: 'Все статусы' },
  { value: 'open', label: '🔴 Открытые' },
  { value: 'pending', label: '🟡 В работе' },
  { value: 'resolved', label: '🟢 Решённые' },
  { value: 'closed', label: '⚫ Закрытые' }
]

const loading = ref(false)
const error = ref(null)
const tickets = ref([])
const selectedTicket = ref(null)
const responses = ref([])
const filterStatus = ref('')

const responseText = ref('')
const responseInternal = ref(false)
const sending = ref(false)

const filteredTickets = computed(() => {
  if (!filterStatus.value) return tickets.value
  return tickets.value.filter(t => t.status === filterStatus.value)
})

async function loadTickets() {
  loading.value = true
  error.value = null
  try {
    const data = await fetchTickets() // ✅ вместо request('/api/tickets')
    tickets.value = data.tickets || []
  } catch (e) {
    error.value = e?.message || 'Ошибка загрузки тикетов'
  } finally {
    loading.value = false
  }
}

async function selectTicket(ticket) {
  selectedTicket.value = ticket
  responses.value = []
  try {
    const data = await fetchTicketById(ticket.id) // ✅ вместо request(...)
    responses.value = data.responses || []
  } catch (e) {
    error.value = e?.message || 'Ошибка загрузки тикета'
  }
}

async function sendResponse() {
  if (!responseText.value.trim() || !selectedTicket.value) return
  
  sending.value = true
  try {
    await respondToTicket(selectedTicket.value.id, { // ✅ вместо request(...)
      message: responseText.value,
      isInternal: responseInternal.value
    })
    
    responseText.value = ''
    responseInternal.value = false
    await selectTicket(selectedTicket.value)
    await loadTickets()
  } catch (e) {
    error.value = e?.message || 'Ошибка отправки'
  } finally {
    sending.value = false
  }
}

function statusLabel(status) {
  const labels = {
    open: 'Открыт',
    pending: 'В работе',
    resolved: 'Решён',
    closed: 'Закрыт'
  }
  return labels[status] || status
}

function formatDate(v) {
  if (!v) return ''
  return new Date(v).toLocaleString('ru-RU', {
    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
  })
}

onMounted(loadTickets)
</script>

<style scoped>
.tickets-page {
  display: grid;
  gap: 20px;
}

.page-header {
  display: grid;
  gap: 8px;
}

.tickets-layout {
  display: grid;
  grid-template-columns: 380px 1fr;
  gap: 20px;
  align-items: start;
}

.tickets-list {
  max-height: 70vh;
  overflow-y: auto;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
}

.list-header h3 {
  margin: 0;
  font-size: 16px;
}

.tickets-status-select {
  flex: 0 1 auto;
  min-width: 200px;
  max-width: 55%;
}

.tickets-items {
  display: grid;
  gap: 8px;
  padding: 12px;
}

.ticket-item {
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--card-2);
  cursor: pointer;
  transition: all 0.15s ease;
}

.ticket-item:hover {
  border-color: var(--primary);
}

.ticket-item.active {
  border-color: var(--primary);
  background: rgba(124, 92, 255, 0.15);
}

.ticket-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.ticket-id {
  font-weight: 600;
  font-size: 13px;
}

.ticket-status {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 999px;
}

.status--open { background: rgba(255, 77, 109, 0.2); color: var(--danger); }
.status--pending { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
.status--resolved { background: rgba(0, 170, 102, 0.2); color: #00aa66; }
.status--closed { background: rgba(150, 150, 150, 0.2); color: var(--muted); }

.ticket-subject {
  font-weight: 600;
  margin-bottom: 4px;
}

.ticket-user {
  font-size: 12px;
  display: flex;
  gap: 8px;
}

.ticket-detail {
  display: grid;
  gap: 16px;
  max-height: 70vh;
  overflow-y: auto;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.ticket-info {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 8px;
  font-size: 13px;
}

.message {
  padding: 16px;
  border-radius: 12px;
  border: 1px solid var(--border);
}

.user-message {
  background: rgba(255, 255, 255, 0.03);
}

.support-message {
  background: rgba(124, 92, 255, 0.1);
  border-color: rgba(124, 92, 255, 0.3);
}

.message-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
  font-size: 14px;
}

.message-body {
  line-height: 1.5;
  white-space: pre-wrap;
}

.response-form {
  padding-top: 16px;
  border-top: 1px solid var(--border);
}

.form-group {
  display: grid;
  gap: 6px;
}

.tag--internal {
  background: rgba(124, 92, 255, 0.3);
  color: var(--primary-2);
}

.loading, .error {
  text-align: center;
  padding: 40px;
}

@media (max-width: 980px) {
  .tickets-layout {
    grid-template-columns: 1fr;
  }
}
</style>