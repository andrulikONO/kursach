<template>
  <div class="help-page">
    <div class="page-header">
      <h1 class="title">Помощь</h1>
      <p class="muted">Ответы на частые вопросы и полезная информация</p>
    </div>

    <div class="help-grid">
      <RouterLink to="/help/how-to-post" class="help-card card">
        <div class="help-card__icon">📝</div>
        <div class="help-card__content">
          <h3 class="title">Как разместить объявление</h3>
          <p class="muted">Пошаговая инструкция по созданию объявления</p>
        </div>
        <span class="btn btn--small">→</span>
      </RouterLink>

      <RouterLink to="/help/safety" class="help-card card">
        <div class="help-card__icon">🛡️</div>
        <div class="help-card__content">
          <h3 class="title">Правила безопасности</h3>
          <p class="muted">Как избежать мошенников и безопасно совершать сделки</p>
        </div>
        <span class="btn btn--small">→</span>
      </RouterLink>

      <RouterLink to="/help/contacts" class="help-card card">
        <div class="help-card__icon">📞</div>
        <div class="help-card__content">
          <h3 class="title">Контакты</h3>
          <p class="muted">Способы связи со службой поддержки</p>
        </div>
        <span class="btn btn--small">→</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.help-page {
  display: grid;
  gap: 24px;
}

.page-header {
  display: grid;
  gap: 8px;
}

.page-header h1 {
  margin: 0;
}

.help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

.help-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  text-decoration: none;
  color: inherit;
  transition: transform 140ms ease, border-color 140ms ease;
}

.help-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.28);
}

.help-card__icon {
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

.help-card__content {
  flex: 1;
  min-width: 0;
}

.help-card__content h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
}

.help-card__content p {
  margin: 0;
  font-size: 14px;
}

.btn--small {
  padding: 8px 12px;
  font-size: 18px;
  flex-shrink: 0;
}
</style>