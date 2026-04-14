import { ref } from 'vue'
import { fetchMe, loginUser } from '../lib/api'

const isAuth = ref(false)
const user = ref(null)

function parseUserIdFromToken(data) {
  const m = String(data || '').match(/user:(\d+)/i)
  return m ? parseInt(m[1], 10) : null
}

function getRoleLabel(code) {
  const map = {
    user: 'Пользователь',
    support: 'Поддержка',
    admin: 'Админ',
    moderator: 'Модератор'
  }
  return map[code] || code
}

function checkAuth() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  
  if (!token) {
    isAuth.value = false
    user.value = null
    localStorage.removeItem('userProfile')
    return
  }
  
  isAuth.value = true
  
  // Минимальный профиль до загрузки API
  const minimalProfile = { 
    userId: parseUserIdFromToken(token), 
    roles: [],
    login: '',
    primaryRole: 'user',
    primaryRoleLabel: 'Пользователь'
  }
  user.value = minimalProfile
  localStorage.setItem('userProfile', JSON.stringify(minimalProfile))
}

async function refreshProfile() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  if (!token) {
    user.value = null
    localStorage.removeItem('userProfile')
    return null
  }
  
  try {
    const me = await fetchMe()
    const primaryRole = me.roles?.[0] || 'user'
    
    user.value = {
      userId: me.id,
      login: me.login,
      email: me.email,
      phone: me.phone,
      firstName: me.first_name,
      lastName: me.last_name,
      gender: me.gender,
      roles: Array.isArray(me.roles) ? me.roles : [],
      isBlocked: !!me.is_blocked,
      primaryRole: primaryRole,
      primaryRoleLabel: getRoleLabel(primaryRole)
    }
    
    localStorage.setItem('userProfile', JSON.stringify(user.value))
    return user.value
  } catch (e) {
    console.warn('Profile load failed', e)
    const minimal = { 
      userId: parseUserIdFromToken(token), 
      roles: [], 
      login: '',
      primaryRole: 'user',
      primaryRoleLabel: 'Пользователь'
    }
    user.value = minimal
    localStorage.setItem('userProfile', JSON.stringify(minimal))
    return minimal
  }
}

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
    localStorage.removeItem('userProfile')
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