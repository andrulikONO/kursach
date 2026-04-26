export const ROLE_LABELS = {
  guest: 'Гость',
  user: 'Пользователь',
  support: 'Поддержка',
  moderator: 'Модератор',
  admin: 'Администратор'
}

const ROLE_PRIORITY = ['admin', 'support', 'moderator', 'user', 'guest']

export function getRoleLabel(code) {
  return ROLE_LABELS[code] || code
}

export function sortRoles(roles) {
  const uniqueRoles = Array.from(new Set(Array.isArray(roles) ? roles : []))

  return uniqueRoles.sort((left, right) => {
    const leftIndex = ROLE_PRIORITY.indexOf(left)
    const rightIndex = ROLE_PRIORITY.indexOf(right)
    const safeLeftIndex = leftIndex === -1 ? ROLE_PRIORITY.length : leftIndex
    const safeRightIndex = rightIndex === -1 ? ROLE_PRIORITY.length : rightIndex

    if (safeLeftIndex !== safeRightIndex) {
      return safeLeftIndex - safeRightIndex
    }

    return String(left).localeCompare(String(right))
  })
}

export function getPrimaryRole(roles) {
  return sortRoles(roles)[0] || 'user'
}
