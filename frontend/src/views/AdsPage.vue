<template>
  <div class="info-page">
    <RouterLink to="/" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← На главную
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Реклама на сайте</h1>
        <p class="muted" style="margin-bottom: 32px">
          Продвигайте свои объявления и привлекайте больше покупателей
        </p>

        <div class="ads-grid">
          <div class="ad-card card">
            <div class="ad-card__header">
              <span class="ad-badge">Популярное</span>
              <h3 class="title">Поднятие в топ</h3>
            </div>
            <div class="ad-card__price">от 99 ₽</div>
            <ul class="ad-features">
              <li>✓ Объявление вверху списка</li>
              <li>✓ До 5 раз больше просмотров</li>
              <li>✓ Действует 7 дней</li>
              <li>✓ Автоматическое продление</li>
            </ul>
            <button class="btn btn--primary" style="width: 100%; margin-top: 16px">
              Заказать
            </button>
          </div>

          <div class="ad-card card featured">
            <div class="ad-card__header">
              <span class="ad-badge ad-badge--hot">Хит</span>
              <h3 class="title">VIP-объявление</h3>
            </div>
            <div class="ad-card__price">от 299 ₽</div>
            <ul class="ad-features">
              <li>✓ Выделение цветом</li>
              <li>✓ В топe категории</li>
              <li>✓ До 10 раз больше просмотров</li>
              <li>✓ Значок VIP</li>
              <li>✓ Действует 14 дней</li>
            </ul>
            <button class="btn btn--primary" style="width: 100%; margin-top: 16px">
              Заказать
            </button>
          </div>

          <div class="ad-card card">
            <div class="ad-card__header">
              <span class="ad-badge">Для профи</span>
              <h3 class="title">XL-объявление</h3>
            </div>
            <div class="ad-card__price">от 199 ₽</div>
            <ul class="ad-features">
              <li>✓ Увеличенная карточка</li>
              <li>✓ Больше фото в превью</li>
              <li>✓ До 3 раз больше кликов</li>
              <li>✓ Действует 10 дней</li>
            </ul>
            <button class="btn btn--primary" style="width: 100%; margin-top: 16px">
              Заказать
            </button>
          </div>
        </div>

        <div class="info-box card" style="margin-top: 32px; box-shadow: none">
          <div class="card__body">
            <strong>💡 Совет:</strong>
            <span class="muted"> Продвижение работает лучше всего в сочетании с качественными фото и подробным описанием.</span>
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

.ads-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.ad-card {
  padding: 24px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.ad-card.featured {
  border-color: var(--primary);
  background: linear-gradient(135deg, rgba(124, 92, 255, 0.08), rgba(77, 211, 255, 0.05));
}

.ad-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.ad-badge {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  background: rgba(124, 92, 255, 0.2);
  color: var(--primary-2);
}

.ad-badge--hot {
  background: rgba(255, 77, 109, 0.2);
  color: var(--danger);
}

.ad-card__price {
  font-size: 28px;
  font-weight: 800;
  color: var(--primary-2);
  margin-bottom: 16px;
}

.ad-features {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 10px;
  flex: 1;
}

.ad-features li {
  color: var(--muted);
  font-size: 14px;
}

.info-box {
  background: rgba(124, 92, 255, 0.1);
  border: 1px solid rgba(124, 92, 255, 0.3);
}
</style>