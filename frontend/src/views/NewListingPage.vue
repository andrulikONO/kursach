<template>
  <div style="display: grid; gap: 14px; max-width: 820px; margin: 0 auto;">
    <div class="split">
      <div>
        <div class="title" style="font-size: 22px">Подача объявления</div>
        <div class="muted">Телефон для связи подтягивается из профиля и будет скрыт до нажатия кнопки в карточке</div>
      </div>
      <RouterLink class="btn" to="/">Каталог</RouterLink>
    </div>

    <div v-if="!isAuth" class="card">
      <div class="card__body" style="text-align: center;">
        <p class="muted">Войдите, чтобы разместить объявление.</p>
        <RouterLink class="btn btn--primary" to="/login">Войти</RouterLink>
      </div>
    </div>

    <div v-else-if="user?.isBlocked" class="card">
      <div class="card__body danger" style="text-align: center;">
        Аккаунт заблокирован. Создание новых объявлений недоступно.
      </div>
    </div>

    <!-- Успешная подача объявления -->
    <div v-else-if="success" class="success-card card">
      <div class="card__body success-content">
        <div class="success-icon">🎉</div>
        <h2 class="success-title">Объявление успешно опубликовано!</h2>
        <p class="success-description">
          Ваше объявление #{{ success.id }} появится в каталоге и станет доступно для просмотра другим пользователям.
        </p>
        
        <div class="success-actions">
          <RouterLink :to="`/product/${success.id}`" class="btn btn--primary">
            Посмотреть объявление
          </RouterLink>
          <button @click="createAnother" class="btn btn--primary">
            Создать еще
          </button>
          <RouterLink to="/" class="btn btn--primary">
            На главную
          </RouterLink>
        </div>

        <div class="success-tips">
          <h4>💡 Советы для успешной продажи:</h4>
          <ul>
            <li>Добавьте качественные фотографии товара</li>
            <li>Будьте на связи - отвечайте на сообщения быстро</li>
            <li>Укажите все важные детали в описании</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Форма подачи -->
    <form v-else class="card" @submit.prevent="submit">
      <div class="card__body" style="display: grid; gap: 12px">
        <div class="row">
          <label style="display: grid; gap: 6px">
            <span class="muted">Заголовок</span>
            <input 
              v-model.trim="form.title" 
              class="input" 
              required 
              placeholder="Например: Куплю iPhone 13 или продам диван"
              :disabled="submitting"
            />
          </label>

          <label style="display: grid; gap: 6px">
            <span class="muted">Цена (₽)</span>
            <input 
              v-model="form.price" 
              class="input" 
              inputmode="numeric" 
              required 
              placeholder="15000"
              :disabled="submitting"
            />
          </label>
        </div>

        <div class="row">
          <CategorySelect
            v-model="form.categorySlug"
            :include-all="false"
            empty-label="Выберите категорию"
            :disabled="submitting"
          />
          <ListingKindSelect
            v-model="form.listingKind"
            :include-all="false"
            empty-label="Выберите тип"
            :disabled="submitting"
          />
        </div>

        <div class="row">
          <CitySelect 
            v-model="form.city" 
            empty-label="Все города"
            :disabled="submitting"
          />

          <label style="display: grid; gap: 6px">
            <span class="muted">Телефон в объявлении</span>
            <input 
              class="input" 
              type="text" 
              readonly 
              :value="user?.phone || '—'"
              :class="{ 'no-phone': !user?.phone }"
            />
            <span v-if="!user?.phone" class="warning-text">
              ⚠️ Укажите телефон в профиле для публикации объявлений
            </span>
          </label>
        </div>

        <label style="display: grid; gap: 6px">
          <span class="muted">Описание</span>
          <textarea 
            v-model.trim="form.description" 
            class="textarea" 
            placeholder="Состояние, детали, условия сделки, что именно ищете или продаете..."
            :disabled="submitting"
            rows="4"
          />
          <div class="char-counter" :class="{ 'char-limit': form.description.length > 1000 }">
            {{ form.description.length }}/1000
          </div>
        </label>

        <div class="split" style="justify-content: center;">
          <button 
            class="btn btn--primary" 
            type="submit" 
            :disabled="submitting || !user?.phone || !isFormValid"
          >
            <span v-if="submitting" class="loading-spinner"></span>
            {{ submitting ? 'Публикация...' : 'Опубликовать' }}
          </button>
        </div>

        <div v-if="error" class="danger" style="text-align: center;">{{ error }}</div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import CategorySelect from '../components/CategorySelect.vue'
import CitySelect from '../components/CitySelect.vue'
import ListingKindSelect from '../components/ListingKindSelect.vue'
import { createProduct } from '../lib/api'
import { useAuth } from '../composables/useAuth'

const { isAuth, user, refreshProfile } = useAuth()

const form = ref({
  title: '',
  price: '',
  categorySlug: '',
  listingKind: 'sale',
  city: '',
  description: ''
})

const submitting = ref(false)
const error = ref(null)
const success = ref(null)

// Валидация формы
const isFormValid = computed(() => {
  return form.value.title.trim() &&
         form.value.price &&
         Number(form.value.price) > 0 &&
         form.value.categorySlug &&
         user.value?.phone
})

onMounted(async () => {
  if (isAuth.value) {
    await refreshProfile()
  }
})

function createAnother() {
  success.value = null
  form.value = {
    title: '',
    price: '',
    categorySlug: '',
    listingKind: 'sale',
    city: '',
    description: ''
  }
  error.value = null
}

async function submit() {
  // Дополнительная валидация
  if (!user.value?.phone) {
    error.value = 'Укажите номер телефона в профиле перед публикацией объявления'
    return
  }
  
  if (!form.value.categorySlug) {
    error.value = 'Пожалуйста, выберите категорию'
    return
  }
  
  if (Number(form.value.price) <= 0) {
    error.value = 'Укажите корректную цену'
    return
  }
  
  submitting.value = true
  error.value = null
  
  try {
    const payload = {
      title: form.value.title.trim(),
      price: Number(form.value.price),
      categorySlug: form.value.categorySlug,
      listingKind: form.value.listingKind || 'sale',
      city: form.value.city || null,
      description: form.value.description ? form.value.description.trim() : null
    }
    const created = await createProduct(payload)
    success.value = created
    
    // Прокручиваем к успешному сообщению
    setTimeout(() => {
      document.querySelector('.success-card')?.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
      })
    }, 100)
    
  } catch (e) {
    error.value = e?.message || 'Ошибка при создании объявления. Попробуйте еще раз.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* Анимации и стили для успешного состояния */
.success-card {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
  border: 2px solid #22c55e;
  animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.success-content {
  text-align: center;
  padding: 32px;
}

.success-icon {
  font-size: 64px;
  margin-bottom: 16px;
  animation: bounce 0.6s ease-out;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.success-title {
  color: #22c55e;
  margin-bottom: 12px;
  font-size: 24px;
  font-weight: 600;
}

.success-description {
  color: var(--muted);
  margin-bottom: 24px;
  font-size: 16px;
}

.success-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.success-tips {
  background: rgba(34, 197, 94, 0.1);
  border-radius: 12px;
  padding: 20px;
  text-align: left;
  margin-top: 20px;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.success-tips h4 {
  color: #22c55e;
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
}

.success-tips ul {
  margin: 0;
  padding-left: 20px;
  color: var(--text-primary);
}

.success-tips li {
  margin: 8px 0;
  line-height: 1.4;
}

/* Индикатор загрузки на кнопке */
.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Предупреждение о телефоне */
.no-phone {
  border-color: #ef4444 !important;
  background-color: rgba(239, 68, 68, 0.1);
}

.warning-text {
  font-size: 12px;
  color: #ef4444;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Счетчик символов */
.char-counter {
  font-size: 12px;
  color: var(--muted);
  text-align: right;
  margin-top: 4px;
}

.char-limit {
  color: #ef4444;
}

/* Улучшенные состояния инпутов */
.input:disabled,
.textarea:disabled {
  background-color: var(--bg-secondary);
  cursor: not-allowed;
  opacity: 0.7;
}

/* Единый стиль для всех кнопок в success-actions */
.success-actions .btn {
  background: var(--primary);
  color: white;
  border: none;
}

.success-actions .btn:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
}

.success-actions .btn:active {
  transform: translateY(0);
}

/* Центрирование кнопки публикации */
.split {
  justify-content: center;
}

/* Адаптивность */
@media (max-width: 640px) {
  .success-icon {
    font-size: 48px;
  }
  
  .success-title {
    font-size: 20px;
  }
  
  .success-description {
    font-size: 14px;
  }
  
  .success-actions {
    flex-direction: column;
  }
  
  .success-actions .btn {
    width: 100%;
  }
  
  .success-content {
    padding: 20px;
  }
  
  .success-tips {
    padding: 16px;
  }
  
  .success-tips li {
    font-size: 13px;
  }
}
</style>