<template>
  <div class="info-page">
    <RouterLink to="/help" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← Назад к помощи
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Правила безопасности</h1>

        <div class="safety-section">
          <h2 class="title" style="margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px">
            🛡️ Для покупателей
          </h2>
          <ul class="safety-list">
            <li>Не переводите предоплату незнакомым людям</li>
            <li>Встречайтесь в людных общественных местах</li>
            <li>Проверяйте товар перед оплатой</li>
            <li>Не сообщайте данные карты или паспорта</li>
            <li>Используйте безопасные способы оплаты</li>
          </ul>
        </div>

        <div class="safety-section" style="margin-top: 32px">
          <h2 class="title" style="margin: 0 0 16px 0; display: flex; align-items: center; gap: 10px">
            📦 Для продавцов
          </h2>
          <ul class="safety-list">
            <li>Не отправляйте товар без подтверждения оплаты</li>
            <li>Будьте осторожны с «покупателями из других городов»</li>
            <li>Не переходите по подозрительным ссылкам</li>
            <li>Проверяйте подлинность платежных уведомлений</li>
            <li>Сохраняйте переписку до завершения сделки</li>
          </ul>
        </div>

        <div class="danger-box card" style="margin-top: 32px; box-shadow: none">
          <div class="card__body">
            <strong style="color: var(--danger)">⚠️ Внимание:</strong>
            <span class="muted"> Администрация никогда не просит данные карты или пароль по телефону!</span>
          </div>
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
  max-width: 800px;
}

.safety-section {
  display: grid;
  gap: 12px;
}

.safety-list {
  margin: 0;
  padding-left: 20px;
  display: grid;
  gap: 8px;
}

.safety-list li {
  color: var(--text);
  line-height: 1.5;
}

.danger-box {
  background: rgba(255, 77, 109, 0.1);
  border: 1px solid rgba(255, 77, 109, 0.3);
}
</style>