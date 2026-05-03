import { ref } from 'vue'
import { fetchMe, loginUser, setUnauthorizedHandler } from '../lib/api'
import { getPrimaryRole, getRoleLabel, sortRoles } from '../lib/roles'

const isAuth = ref(false)
const user = ref(null)
const lastEventId = ref(Number(localStorage.getItem('lastEventId') || 0))
let eventSource = null

function parseUserIdFromToken(data) {
  const m = String(data || '').match(/user:(\d+)/i)
  return m ? parseInt(m[1], 10) : null
}

function buildUserProfile(profile) {
  const roles = sortRoles(profile?.roles || [])
  const primaryRole = getPrimaryRole(roles)
  const fio = profile?.fio || [profile?.first_name || profile?.firstName || '', profile?.last_name || profile?.lastName || ''].filter(Boolean).join(' ').trim()
  return {
    userId: profile?.id ?? profile?.userId ?? null,
    login: profile?.login || '',
    email: profile?.email || '',
    phone: profile?.phone || '',
    fio,
    firstName: profile?.first_name || profile?.firstName || '',
    lastName: profile?.last_name || profile?.lastName || '',
    roles,
    primaryRole,
    primaryRoleLabel: getRoleLabel(primaryRole),
    isBlocked: !!(profile?.is_blocked ?? profile?.isBlocked)
  }
}

function saveProfile(profile) {
  user.value = profile
  localStorage.setItem('userProfile', JSON.stringify(profile))
}

function hardLogout() {
  localStorage.removeItem('demoAuth')
  localStorage.removeItem('userProfile')
  localStorage.removeItem('lastEventId')
  if (eventSource) {
    eventSource.close()
    eventSource = null
  }
  isAuth.value = false
  user.value = null
}

function checkAuth() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  if (!token) {
    hardLogout()
    return
  }
  isAuth.value = true
  const cachedProfile = typeof localStorage !== 'undefined' ? localStorage.getItem('userProfile') : null
  if (cachedProfile) {
    try {
      saveProfile(buildUserProfile(JSON.parse(cachedProfile)))
      return
    } catch {}
  }
  saveProfile(buildUserProfile({ userId: parseUserIdFromToken(token), roles: ['user'] }))
}

async function refreshProfile() {
  if (!localStorage.getItem('demoAuth')) {
    hardLogout()
    return null
  }
  const me = await fetchMe()
  const profile = buildUserProfile(me)
  saveProfile(profile)
  return profile
}

function updateUserRealtime(patch) {
  if (!user.value) return
  const next = buildUserProfile({ ...user.value, ...patch })
  saveProfile(next)
}

function connectEvents() {
  if (!isAuth.value) return
  if (eventSource) {
    eventSource.close()
  }
  eventSource = new EventSource(`/api/events/stream?lastEventId=${encodeURIComponent(lastEventId.value || 0)}`)
  eventSource.onmessage = (e) => {
    if (e.lastEventId) {
      lastEventId.value = Number(e.lastEventId)
      localStorage.setItem('lastEventId', String(lastEventId.value))
    }
  }
  eventSource.addEventListener('role_changed', (e) => {
    try {
      const data = JSON.parse(e.data || '{}')
      updateUserRealtime({ roles: Array.isArray(data.roles) ? data.roles : [] })
    } catch {}
  })
  eventSource.addEventListener('account_deleted', () => {
    hardLogout()
    window.location.href = '/login'
  })
  eventSource.onerror = () => {
    if (eventSource) eventSource.close()
    setTimeout(() => {
      if (isAuth.value) connectEvents()
    }, 1500)
  }
}

setUnauthorizedHandler(() => {
  hardLogout()
  if (window.location.pathname !== '/login') {
    window.location.href = '/login'
  }
})

export async function initAuth() {
  checkAuth()
  if (isAuth.value) {
    await refreshProfile()
    connectEvents()
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
    connectEvents()
  }

  function logout() {
    hardLogout()
  }

  function isAdmin() {
    return (user.value?.roles || []).includes('admin')
  }

  return {
    isAuth,
    user,
    login,
    logout,
    checkAuth,
    refreshProfile,
    isAdmin,
    updateUserRealtime
  }
}
