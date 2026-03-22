import { ref, onMounted } from 'vue'

const isAuth = ref(false)
const user = ref(null)

function parseAuthData(data) {
  const userData = {}
  const parts = String(data).replace(/^Demo\s+/i, '').trim().split(/\s+/)
  parts.forEach((part) => {
    if (part.startsWith('user:')) {
      userData.userId = parseInt(part.replace('user:', ''), 10)
    } else if (part.startsWith('roles:')) {
      userData.roles = part.replace('roles:', '').split(',').map((r) => r.trim()).filter(Boolean)
    }
  })
  return userData
}

function checkAuth() {
  const authData = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  if (authData) {
    isAuth.value = true
    user.value = parseAuthData(authData)
  } else {
    isAuth.value = false
    user.value = null
  }
}

export function useAuth() {
  onMounted(() => {
    checkAuth()
  })

  function login(identifier, password) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        const authData = 'Demo user:1 roles:seller,customer'
        localStorage.setItem('demoAuth', authData)
        checkAuth()
        resolve()
      }, 500)
    })
  }

  /** @deprecated используйте форму регистрации с API; оставлено для совместимости */
  function register() {
    return new Promise((resolve) => {
      setTimeout(() => {
        localStorage.setItem('demoAuth', 'Demo user:1 roles:seller,customer')
        checkAuth()
        resolve()
      }, 500)
    })
  }

  function logout() {
    localStorage.removeItem('demoAuth')
    checkAuth()
  }

  return {
    isAuth,
    user,
    login,
    register,
    logout,
    checkAuth
  }
}
