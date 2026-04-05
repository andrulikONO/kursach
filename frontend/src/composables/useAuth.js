import { ref } from 'vue'
import { fetchMe, loginUser } from '../lib/api'

const isAuth = ref(false)
const user = ref(null)

function parseUserIdFromToken(data) {
  const m = String(data || '').match(/user:(\d+)/i)
  return m ? parseInt(m[1], 10) : null
}

function checkAuth() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  if (!token) {
    isAuth.value = false
    user.value = null
    return
  }
  isAuth.value = true
  if (!user.value?.roles?.length) {
    user.value = { userId: parseUserIdFromToken(token), roles: [] }
  }
}

async function refreshProfile() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  if (!token) {
    user.value = null
    return null
  }
  try {
    const me = await fetchMe()
    user.value = {
      userId: me.id,
      login: me.login,
      email: me.email,
      phone: me.phone,
      firstName: me.first_name,
      lastName: me.last_name,
      roles: Array.isArray(me.roles) ? me.roles : [],
      isBlocked: !!me.is_blocked
    }
    return user.value
  } catch {
    user.value = { userId: parseUserIdFromToken(token), roles: [] }
    return user.value
  }
}

/** Вызови один раз из App.vue (onMounted) */
export async function initAuth() {
  checkAuth()
  if (isAuth.value) {
    await refreshProfile()
  }
}

export function useAuth() {
  async function login(identifier, password) {
    const r = await loginUser({ identifier, password })
    if (r?.demoAuth) {
      localStorage.setItem('demoAuth', r.demoAuth)
    }
    isAuth.value = true
    await refreshProfile()
  }

  function register() {
    return Promise.resolve()
  }

  function logout() {
    localStorage.removeItem('demoAuth')
    isAuth.value = false
    user.value = null
  }

  function isAdmin() {
    return (user.value?.roles || []).includes('admin')
  }

  return {
    isAuth,
    user,
    login,
    register,
    logout,
    checkAuth,
    refreshProfile,
    isAdmin
  }
}
