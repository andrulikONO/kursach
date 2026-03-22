<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Условия использования</h1>

        <div class="legal-section">
          <h2 class="title">1. Принятие условий</h2>
          <p class="muted">
            Используя сайт «Объявления», вы соглашаетесь с настоящими условиями использования. 
            Если вы не согласны, пожалуйста, не используйте сайт.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">2. Правила размещения</h2>
          <p class="muted">Запрещено размещать:</p>
          <ul class="legal-list">
            <li>Объявления о запрещённых товарах и услугах</li>
            <li>Недостоверную или вводящую в заблуждение информацию</li>
            <li>Контент, нарушающий права третьих лиц</li>
            <li>Спам и рекламные рассылки</li>
          </ul>
        </div>

        <div class="legal-section">
          <h2 class="title">3. Ответственность</h2>
          <p class="muted">
            Пользователь несёт полную ответственность за размещённый контент и совершаемые сделки. 
            Администрация не гарантирует результат сделок между пользователями.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">4. Изменение условий</h2>
          <p class="muted">
            Администрация оставляет за собой право изменять условия использования. 
            Продолжение использования сайта означает принятие новых условий.
          </p>
        </div>

        <div class="muted" style="margin-top: 32px; font-size: 13px">
          Последнее обновление: {{ currentDate }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const currentDate = new Date().toLocaleDateString('ru-RU')
</script>

<style scoped>
.info-page {
  display: grid;
  gap: 16px;
  max-width: 800px;
}

.legal-section {
  display: grid;
  gap: 12px;
  margin-bottom: 24px;
}

.legal-section h2 {
  margin: 0;
  font-size: 18px;
}

.legal-section p {
  margin: 0;
  line-height: 1.6;
}

.legal-list {
  margin: 8px 0 0 20px;
  padding: 0;
  display: grid;
  gap: 6px;
}

.legal-list li {
  color: var(--muted);
}
</style>