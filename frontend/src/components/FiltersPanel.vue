<template>
  <div class="card">
    <div class="card__body" style="display: grid; gap: 12px">
      <div>
        <div class="title">Фильтры</div>
        <div class="muted" style="font-size: 13px">Поиск, покупка/продажа, цена</div>
      </div>

      <label style="display: grid; gap: 6px">
        <span class="muted">Поиск</span>
        <input class="input" :value="model.q" @input="set('q', $event.target.value)" placeholder="например: iPhone, кроссовки..." />
      </label>

      <ListingKindSelect :model-value="model.listingKind" @update:modelValue="set('listingKind', $event)" />

      <div v-if="showCategoryFilter">
        <CategorySelect :model-value="model.category" @update:modelValue="set('category', $event)" />
      </div>

      <div class="row">
        <label style="display: grid; gap: 6px">
          <span class="muted">Цена от</span>
          <input class="input" inputmode="numeric" :value="model.minPrice" @input="set('minPrice', $event.target.value)" placeholder="0" />
        </label>

        <label style="display: grid; gap: 6px">
          <span class="muted">Цена до</span>
          <input class="input" inputmode="numeric" :value="model.maxPrice" @input="set('maxPrice', $event.target.value)" placeholder="100000" />
        </label>
      </div>

      <button class="btn btn--primary" type="button" @click="$emit('apply', normalized)">Применить</button>
      <button class="btn" type="button" @click="reset">Сброс</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import CategorySelect from './CategorySelect.vue'
import ListingKindSelect from './ListingKindSelect.vue'

const props = defineProps({
  model: { type: Object, required: true },
  showCategoryFilter: { type: Boolean, default: true }
})
const emit = defineEmits(['update:model', 'apply'])

function set(key, value) {
  emit('update:model', { ...props.model, [key]: value })
}

function reset() {
  const nextModel = { q: '', category: '', listingKind: '', minPrice: '', maxPrice: '' }
  emit('update:model', nextModel)
  emit('apply', nextModel)
}

const normalized = computed(() => ({
  q: (props.model.q || '').trim(),
  category: props.model.category || '',
  listingKind: props.model.listingKind || '',
  minPrice: props.model.minPrice === '' ? '' : Number(props.model.minPrice),
  maxPrice: props.model.maxPrice === '' ? '' : Number(props.model.maxPrice)
}))
</script>
