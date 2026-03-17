<template>
  <div class="card">
    <div class="card__body" style="display: grid; gap: 10px">
      <div class="title">Страница не найдена</div>
      <div class="muted">Проверь адрес или вернись в каталог.</div>
      <RouterLink class="btn btn--primary" to="/">В каталог</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

