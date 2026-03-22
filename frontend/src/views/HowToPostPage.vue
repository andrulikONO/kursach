<template>
  <div class="info-page">
    <RouterLink to="/help" class="btn btn--secondary" style="margin-bottom: 20px; display: inline-flex">
      ← Назад к помощи
    </RouterLink>

    <div class="card">
      <div class="card__body">
        <h1 class="title" style="margin: 0 0 24px 0">Как разместить объявление</h1>

        <div class="steps">
          <div class="step">
            <div class="step__number">1</div>
            <div class="step__content">
              <h3 class="title">Нажмите «Подать объявление»</h3>
              <p class="muted">Кнопка находится в верхней панели сайта справа</p>
            </div>
          </div>

          <div class="step">
            <div class="step__number">2</div>
            <div class="step__content">
              <h3 class="title">Заполните форму</h3>
              <p class="muted">Укажите заголовок, цену, категорию, город и описание товара</p>
            </div>
          </div>

          <div class="step">
            <div class="step__number">3</div>
            <div class="step__content">
              <h3 class="title">Добавьте фотографии</h3>
              <p class="muted">Чем больше фото, тем выше интерес покупателей (в разработке)</p>
            </div>
          </div>

          <div class="step">
            <div class="step__number">4</div>
            <div class="step__content">
              <h3 class="title">Опубликуйте</h3>
              <p class="muted">Нажмите кнопку «Опубликовать» — объявление появится в каталоге</p>
            </div>
          </div>
        </div>

        <div class="info-box card" style="margin-top: 24px; box-shadow: none">
          <div class="card__body">
            <strong>💡 Совет:</strong>
            <span class="muted"> Указывайте честную цену и подробное описание — это ускорит продажу!</span>
          </div>
        </div>

        <RouterLink to="/new" class="btn btn--primary" style="margin-top: 24px; display: inline-flex">
          Разместить объявление сейчас
        </RouterLink>
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

.steps {
  display: grid;
  gap: 20px;
}

.step {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.step__number {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary), var(--primary-2));
  border-radius: 50%;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.step__content {
  flex: 1;
}

.step__content h3 {
  margin: 0 0 8px 0;
}

.step__content p {
  margin: 0;
}

.info-box {
  background: rgba(124, 92, 255, 0.1);
  border: 1px solid rgba(124, 92, 255, 0.3);
}
</style>