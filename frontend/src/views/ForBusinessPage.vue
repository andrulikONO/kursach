<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 12px 0">Для бизнеса</h1>
        <p class="muted" style="margin-bottom: 32px">
          Решения для компаний и предпринимателей
        </p>

        <div class="business-hero" style="text-align: center; padding: 40px 20px; background: linear-gradient(135deg, rgba(124, 92, 255, 0.15), rgba(77, 211, 255, 0.1)); border-radius: var(--radius); margin-bottom: 32px">
          <div style="font-size: 48px; margin-bottom: 16px">🏢</div>
          <h2 class="title" style="margin: 0 0 12px 0">Работайте с нами на постоянной основе</h2>
          <p class="muted" style="max-width: 600px; margin: 0 auto 20px">
            Специальные условия для магазинов, сервисов и агентств. 
            Управление объявлениями через API и личный кабинет.
          </p>
          <RouterLink to="/help/contacts" class="btn btn--primary">
            Связаться с менеджером
          </RouterLink>
        </div>

        <div class="business-features">
          <div class="biz-card">
            <div class="biz-icon">📦</div>
            <h3 class="title">Массовая загрузка</h3>
            <p class="muted">Импорт товаров через XML/CSV. Обновление цен и остатков автоматически.</p>
          </div>

          <div class="biz-card">
            <div class="biz-icon">🔗</div>
            <h3 class="title">API для интеграции</h3>
            <p class="muted">REST API для управления объявлениями из вашей CRM или 1С.</p>
          </div>

          <div class="biz-card">
            <div class="biz-icon">📊</div>
            <h3 class="title">Статистика и аналитика</h3>
            <p class="muted">Подробные отчёты по просмотрам, кликам и конверсиям.</p>
          </div>

          <div class="biz-card">
            <div class="biz-icon">💼</div>
            <h3 class="title">Персональный менеджер</h3>
            <p class="muted">Поддержка и консультации для крупных партнёров.</p>
          </div>

          <div class="biz-card">
            <div class="biz-icon">🏷️</div>
            <h3 class="title">Скидки на продвижение</h3>
            <p class="muted">Специальные тарифы на рекламу для постоянных клиентов.</p>
          </div>

          <div class="biz-card">
            <div class="biz-icon">✅</div>
            <h3 class="title">Верификация компании</h3>
            <p class="muted">Значок доверия для юридических лиц повышает конверсию.</p>
          </div>
        </div>

        <div class="biz-pricing card" style="margin-top: 32px; box-shadow: none">
          <div class="card__body">
            <h3 class="title" style="margin: 0 0 20px 0">Тарифы для бизнеса</h3>
            <div class="pricing-grid">
              <div class="pricing-card">
                <div class="pricing-name">Старт</div>
                <div class="pricing-price">Бесплатно</div>
                <ul class="pricing-features">
                  <li>До 10 объявлений</li>
                  <li>Базовая статистика</li>
                  <li>Email поддержка</li>
                </ul>
              </div>
              <div class="pricing-card popular">
                <div class="pricing-badge">Популярный</div>
                <div class="pricing-name">Бизнес</div>
                <div class="pricing-price">990 ₽/мес</div>
                <ul class="pricing-features">
                  <li>До 100 объявлений</li>
                  <li>Расширенная статистика</li>
                  <li>Приоритетная поддержка</li>
                  <li>API доступ</li>
                </ul>
              </div>
              <div class="pricing-card">
                <div class="pricing-name">Премиум</div>
                <div class="pricing-price">Индивидуально</div>
                <ul class="pricing-features">
                  <li>Безлимитные объявления</li>
                  <li>Персональный менеджер</li>
                  <li>Интеграция с CRM</li>
                  <li>Скидки на рекламу</li>
                </ul>
              </div>
            </div>
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
  max-width: 1000px;
}

.business-features {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.biz-card {
  padding: 20px;
  background: var(--card-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  text-align: center;
}

.biz-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.biz-card h3 {
  margin: 0 0 8px 0;
  font-size: 16px;
}

.biz-card p {
  margin: 0;
  font-size: 14px;
}

.biz-pricing {
  background: rgba(124, 92, 255, 0.1);
  border: 1px solid rgba(124, 92, 255, 0.3);
}

.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
}

.pricing-card {
  padding: 24px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  position: relative;
}

.pricing-card.popular {
  border-color: var(--primary);
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.15), rgba(77, 211, 255, 0.1));
}

.pricing-badge {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 12px;
  background: var(--primary);
  border-radius: 999px;
  font-size: 12px;
  color: white;
}

.pricing-name {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
}

.pricing-price {
  font-size: 24px;
  font-weight: 800;
  color: var(--primary-2);
  margin-bottom: 16px;
}

.pricing-features {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 8px;
}

.pricing-features li {
  color: var(--muted);
  font-size: 14px;
}

@media (max-width: 640px) {
  .pricing-grid {
    grid-template-columns: 1fr;
  }
}
</style>