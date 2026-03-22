<template>
  <form
    class="reg-form"
    novalidate
    @submit.prevent="handleSubmit"
    @input="onInput"
    @blur.capture="onBlur"
  >
    <div class="row">
      <label class="form-group">
        <span class="muted">Имя (2–15 символов)</span>
        <input
          id="firstName"
          v-model="form.firstName"
          name="firstName"
          type="text"
          class="input"
          :class="fieldClass('firstName')"
          autocomplete="off"
          maxlength="15"
        />
        <span v-if="fieldErrors.firstName" class="field-msg danger">{{ fieldErrors.firstName }}</span>
      </label>
      <label class="form-group">
        <span class="muted">Фамилия (2–15 символов)</span>
        <input
          id="lastName"
          v-model="form.lastName"
          name="lastName"
          type="text"
          class="input"
          :class="fieldClass('lastName')"
          autocomplete="off"
          maxlength="15"
        />
        <span v-if="fieldErrors.lastName" class="field-msg danger">{{ fieldErrors.lastName }}</span>
      </label>
    </div>

    <label class="form-group">
      <span class="muted">Email</span>
      <input
        id="email"
        v-model="form.email"
        name="email"
        type="email"
        class="input"
        :class="fieldClass('email')"
        autocomplete="off"
      />
      <span v-if="fieldErrors.email" class="field-msg danger">{{ fieldErrors.email }}</span>
    </label>

    <label class="form-group">
      <span class="muted">Логин (не менее 6 символов)</span>
      <input
        id="login"
        v-model="form.login"
        name="login"
        type="text"
        class="input"
        :class="fieldClass('login')"
        autocomplete="off"
        minlength="6"
      />
      <span v-if="fieldErrors.login" class="field-msg danger">{{ fieldErrors.login }}</span>
    </label>

    <div class="row">
      <label class="form-group">
        <span class="muted">Пароль</span>
        <div class="input-row">
          <input
            id="password"
            v-model="form.password"
            name="password"
            :type="showPwd ? 'text' : 'password'"
            class="input"
            :class="fieldClass('password')"
            autocomplete="new-password"
          />
          <button type="button" class="btn btn--ghost" tabindex="-1" @click="showPwd = !showPwd">
            {{ showPwd ? 'Скрыть' : 'Показать' }}
          </button>
        </div>
        <span v-if="fieldErrors.password" class="field-msg danger">{{ fieldErrors.password }}</span>
      </label>
      <label class="form-group">
        <span class="muted">Подтверждение пароля</span>
        <div class="input-row">
          <input
            id="confirmPassword"
            v-model="form.confirmPassword"
            name="confirmPassword"
            :type="showPwd2 ? 'text' : 'password'"
            class="input"
            :class="fieldClass('confirmPassword')"
            autocomplete="new-password"
          />
          <button type="button" class="btn btn--ghost" tabindex="-1" @click="showPwd2 = !showPwd2">
            {{ showPwd2 ? 'Скрыть' : 'Показать' }}
          </button>
        </div>
        <span v-if="fieldErrors.confirmPassword" class="field-msg danger">{{ fieldErrors.confirmPassword }}</span>
      </label>
    </div>

    <label class="form-group">
      <span class="muted">Возраст</span>
      <select
        id="ageConfirmed"
        v-model="form.ageConfirmed"
        name="ageConfirmed"
        class="input select-like"
        :class="fieldClass('ageConfirmed')"
        @change="touchField('ageConfirmed')"
      >
        <option value="">Выберите...</option>
        <option value="true">Мне есть 18 лет</option>
        <option value="false">Нет 18 лет</option>
      </select>
      <span v-if="fieldErrors.ageConfirmed" class="field-msg danger">{{ fieldErrors.ageConfirmed }}</span>
    </label>

    <div class="form-group">
      <span class="muted">Пол</span>
      <div class="inline-radios">
        <label class="radio-label">
          <input v-model="form.gender" type="radio" name="gender" value="MALE" @change="touchField('gender')" />
          Мужской
        </label>
        <label class="radio-label">
          <input v-model="form.gender" type="radio" name="gender" value="FEMALE" @change="touchField('gender')" />
          Женский
        </label>
      </div>
      <span v-if="fieldErrors.gender" class="field-msg danger">{{ fieldErrors.gender }}</span>
    </div>

    <label class="checkbox-label">
      <input
        id="acceptRules"
        v-model="form.acceptRules"
        name="acceptRules"
        type="checkbox"
        @change="touchField('acceptRules')"
      />
      <span class="muted" style="font-size: 13px; line-height: 1.4">
        Я принимаю
        <RouterLink to="/terms">правила</RouterLink>
        и
        <RouterLink to="/privacy">политику конфиденциальности</RouterLink>
      </span>
    </label>
    <span v-if="fieldErrors.acceptRules" class="field-msg danger">{{ fieldErrors.acceptRules }}</span>

    <button class="btn btn--primary" type="submit" :disabled="loading" style="width: 100%">
      {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useRegistrationValidation } from '../composables/useRegistrationValidation'

const emit = defineEmits(['success', 'error'])

const loading = ref(false)
const showPwd = ref(false)
const showPwd2 = ref(false)

const {
  form,
  fieldErrors,
  onInput,
  onBlur,
  fieldClass,
  touchField,
  submitRegistration
} = useRegistrationValidation()

async function handleSubmit() {
  loading.value = true
  try {
    const data = await submitRegistration()
    if (data?.demoAuth && typeof localStorage !== 'undefined') {
      localStorage.setItem('demoAuth', data.demoAuth)
    }
    emit('success', data)
  } catch (e) {
    emit('error', e?.message || 'Ошибка регистрации')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reg-form {
  display: grid;
  gap: 14px;
}

.form-group {
  display: grid;
  gap: 6px;
}

.field-msg {
  font-size: 12px;
}

.input-row {
  display: flex;
  gap: 8px;
  align-items: stretch;
}

.input-row .input {
  flex: 1;
}

.btn--ghost {
  flex-shrink: 0;
  white-space: nowrap;
  padding: 8px 10px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.04);
}

.select-like {
  appearance: auto;
}

.inline-radios {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.radio-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 14px;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
}

.is-valid {
  border-color: rgba(46, 204, 113, 0.55) !important;
}

.is-invalid {
  border-color: rgba(255, 77, 109, 0.75) !important;
}
</style>
