<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Политика конфиденциальности</h1>

        <div class="legal-section">
          <h2 class="title">1. Общие положения</h2>
          <p class="muted">
            Настоящая политика конфиденциальности описывает, как мы собираем, используем и защищаем 
            вашу личную информацию при использовании сайта «Объявления».
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">2. Сбор информации</h2>
          <p class="muted">Мы можем собирать следующую информацию:</p>
          <ul class="legal-list">
            <li>Контактные данные (email, телефон)</li>
            <li>Информация о размещённых объявлениях</li>
            <li>Технические данные (IP-адрес, браузер)</li>
          </ul>
        </div>

        <div class="legal-section">
          <h2 class="title">3. Использование информации</h2>
          <p class="muted">
            Собранная информация используется для предоставления услуг, улучшения работы сайта 
            и связи с пользователями.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">4. Защита данных</h2>
          <p class="muted">
            Мы принимаем технические и организационные меры для защиты вашей информации от 
            несанкционированного доступа.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">5. Контакты</h2>
          <p class="muted">
            По вопросам конфиденциальности обращайтесь: 
            <a href="mailto:privacy@avitoclone.ru" class="link">privacy@avitoclone.ru</a>
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

.link {
  color: var(--primary-2);
}
</style>