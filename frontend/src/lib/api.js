const API_BASE = ''

function getAuthHeaders() {
  // DEV-режим: чтобы форма "подача объявления" сразу работала с ролевой моделью на бэкенде
  // Можно переопределить через localStorage: demoAuth = "Demo user:2 roles:seller"
  const fromStorage = typeof localStorage !== 'undefined' ? localStorage.getItem('demoAuth') : null
  const demo = fromStorage || (import.meta?.env?.DEV ? 'Demo user:1 roles:seller' : null)
  return demo ? { Authorization: demo } : {}
}

async function request(path, { method = 'GET', body, headers } = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      ...getAuthHeaders(),
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
    const msg = (data && data.error) || `HTTP ${res.status}`
    throw new Error(msg)
  }
  return data
}

export function fetchProducts(params) {
  const qs = new URLSearchParams()
  if (params?.q) qs.set('q', params.q)
  if (params?.type) qs.set('type', params.type)
  if (params?.minPrice != null && params.minPrice !== '') qs.set('minPrice', params.minPrice)
  if (params?.maxPrice != null && params.maxPrice !== '') qs.set('maxPrice', params.maxPrice)

  const suffix = qs.toString() ? `?${qs.toString()}` : ''
  return request(`/api/products${suffix}`)
}

export function fetchProductById(id) {
  return request(`/api/products/${encodeURIComponent(id)}`)
}

export function createProduct(payload) {
  // позже сюда можно добавить Authorization header
  return request('/api/products', { method: 'POST', body: payload })
}

