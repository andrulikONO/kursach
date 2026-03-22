<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 12px 0">Служба поддержки</h1>
        <p class="muted" style="margin-bottom: 32px">
          Сообщите о нарушении или задайте вопрос — мы ответим в течение 24 часов
        </p>

        <div class="ticket-form" style="max-width: 600px">
          <form @submit.prevent="submitTicket" style="display: grid; gap: 16px">
            <label class="form-group">
              <span class="muted">Тема обращения</span>
              <CustomSelect 
                v-model="ticket.subject" 
                :options="subjectOptions"
                placeholder="Выберите тему"
                required
              />
            </label>

            <label class="form-group">
              <span class="muted">Ссылка на объявление (если есть)</span>
              <input 
                v-model.trim="ticket.url" 
                class="input" 
                type="url" 
                placeholder="https://.../product/123"
              />
            </label>

            <label class="form-group">
              <span class="muted">Ваше сообщение</span>
              <textarea 
                v-model.trim="ticket.message" 
                class="textarea" 
                placeholder="Опишите проблему подробно..."
                rows="5"
                required
              />
            </label>

            <label class="form-group">
              <span class="muted">Ваш email для связи</span>
              <input 
                v-model.trim="ticket.email" 
                class="input" 
                type="email" 
                placeholder="your@email.com"
                required
              />
            </label>

            <button 
              class="btn btn--primary" 
              type="submit" 
              :disabled="submitting"
              style="width: 100%"
            >
              {{ submitting ? 'Отправка...' : 'Отправить обращение' }}
            </button>
          </form>

          <div v-if="ticketError" class="danger" style="margin-top: 12px; text-align: center">
            {{ ticketError }}
          </div>

          <div v-if="ticketSuccess" class="success-box card" style="margin-top: 16px; box-shadow: none">
            <div class="card__body" style="text-align: center">
              <div style="font-size: 32px; margin-bottom: 8px">✅</div>
              <strong>Обращение отправлено!</strong>
              <p class="muted" style="margin: 8px 0 0 0">
                Номер тикета: <b>#{{ ticketId }}</b><br>
                Ответ придёт на email в течение 24 часов
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import CustomSelect from '../components/CustomSelect.vue'

const submitting = ref(false)
const ticketError = ref(null)
const ticketSuccess = ref(false)
const ticketId = ref(null)

const ticket = ref({
  subject: '',
  url: '',
  message: '',
  email: ''
})

const subjectOptions = [
  { value: 'fraud', label: 'Мошенничество' },
  { value: 'spam', label: 'Спам' },
  { value: 'fake', label: 'Поддельное объявление' },
  { value: 'prohibited', label: 'Запрещённый товар' },
  { value: 'other', label: 'Другое' },
]

async function submitTicket() {
  submitting.value = true
  ticketError.value = null
  ticketSuccess.value = false

  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    ticketId.value = Math.floor(Math.random() * 10000) + 1000
    ticketSuccess.value = true
    ticket.value = { subject: '', url: '', message: '', email: '' }
  } catch (e) {
    ticketError.value = e?.message || 'Ошибка отправки'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.info-page {
  display: grid;
  gap: 16px;
  max-width: 700px;
}

.form-group {
  display: grid;
  gap: 6px;
}

.success-box {
  background: rgba(0, 170, 102, 0.1);
  border: 1px solid rgba(0, 170, 102, 0.3);
}

.danger {
  color: var(--danger);
}
</style>