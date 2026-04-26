import { ref } from 'vue'
import { fetchMe, loginUser } from '../lib/api'
import { getPrimaryRole, getRoleLabel, sortRoles } from '../lib/roles'

const isAuth = ref(false)
const user = ref(null)

function parseUserIdFromToken(data) {
  const m = String(data || '').match(/user:(\d+)/i)
  return m ? parseInt(m[1], 10) : null
}

function buildUserProfile(profile) {
  const roles = sortRoles(profile?.roles || [])
  const primaryRole = getPrimaryRole(roles)

  return {
    userId: profile?.id ?? profile?.userId ?? null,
    login: profile?.login || '',
    email: profile?.email || '',
    phone: profile?.phone || '',
    firstName: profile?.first_name || profile?.firstName || '',
    lastName: profile?.last_name || profile?.lastName || '',
    gender: profile?.gender || '',
    roles,
    isBlocked: !!(profile?.is_blocked ?? profile?.isBlocked),
    primaryRole,
    primaryRoleLabel: getRoleLabel(primaryRole)
  }
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

  const cachedProfile = typeof localStorage !== 'undefined' ? localStorage.getItem('userProfile') : null
  if (cachedProfile) {
    try {
      user.value = buildUserProfile(JSON.parse(cachedProfile))
      return
    } catch {
      // Если кэш поврежден, восстановим минимальный профиль ниже.
    }
  }

  const minimalProfile = buildUserProfile({
    userId: parseUserIdFromToken(token),
    roles: ['user']
  })
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
    user.value = buildUserProfile(me)

    localStorage.setItem('userProfile', JSON.stringify(user.value))
    return user.value
  } catch (e) {
    console.warn('Profile load failed', e)
    const minimal = buildUserProfile({
      userId: parseUserIdFromToken(token),
      roles: ['user']
    })
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
