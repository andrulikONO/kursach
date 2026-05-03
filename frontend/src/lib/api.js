const API_BASE = ''
let unauthorizedHandler = null

export function setUnauthorizedHandler(handler) {
  unauthorizedHandler = typeof handler === 'function' ? handler : null
}

function getAuthHeaders() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  return token ? { Authorization: token } : {}
}

async function request(path, { method = 'GET', body, headers, skipAuth = false } = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      ...(skipAuth ? {} : getAuthHeaders()),
      ...(headers || {})
    },
    body: body ? JSON.stringify(body) : undefined
  })

  const text = await res.text()
  let data = null
  try {
    data = text ? JSON.parse(text) : null
  } catch {
    data = text
  }

  if (!res.ok) {
    if ((res.status === 401 || res.status === 403) && unauthorizedHandler && !skipAuth) {
      unauthorizedHandler({ status: res.status, data })
    }
    let msg = (data && data.error) || `HTTP ${res.status}`
    if (res.status === 422 && data && data.fields) {
      msg = 'Проверьте правильность заполнения полей'
    }
    if (res.status === 409) {
      msg = (data && data.error) || 'Данные уже заняты'
    }
    const err = new Error(msg)
    err.status = res.status
    err.data = data
    throw err
  }
  return data
}

export function authRequest(path, options = {}) {
  return request(path, options)
}

export function fetchProducts(params) {
  const qs = new URLSearchParams()
  if (params?.q) qs.set('q', params.q)
  if (params?.type) qs.set('type', params.type)
  if (params?.category) qs.set('category', params.category)
  if (params?.listingKind) qs.set('listingKind', params.listingKind)
  if (params?.minPrice != null && params.minPrice !== '') qs.set('minPrice', params.minPrice)
  if (params?.maxPrice != null && params.maxPrice !== '') qs.set('maxPrice', params.maxPrice)
  if (params?.page != null && params.page !== '') qs.set('page', String(params.page))
  if (params?.perPage != null && params.perPage !== '') qs.set('perPage', String(params.perPage))

  const suffix = qs.toString() ? `?${qs.toString()}` : ''
  return request(`/api/products${suffix}`)
}

export function fetchProductById(id) {
  return request(`/api/products/${encodeURIComponent(id)}`)
}

export function createProduct(payload) {
  return request('/api/products', { method: 'POST', body: payload })
}

export function deleteProduct(id) {
  return request(`/api/products/${encodeURIComponent(id)}`, { method: 'DELETE' })
}

export function fetchProductPhone(id) {
  return request(`/api/products/${encodeURIComponent(id)}/phone`)
}

export function fetchProductComments(id) {
  return request(`/api/products/${encodeURIComponent(id)}/comments`)
}

export function createProductComment(id, payload) {
  return request(`/api/products/${encodeURIComponent(id)}/comments`, { method: 'POST', body: payload })
}

export function fetchTickets() {
  return request('/api/tickets')
}

export function createTicket(payload) {
  return request('/api/tickets', { method: 'POST', body: payload })
}

export function fetchTicketById(id) {
  return request(`/api/tickets/${id}`)
}

export function respondToTicket(id, payload) {
  return request(`/api/tickets/${id}/respond`, { method: 'POST', body: payload })
}

export function fetchMe() {
  return request('/api/me')
}
export function updateMe(payload) {
  return request('/api/me', { method: 'PATCH', body: payload })
}

export function fetchMyProducts() {
  return request('/api/my/products')
}

export function loginUser(payload) {
  return request('/api/auth/login', { method: 'POST', body: payload, skipAuth: true })
}


export function checkAuthAvailability(type, value) {
  const qs = new URLSearchParams({ type, value })
  return request(`/api/auth/check-availability?${qs.toString()}`, { skipAuth: true })
}

export function registerUser(payload) {
  return request('/api/auth/register', { method: 'POST', body: payload, skipAuth: true })
}

export function fetchAdminRoles() {
  return request('/api/admin/roles')
}

export function adminAssignRole(userId, roleCode) {
  return request(`/api/admin/users/${userId}/roles`, {
    method: 'POST',
    body: { roleCode }
  })
}

export function adminRemoveRole(userId, roleCode) {
  return request(`/api/admin/users/${userId}/roles/${roleCode}`, {
    method: 'DELETE'
  })
}

export function assignUserRole(userId, roleCode) {
  return request(`/api/admin/users/${userId}/roles`, {
    method: 'POST',
    body: { roleCode }
  })
}

export function removeUserRole(userId, roleCode) {
  return request(`/api/admin/users/${userId}/roles/${roleCode}`, {
    method: 'DELETE'
  })
}

export function fetchAdminUsers() {
  return request('/api/admin/users')
}

export function adminBlockUser(userId) {
  return request(`/api/admin/users/${encodeURIComponent(userId)}/block`, { method: 'POST' })
}

export function adminUnblockUser(userId) {
  return request(`/api/admin/users/${encodeURIComponent(userId)}/unblock`, { method: 'POST' })
}

export function adminDeleteUser(userId) {
  return request(`/api/admin/users/${encodeURIComponent(userId)}`, { method: 'DELETE' })
}

export function fetchChatDialogs() {
  return request('/api/chat/dialogs')
}

export function fetchChatMessages(peerId, afterId = 0) {
  const qs = new URLSearchParams({ peerId: String(peerId) })
  if (afterId) qs.set('afterId', String(afterId))
  return request(`/api/chat/messages?${qs.toString()}`)
}

export function sendChatMessage(receiverId, body) {
  return request('/api/chat/messages', { method: 'POST', body: { receiverId, body } })
}
