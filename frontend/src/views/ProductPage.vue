<template>
  <div style="display: grid; gap: 14px">
    <div class="split">
      <div>
        <div class="title" style="font-size: 22px">Карточка объявления</div>
        <div class="muted">Страница объявления с продавцом, номером и комментариями</div>
      </div>
      <RouterLink class="btn" to="/">Назад в каталог</RouterLink>
    </div>

    <div v-if="store.selectedLoading" class="card">
      <div class="card__body">Загрузка...</div>
    </div>

    <div v-else-if="store.selectedError" class="card">
      <div class="card__body">
        <div class="danger">{{ store.selectedError }}</div>
      </div>
    </div>

    <div v-else-if="store.selected" class="layout">
      <section class="card">
        <div class="thumb" style="height: 240px">{{ (store.selected.type || 'Товар').slice(0, 1) }}</div>
        <div class="card__body" style="display: grid; gap: 14px">
          <div class="title" style="font-size: 22px">{{ store.selected.title }}</div>
          <div class="price" style="font-size: 20px">{{ formatPrice(store.selected.price) }}</div>

          <div class="tags">
            <span class="tag tag--kind">{{ getListingKindLabel(store.selected.listing_kind) }}</span>
            <span class="tag">{{ store.selected.type || '—' }}</span>
            <span class="tag">Город: {{ store.selected.city || '—' }}</span>
          </div>

          <div class="muted" style="white-space: pre-wrap">{{ store.selected.description || 'Описание отсутствует' }}</div>

          <button
            v-if="isAdmin"
            type="button"
            class="btn danger-outline"
            :disabled="deleting"
            @click="removeListing"
          >
            {{ deleting ? 'Удаление...' : 'Удалить объявление (админ)' }}
          </button>
        </div>
      </section>

      <aside class="side">
        <section class="card">
          <div class="card__body" style="display: grid; gap: 10px">
            <div class="title">Продавец</div>
            <div><b>{{ sellerName }}</b></div>
            <div class="muted">@{{ store.selected.seller_login || 'unknown' }}</div>
            <div class="muted" style="font-size: 13px">ID объявления: {{ store.selected.id }}</div>
          </div>
        </section>

        <section class="card">
          <div class="card__body" style="display: grid; gap: 10px">
            <div class="title">Контакты</div>
            <div v-if="revealedPhone" class="muted">
              Телефон: <b>{{ revealedPhone }}</b>
            </div>
            <div v-else class="muted">Номер скрыт до нажатия кнопки</div>
            <button class="btn btn--primary" type="button" :disabled="revealingPhone" @click="revealPhone">
              {{ revealingPhone ? 'Показываем...' : 'Показать номер' }}
            </button>
          </div>
        </section>
      </aside>

      <section class="card comments">
        <div class="card__body" style="display: grid; gap: 14px">
          <div class="split">
            <div class="title">Комментарии</div>
            <div class="muted">{{ comments.length }}</div>
          </div>

          <form v-if="isAuth" style="display: grid; gap: 10px" @submit.prevent="submitComment">
            <textarea v-model.trim="commentBody" class="textarea" placeholder="Напишите вопрос продавцу или уточнение по объявлению" />
            <div class="split">
              <button class="btn btn--primary" type="submit" :disabled="sendingComment || !commentBody">
                {{ sendingComment ? 'Отправка...' : 'Отправить комментарий' }}
              </button>
            </div>
          </form>
          <div v-else class="muted">Войдите, чтобы оставлять комментарии.</div>

          <div v-if="commentsError" class="danger">{{ commentsError }}</div>

          <div v-if="comments.length === 0" class="muted">Комментариев пока нет.</div>
          <div v-else class="comments-list">
            <div v-for="comment in comments" :key="comment.id" class="comment-item">
              <div class="split">
                <b>{{ formatCommentAuthor(comment) }}</b>
                <span class="muted" style="font-size: 12px">{{ formatDateTime(comment.created_at) }}</span>
              </div>
              <div class="muted" style="white-space: pre-wrap">{{ comment.body }}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useProductStore } from '../stores/productStore'
import { createProductComment, deleteProduct, fetchProductComments, fetchProductPhone } from '../lib/api'
import { useAuth } from '../composables/useAuth'
import { getListingKindLabel } from '../lib/catalog'

const props = defineProps({ id: { type: String, required: true } })
const store = useProductStore()
const router = useRouter()
const { isAuth, refreshProfile, user } = useAuth()
const deleting = ref(false)
const revealingPhone = ref(false)
const revealedPhone = ref('')
const comments = ref([])
const commentsError = ref('')
const commentBody = ref('')
const sendingComment = ref(false)

const isAdmin = computed(() => (user.value?.roles || []).includes('admin'))
const sellerName = computed(() => {
  const first = store.selected?.seller_first_name || ''
  const last = store.selected?.seller_last_name || ''
  const fullName = `${first} ${last}`.trim()
  return fullName || store.selected?.seller_login || 'Продавец'
})

onMounted(() => {
  refreshProfile()
})

function formatPrice(v) {
  return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(Number(v || 0))
}

function formatDateTime(v) {
  if (!v) return '—'
  return new Date(v).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCommentAuthor(comment) {
  const fullName = `${comment.first_name || ''} ${comment.last_name || ''}`.trim()
  return fullName || comment.login || 'Пользователь'
}

async function load() {
  revealedPhone.value = ''
  await Promise.all([store.loadOne(props.id), loadComments()])
}

async function loadComments() {
  commentsError.value = ''
  try {
    const data = await fetchProductComments(props.id)
    comments.value = data.items || []
  } catch (e) {
    commentsError.value = e?.message || String(e)
  }
}

async function revealPhone() {
  revealingPhone.value = true
  try {
    const data = await fetchProductPhone(props.id)
    revealedPhone.value = data.phone || ''
  } catch (e) {
    commentsError.value = e?.message || String(e)
  } finally {
    revealingPhone.value = false
  }
}

async function submitComment() {
  sendingComment.value = true
  commentsError.value = ''
  try {
    await createProductComment(props.id, { body: commentBody.value })
    commentBody.value = ''
    await loadComments()
  } catch (e) {
    commentsError.value = e?.message || String(e)
  } finally {
    sendingComment.value = false
  }
}

async function removeListing() {
  if (!confirm('Удалить объявление?')) return
  deleting.value = true
  try {
    await deleteProduct(props.id)
    router.push('/')
  } catch (e) {
    alert(e?.message || String(e))
  } finally {
    deleting.value = false
  }
}

onMounted(load)
watch(() => props.id, load)
</script>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 16px;
}

.side {
  display: grid;
  gap: 16px;
  align-content: start;
}

.comments {
  grid-column: 1 / -1;
}

.comments-list {
  display: grid;
  gap: 10px;
}

.comment-item {
  display: grid;
  gap: 8px;
  padding: 12px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
}

.danger-outline {
  border-color: var(--danger);
  color: var(--danger);
}

.tag--kind {
  background: rgba(255, 152, 0, 0.2);
  color: #ffb74d;
}

@media (max-width: 960px) {
  .layout {
    grid-template-columns: 1fr;
  }
}
</style>
