import { ref, onMounted } from 'vue'

export function useAuth() {
  const isAuth = ref(false)
  const user = ref(null)

  onMounted(() => {
    checkAuth()
  })

  function checkAuth() {
    const authData = localStorage.getItem('demoAuth')
    if (authData) {
      isAuth.value = true
      user.value = parseAuthData(authData)
    } else {
      isAuth.value = false
      user.value = null
    }
  }

  function parseAuthData(data) {
    // Парсим "Demo user:1 roles:seller,customer"
    const parts = data.replace('Demo ', '').split(' ')
    const userData = {}
    parts.forEach(part => {
      if (part.startsWith('user:')) {
        userData.userId = parseInt(part.replace('user:', ''))
      } else if (part.startsWith('roles:')) {
        userData.roles = part.replace('roles:', '').split(',')
      }
    })
    return userData
  }

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

  function register(data) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        const authData = 'Demo user:1 roles:seller,customer'
        localStorage.setItem('demoAuth', authData)
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