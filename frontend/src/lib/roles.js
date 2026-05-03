export const ROLE_LABELS = {
  guest: 'Гость',
  user: 'Пользователь',
  support: 'Поддержка',
  moderator: 'Модератор',
  admin: 'Администратор',
  main_admin: 'Главный админ'  
}

const ROLE_PRIORITY = ['admin', 'support', 'moderator', 'user', 'guest']

/**
 * Возвращает человеко-читаемое название роли
 */
export function getRoleLabel(code) {
  return ROLE_LABELS[code] || code
}

/**
 * Сортирует роли по приоритету
 */
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

/**
 * ✅ Обновлённая функция: принимает userId для определения главного админа
 */
export function getPrimaryRole(roles, userId) {
  const sorted = sortRoles(roles)
  const primary = sorted[0] || 'user'
  
  // ✅ Если ID=1 и есть роль admin — это Главный админ
  if (userId === 1 && primary === 'admin') {
    return 'main_admin'
  }
  
  return primary
}