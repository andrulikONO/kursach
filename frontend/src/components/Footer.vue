<template>
  <footer class="footer">
    <div class="container footer__inner">
      <div class="footer__section">
        <div class="footer__logo">
          <span class="brand__dot" aria-hidden="true" />
          <span>Объявления от реальных людей</span>
        </div>
        <p class="muted" style="margin-top: 12px; font-size: 14px; line-height: 1.5">
          Аналог Avito с базовым функционалом.
        </p>
      </div>

      <div class="footer__section">
        <h4 class="footer__title">Разделы</h4>
        <nav class="footer__nav">
          <RouterLink to="/help">Помощь</RouterLink>
        </nav>
      </div>

      <div class="footer__section">
        <h4 class="footer__title">Бизнесу</h4>
        <nav class="footer__nav">
          <RouterLink to="/ads">Реклама на сайте</RouterLink>
          <RouterLink to="/security">Безопасность</RouterLink>
          <RouterLink to="/for-business">Для бизнеса</RouterLink>
        </nav>
      </div>

      <div class="footer__section">
        <h4 class="footer__title">Документы</h4>
        <nav class="footer__nav">
          <RouterLink to="/privacy">Политика конфиденциальности</RouterLink>
          <RouterLink to="/terms">Условия использования</RouterLink>
          <RouterLink to="/offer">Оферта</RouterLink>
        </nav>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="container">
        <span class="muted" style="font-size: 13px">
          © {{ currentYear }} Объявления.
        </span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { RouterLink } from 'vue-router'

const currentYear = new Date().getFullYear()
</script>
<style scoped>
.footer {
  margin-top: auto;
  background: rgba(0, 0, 0, 0.3);
  border-top: 1px solid var(--border);
}

.footer__inner {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 32px;
  padding: 40px 16px;
}

.footer__logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 18px;
}

.footer__title {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
}

.footer__nav {
  display: grid;
  gap: 10px;
}

.footer__nav a {
  color: var(--muted);
  font-size: 14px;
  transition: color 0.2s;
}

.footer__nav a:hover {
  color: var(--text);
}

.footer__bottom {
  border-top: 1px solid var(--border);
  padding: 16px 0;
  text-align: center;
}

@media (max-width: 980px) {
  .footer__inner {
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }
}

@media (max-width: 640px) {
  .footer__inner {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  .footer__logo {
    font-size: 16px;
  }
}
</style>