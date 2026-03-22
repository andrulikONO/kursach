<template>
  <div class="info-page">
    <RouterLink to="/help" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← Назад к помощи
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Контакты</h1>

        <div class="contacts-grid">
          <div class="contact-card">
            <div class="contact-icon">📧</div>
            <h3 class="title">Email</h3>
            <a href="mailto:support@avitoclone.ru" class="contact-link">support@avitoclone.ru</a>
            <p class="muted">Ответ в течение 24 часов</p>
          </div>

          <div class="contact-card">
            <div class="contact-icon">📞</div>
            <h3 class="title">Телефон</h3>
            <a href="tel:+78000000000" class="contact-link">8 (800) 000-00-00</a>
            <p class="muted">Пн-Пт: 9:00 - 18:00 (МСК)</p>
          </div>

          <div class="contact-card">
            <div class="contact-icon">💬</div>
            <h3 class="title">Чат</h3>
            <span class="contact-link">Онлайн-чат</span>
            <p class="muted">Доступен на сайте</p>
          </div>
        </div>

        <div class="info-box card" style="margin-top: 24px; box-shadow: none">
          <div class="card__body">
            <strong>📍 Офис:</strong>
            <span class="muted"> г. Москва, ул. Примерная, д. 1 (для курьерской доставки)</span>
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

.contacts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
}

.contact-card {
  padding: 20px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  text-align: center;
}

.contact-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.contact-card h3 {
  margin: 0 0 8px 0;
  font-size: 16px;
}

.contact-link {
  color: var(--primary-2);
  font-weight: 600;
  display: block;
  margin-bottom: 4px;
}

.contact-link:hover {
  text-decoration: underline;
}

.contact-card p {
  margin: 0;
  font-size: 13px;
}

.info-box {
  background: rgba(124, 92, 255, 0.1);
  border: 1px solid rgba(124, 92, 255, 0.3);
}
</style>