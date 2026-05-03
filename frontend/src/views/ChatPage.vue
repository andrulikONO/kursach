<template>
  <div class="chat-page">
    <div class="card">
      <div class="card__body chat-layout">
        <aside class="chat-list">
          <!-- Заголовок списка диалогов -->
          <div class="chat-list-header">
            <h3 class="chat-list-title">💬 Сообщения</h3>
            <span class="dialogs-count" v-if="dialogs.length > 0">{{ dialogs.length }}</span>
          </div>

          <!-- Список диалогов -->
          <div v-if="dialogs.length > 0">
            <div 
              v-for="d in dialogs" 
              :key="d.peer_id" 
              class="dialog-item" 
              :class="{ active: peerId === Number(d.peer_id) }" 
              @click="openDialog(d.peer_id)"
            >
              <div class="dialog-avatar">
                {{ getAvatarLetter(d.peer_fio || d.peer_login) }}
              </div>
              <div class="dialog-content">
                <div class="dialog-name">{{ d.peer_fio || d.peer_login }}</div>
                <div class="dialog-text muted">{{ d.last_message || 'Нет сообщений' }}</div>
              </div>
              <div v-if="d.unread_count" class="unread-badge">{{ d.unread_count }}</div>
            </div>
          </div>

          <!-- Заглушка для пустого списка -->
          <div v-else class="empty-dialogs">
            <div class="empty-icon">💬</div>
            <div class="empty-title">Нет диалогов</div>
            <div class="empty-description">
              У вас пока нет переписки с другими пользователями.
            </div>
            <RouterLink to="/" class="btn btn--primary btn--small">
              Найти объявления
            </RouterLink>
            <div class="empty-hint">
              Напишите автору объявления, чтобы начать диалог
            </div>
          </div>
        </aside>

        <section class="chat-thread">
          <!-- Заглушка когда диалог не выбран -->
          <div v-if="!peerId" class="empty-state">
            <div class="empty-icon">💬</div>
            <div class="empty-title">Выберите диалог</div>
            <div class="empty-description">
              Выберите чат из списка слева, чтобы начать общение
            </div>
          </div>

          <!-- Заглушка для пустого диалога (нет сообщений) -->
          <template v-else-if="messages.length === 0">
            <div class="empty-chat">
              <div class="empty-icon">💭</div>
              <div class="empty-title">Начните общение</div>
              <div class="empty-description">
                Напишите первое сообщение, чтобы начать диалог с {{ getPeerName() }}
              </div>
            </div>
            <form class="composer" @submit.prevent="send">
              <input v-model="text" class="input" placeholder="Напишите сообщение..." />
              <button class="btn btn--primary" :disabled="!text.trim()">Отправить</button>
            </form>
          </template>

          <!-- Обычный чат с сообщениями -->
          <template v-else>
            <div class="messages" ref="messagesContainer">
              <div 
                v-for="m in messages" 
                :key="m.id" 
                class="msg" 
                :class="{ me: Number(m.sender_id) === myId }"
              >
                <div class="msg-body">{{ m.body }}</div>
                <small class="msg-time muted">{{ formatTime(m.created_at) }}</small>
              </div>
            </div>
            <form class="composer" @submit.prevent="send">
              <input v-model="text" class="input" placeholder="Сообщение..." />
              <button class="btn btn--primary" :disabled="!text.trim()">Отправить</button>
            </form>
          </template>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchChatDialogs, fetchChatMessages, sendChatMessage } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()
const myId = computed(() => Number(user.value?.userId || 0))
const peerId = computed(() => Number(route.query.peerId || 0))
const dialogs = ref([])
const messages = ref([])
const text = ref('')
const messagesContainer = ref(null)
let timer = null

// Функция для получения буквы аватара
function getAvatarLetter(name) {
  if (!name) return '👤'
  const firstLetter = name.charAt(0).toUpperCase()
  return firstLetter
}

// Функция для получения имени собеседника
function getPeerName() {
  const dialog = dialogs.value.find(d => Number(d.peer_id) === peerId.value)
  if (dialog) {
    return dialog.peer_fio || dialog.peer_login || 'пользователем'
  }
  return 'пользователем'
}

// Форматирование времени
function formatTime(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) {
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
  } else if (days === 1) {
    return 'Вчера'
  } else if (days < 7) {
    return date.toLocaleDateString('ru-RU', { weekday: 'short' })
  } else {
    return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' })
  }
}

// Прокрутка вниз чата
async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

async function loadDialogs() {
  try {
    const d = await fetchChatDialogs()
    dialogs.value = d.items || []
  } catch (error) {
    console.error('Failed to load dialogs:', error)
    dialogs.value = []
  }
}

async function loadMessages() {
  if (!peerId.value) return
  try {
    const d = await fetchChatMessages(peerId.value)
    messages.value = d.items || []
    await scrollToBottom()
  } catch (error) {
    console.error('Failed to load messages:', error)
    messages.value = []
  }
}

function openDialog(id) {
  router.replace({ path: '/chat', query: { peerId: String(id) } })
}

async function send() {
  if (!peerId.value || !text.value.trim()) return
  const body = text.value.trim()
  text.value = ''
  try {
    await sendChatMessage(peerId.value, body)
    await loadMessages()
    await loadDialogs()
    await scrollToBottom()
  } catch (error) {
    console.error('Failed to send message:', error)
    text.value = body // восстанавливаем текст при ошибке
  }
}

async function poll() {
  if (peerId.value && messages.value.length) {
    const afterId = Number(messages.value[messages.value.length - 1].id || 0)
    try {
      const d = await fetchChatMessages(peerId.value, afterId)
      if ((d.items || []).length) {
        messages.value = [...messages.value, ...d.items]
        await loadDialogs()
        await scrollToBottom()
      }
    } catch (error) {
      console.error('Polling error:', error)
    }
  } else if (peerId.value) {
    await loadMessages()
  }
  timer = setTimeout(poll, 1200)
}

onMounted(async () => {
  await loadDialogs()
  await loadMessages()
  timer = setTimeout(poll, 1200)
})

onBeforeUnmount(() => {
  if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.chat-layout { 
  display: grid; 
  grid-template-columns: 320px 1fr; 
  gap: 16px; 
  min-height: 520px; 
}

/* Левая панель с диалогами */
.chat-list { 
  border-right: 1px solid var(--border); 
  padding-right: 12px; 
  overflow-y: auto; 
  max-height: 520px;
}

.chat-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 10px 12px 10px;
  border-bottom: 2px solid var(--border);
  margin-bottom: 12px;
}

.chat-list-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.dialogs-count {
  background: var(--primary);
  color: white;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.dialog-item { 
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px; 
  border-radius: 12px; 
  cursor: pointer; 
  transition: all 0.2s ease;
  margin-bottom: 4px;
}

.dialog-item:hover { 
  background: rgba(124, 92, 255, 0.08); 
}

.dialog-item.active { 
  background: rgba(124, 92, 255, 0.15); 
}

.dialog-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, var(--primary), var(--primary-2));
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 18px;
  color: white;
  flex-shrink: 0;
}

.dialog-content {
  flex: 1;
  min-width: 0;
}

.dialog-name { 
  font-weight: 600; 
  margin-bottom: 4px;
}

.dialog-text { 
  white-space: nowrap; 
  overflow: hidden; 
  text-overflow: ellipsis; 
  font-size: 13px;
}

.unread-badge {
  background: var(--primary);
  color: white;
  padding: 2px 6px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  min-width: 20px;
  text-align: center;
}

/* Заглушка для пустых диалогов */
.empty-dialogs {
  text-align: center;
  padding: 40px 20px;
}

.empty-chat,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 60px 20px;
  min-height: 400px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 8px;
}

.empty-description {
  color: var(--muted);
  margin-bottom: 24px;
  max-width: 300px;
}

.empty-hint {
  margin-top: 20px;
  font-size: 12px;
  color: var(--muted);
  opacity: 0.7;
}

.btn--small {
  padding: 8px 16px;
  font-size: 14px;
}

/* Правая панель с сообщениями */
.chat-thread {
  display: flex;
  flex-direction: column;
  min-height: 520px;
}

.messages { 
  flex: 1;
  display: flex; 
  flex-direction: column;
  gap: 12px; 
  max-height: 430px; 
  overflow-y: auto; 
  padding: 4px;
}

.msg { 
  max-width: 70%; 
  padding: 10px 14px; 
  border-radius: 12px; 
  background: var(--card-2);
  align-self: flex-start;
}

.msg.me { 
  align-self: flex-end;
  background: rgba(124, 92, 255, 0.2); 
}

.msg-body {
  word-wrap: break-word;
  margin-bottom: 4px;
}

.msg-time {
  font-size: 10px;
  opacity: 0.7;
}

.composer { 
  margin-top: 16px; 
  display: grid; 
  grid-template-columns: 1fr auto; 
  gap: 10px; 
}

.composer .input {
  padding: 10px 14px;
}

/* Адаптив */
@media (max-width: 900px) { 
  .chat-layout { 
    grid-template-columns: 1fr; 
  } 
  .chat-list { 
    border-right: none; 
    border-bottom: 1px solid var(--border); 
    padding-right: 0; 
    padding-bottom: 12px; 
    max-height: 300px;
  }
  
  .msg {
    max-width: 85%;
  }
}

/* Стилизация скролла */
.messages::-webkit-scrollbar,
.chat-list::-webkit-scrollbar {
  width: 6px;
}

.messages::-webkit-scrollbar-track,
.chat-list::-webkit-scrollbar-track {
  background: var(--border);
  border-radius: 3px;
}

.messages::-webkit-scrollbar-thumb,
.chat-list::-webkit-scrollbar-thumb {
  background: var(--primary);
  border-radius: 3px;
}
</style>