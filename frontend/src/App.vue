<template>
  <div class="topbar">
    <div class="container topbar__inner">
      <RouterLink class="brand" to="/">
        <span class="brand__dot" aria-hidden="true" />
        <span>Объявления</span>
      </RouterLink>

      <nav class="nav">
        <RouterLink class="btn" to="/">Каталог</RouterLink>
        <RouterLink class="btn btn--primary" to="/new">Подать объявление</RouterLink>
      </nav>
    </div>
  </div>

  <main class="container" style="padding: 18px 16px 28px">
    <RouterView />
  </main>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

