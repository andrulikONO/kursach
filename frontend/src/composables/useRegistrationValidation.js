import { reactive } from 'vue'
import { checkAuthAvailability, registerUser } from '../lib/api'

/**
 * Динамическая валидация как в UserAuthApp (validation.js):
 * input/blur, проверка занятости login/email, сильный пароль, совпадение паролей.
 */
const EMAIL_RE = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/

function normalizePhone(raw) {
  const d = String(raw || '').replace(/\D/g, '')
  if (d.length === 11 && d[0] === '8') return '+7' + d.slice(1)
  if (d.length === 11 && d[0] === '7') return '+' + d
  if (d.length === 10) return '+7' + d
  return String(raw || '').trim()
}

function isValidPhone(raw) {
  return /^\+7\d{10}$/.test(normalizePhone(raw))
}

function checkStrongPassword(pwd) {
  const s = pwd || ''
  return (
    s.length >= 6
    && !/\s/.test(s)
    && /[a-z]/.test(s)
    && /[A-Z]/.test(s)
    && /\d/.test(s)
    && /[^A-Za-z0-9]/.test(s)
  )
}

export function useRegistrationValidation() {
  const form = reactive({
    firstName: '',
    lastName: '',
    email: '',
    login: '',
    phone: '',
    password: '',
    confirmPassword: '',
    ageConfirmed: '', // 'true' | 'false' | ''
    gender: '', // MALE | FEMALE | ''
    acceptRules: false
  })

  const availabilityState = reactive({ email: null, login: null })
  const fieldErrors = reactive({
    firstName: '',
    lastName: '',
    email: '',
    login: '',
    phone: '',
    password: '',
    confirmPassword: '',
    ageConfirmed: '',
    gender: '',
    acceptRules: ''
  })

  const touched = reactive({
    firstName: false,
    lastName: false,
    email: false,
    login: false,
    phone: false,
    password: false,
    confirmPassword: false,
    ageConfirmed: false,
    gender: false,
    acceptRules: false
  })

  const textFields = ['firstName', 'lastName', 'email', 'login', 'phone']
  const allFields = [...textFields, 'password', 'confirmPassword', 'ageConfirmed', 'gender', 'acceptRules']

  function setError(fieldId, msg) {
    fieldErrors[fieldId] = msg || ''
  }

  function validateField(fieldId) {
    let valid = true
    let msg = ''

    if (fieldId === 'firstName') {
      const v = (form.firstName || '').trim()
      if (v.length < 2 || v.length > 15) {
        valid = false
        msg = 'Имя: от 2 до 15 символов'
      } else if (!/^[A-Za-z-]+$/.test(v)) {
        valid = false
        msg = 'Только латинские буквы и дефис'
      }
    } else if (fieldId === 'lastName') {
      const v = (form.lastName || '').trim()
      if (v.length < 2 || v.length > 15) {
        valid = false
        msg = 'Фамилия: от 2 до 15 символов'
      } else if (!/^[A-Za-z-]+$/.test(v)) {
        valid = false
        msg = 'Только латинские буквы и дефис'
      }
    } else if (fieldId === 'email') {
      const v = (form.email || '').trim()
      if (!v) {
        valid = false
        msg = 'Введите email'
      } else if (!EMAIL_RE.test(v)) {
        valid = false
        msg = 'Введите корректный email'
      } else if (availabilityState.email === false) {
        valid = false
        msg = 'Email уже занят'
      }
    } else if (fieldId === 'login') {
      const v = (form.login || '').trim()
      if (v.length < 6) {
        valid = false
        msg = 'Логин: не менее 6 символов'
      } else if (availabilityState.login === false) {
        valid = false
        msg = 'Логин уже занят'
      }
    } else if (fieldId === 'phone') {
      if (!isValidPhone(form.phone || '')) {
        valid = false
        msg = 'Телефон: формат +7XXXXXXXXXX'
      }
    } else if (fieldId === 'password') {
      let raw = form.password || ''
      const v = raw.replace(/\s+/g, '')
      if (v !== raw) form.password = v
      if (!v) {
        valid = false
        msg = 'Введите пароль'
      } else if (!checkStrongPassword(v)) {
        valid = false
        msg = 'Пароль: мин. 6 символов, строчная, заглавная, цифра и спецсимвол (без пробелов)'
      }
    } else if (fieldId === 'confirmPassword') {
      let raw = form.confirmPassword || ''
      const v2 = raw.replace(/\s+/g, '')
      if (v2 !== raw) form.confirmPassword = v2
      if (form.confirmPassword !== form.password) {
        valid = false
        msg = 'Пароли не совпадают'
      }
    } else if (fieldId === 'ageConfirmed') {
      if (form.ageConfirmed !== 'true' && form.ageConfirmed !== 'false') {
        valid = false
        msg = 'Выберите один из вариантов'
      }
    } else if (fieldId === 'gender') {
      if (form.gender !== 'MALE' && form.gender !== 'FEMALE') {
        valid = false
        msg = 'Укажите пол'
      }
    } else if (fieldId === 'acceptRules') {
      if (!form.acceptRules) {
        valid = false
        msg = 'Необходимо согласие с правилами'
      }
    }

    setError(fieldId, valid ? '' : msg)

    const elValid = valid
    let showValid = elValid
    if (fieldId === 'password' || fieldId === 'confirmPassword') {
      showValid = elValid && !!(form[fieldId] && String(form[fieldId]).length)
    }
    if (fieldId === 'acceptRules') {
      showValid = elValid && !!form.acceptRules
    }

    return { valid: elValid, showValid, msg }
  }

  function fieldClass(fieldId) {
    const t = touched[fieldId]
    if (!t) return {}
    const { valid, showValid } = validateField(fieldId)
    return {
      'is-valid': showValid,
      'is-invalid': !valid
    }
  }

  async function checkAvailability(fieldId) {
    const el = fieldId
    if (el !== 'login' && el !== 'email') return
    const v = (form[el] || '').trim()
    if (!v) return
    if (el === 'login' && v.length < 6) return

    try {
      const data = await checkAuthAvailability(el, v)
      availabilityState[el] = !!data.available
      if (!data.available && data.message) {
        setError(el, data.message)
      }
      validateField(el)
    } catch {
      // тихо, как в оригинальном validation.js
    }
  }

  function onInput(e) {
    const t = e?.target
    if (!t?.name && !t?.id) return
    const id = t.name || t.id
    if (!allFields.includes(id)) return

    if (textFields.includes(id)) {
      let v = t.value || ''
      if (v.length && v.charAt(0) === ' ') {
        v = v.replace(/^\s+/, '')
        t.value = v
        form[id] = v
      }
    }

    if (id === 'login' || id === 'email') {
      availabilityState[id] = null
    }

    validateField(id)
    if (id === 'password') validateField('confirmPassword')
  }

  function onBlur(e) {
    const t = e?.target
    if (!t?.name && !t?.id) return
    const id = t.name || t.id
    if (!allFields.includes(id)) return
    touched[id] = true

    if (textFields.includes(id)) {
      t.value = (t.value || '').trim()
      form[id] = t.value
    }

    validateField(id)
    if (id === 'login' || id === 'email') {
      checkAvailability(id)
    }
    if (id === 'password') {
      validateField('confirmPassword')
    }
  }

  function touchField(fieldId) {
    if (allFields.includes(fieldId)) {
      touched[fieldId] = true
      validateField(fieldId)
    }
  }

  function validateAll() {
    allFields.forEach((id) => { touched[id] = true })
    textFields.forEach((id) => {
      form[id] = (form[id] || '').trim()
    })
    form.phone = normalizePhone(String(form.phone || '').replace(/\s/g, ''))
    let ok = true
    allFields.forEach((id) => {
      const { valid } = validateField(id)
      if (!valid) ok = false
    })
    return ok
  }

  async function submitRegistration() {
    if (!validateAll()) {
      const err = new Error('Проверьте правильность заполнения полей')
      err.fields = fieldErrors
      throw err
    }
    const payload = {
      firstName: form.firstName.trim(),
      lastName: form.lastName.trim(),
      email: form.email.trim(),
      login: form.login.trim(),
      phone: normalizePhone(form.phone || ''),
      password: form.password,
      confirmPassword: form.confirmPassword,
      ageConfirmed: form.ageConfirmed === 'true',
      gender: form.gender,
      acceptRules: form.acceptRules
    }
    try {
      return await registerUser(payload)
    } catch (e) {
      if ((e?.status === 422 || e?.status === 409) && e?.data?.fields) {
        Object.assign(fieldErrors, e.data.fields)
      }
      throw e
    }
  }

  return {
    form,
    fieldErrors,
    touched,
    availabilityState,
    textFields,
    allFields,
    validateField,
    fieldClass,
    onInput,
    onBlur,
    validateAll,
    touchField,
    checkAvailability,
    submitRegistration
  }
}
