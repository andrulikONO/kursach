<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 12px 0">Безопасность сделок</h1>
        <p class="muted" style="margin-bottom: 32px">
          Как мы защищаем покупателей и продавцов на платформе
        </p>

        <div class="security-features">
          <div class="security-item">
            <div class="security-icon">🛡️</div>
            <div class="security-content">
              <h3 class="title">Проверка пользователей</h3>
              <p class="muted">
                Верификация по телефону и email снижает риск мошенничества. 
                Продавцы с подтверждёнными данными получают значок доверия.
              </p>
            </div>
          </div>

          <div class="security-item">
            <div class="security-icon">🔒</div>
            <div class="security-content">
              <h3 class="title">Защищённые платежи</h3>
              <p class="muted">
                Деньги резервируются до подтверждения получения товара. 
                Возврат средств при несоответствии описанию.
              </p>
            </div>
          </div>

          <div class="security-item">
            <div class="security-icon">👁️</div>
            <div class="security-content">
              <h3 class="title">Модерация объявлений</h3>
              <p class="muted">
                Автоматическая и ручная проверка контента. 
                Блокировка подозрительных объявлений в течение 15 минут.
              </p>
            </div>
          </div>

          <div class="security-item">
            <div class="security-icon">💬</div>
            <div class="security-content">
              <h3 class="title">Внутренний чат</h3>
              <p class="muted">
                Общение внутри платформы без передачи личных контактов. 
                История переписки сохраняется для разрешения споров.
              </p>
            </div>
          </div>

          <div class="security-item">
            <div class="security-icon">⚖️</div>
            <div class="security-content">
              <h3 class="title">Арбитраж споров</h3>
              <p class="muted">
                Служба поддержки помогает решить конфликт между покупателем 
                и продавцом в течение 3 рабочих дней.
              </p>
            </div>
          </div>

          <div class="security-item">
            <div class="security-icon">📊</div>
            <div class="security-content">
              <h3 class="title">Рейтинг и отзывы</h3>
              <p class="muted">
                Система оценок помогает выбрать надёжного продавца. 
                Подозрительные аккаунты блокируются при жалобах.
              </p>
            </div>
          </div>
        </div>

        <div class="safety-tips card" style="margin-top: 32px; box-shadow: none">
          <div class="card__body">
            <h3 class="title" style="margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px">
              ⚠️ Правила безопасной сделки
            </h3>
            <ul class="safety-list">
              <li>Не переводите деньги до получения товара</li>
              <li>Встречайтесь в людных общественных местах</li>
              <li>Проверяйте товар перед оплатой</li>
              <li>Не сообщайте данные карты или паспорта</li>
              <li>Сохраняйте переписку до завершения сделки</li>
              <li>При подозрениях — обратитесь в поддержку</li>
            </ul>
          </div>
        </div>

        <div class="split" style="margin-top: 32px; gap: 16px">
          <RouterLink to="/help/safety" class="btn btn--secondary">
            📚 Подробнее о безопасности
          </RouterLink>
          <RouterLink to="/support" class="btn btn--primary">
            🚨 Сообщить о нарушении
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.info-page {
  display: grid;
  gap: 16px;
  max-width: 900px;
}

.security-features {
  display: grid;
  gap: 20px;
}

.security-item {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
}

.security-icon {
  font-size: 32px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.22), rgba(77, 211, 255, 0.14));
  border-radius: 12px;
  flex-shrink: 0;
}

.security-content {
  flex: 1;
}

.security-content h3 {
  margin: 0 0 8px 0;
  font-size: 16px;
}

.security-content p {
  margin: 0;
  line-height: 1.5;
}

.safety-tips {
  background: rgba(124, 92, 255, 0.1);
  border: 1px solid rgba(124, 92, 255, 0.3);
}

.safety-list {
  margin: 0;
  padding-left: 20px;
  display: grid;
  gap: 10px;
}

.safety-list li {
  color: var(--text);
  line-height: 1.5;
}

@media (max-width: 640px) {
  .security-item {
    flex-direction: column;
  }
  
  .split {
    flex-direction: column;
  }
}
</style>