<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Публичная оферта</h1>

        <div class="legal-section">
          <h2 class="title">1. Термины и определения</h2>
          <p class="muted">
            <strong>Исполнитель</strong> — администрация сайта «Объявления».<br>
            <strong>Пользователь</strong> — любое физическое или юридическое лицо, использующее сайт.<br>
            <strong>Услуги</strong> — размещение объявлений и сопутствующие сервисы.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">2. Предмет оферты</h2>
          <p class="muted">
            Исполнитель обязуется предоставить Пользователю доступ к сервису размещения объявлений, 
            а Пользователь обязуется оплатить услуги (если применимо) и соблюдать правила сайта.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">3. Порядок заключения договора</h2>
          <p class="muted">
            Договор считается заключённым с момента регистрации на сайте или размещения первого 
            объявления. Акцептом оферты является совершение указанных действий.
          </p>
        </div>

        <div class="legal-section">
          <h2 class="title">4. Реквизиты</h2>
          <p class="muted">
            <strong>Наименование:</strong> ООО «Объявления»<br>
            <strong>Адрес:</strong> г. Москва, ул. Примерная, д. 1<br>
            <strong>Email:</strong> legal@avitoclone.ru
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

.link {
  color: var(--primary-2);
}
</style>