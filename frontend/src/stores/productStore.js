import { defineStore } from 'pinia'
import { fetchProductById, fetchProducts } from '../lib/api'

export const useProductStore = defineStore('products', {
  state: () => ({
    items: [],
    loading: false,
    error: null,
    selected: null,
    selectedLoading: false,
    selectedError: null
  }),
  actions: {
    async loadList(filters) {
      this.loading = true
      this.error = null
      try {
        const data = await fetchProducts(filters)
        this.items = data?.items || []
      } catch (e) {
        this.error = e?.message || String(e)
      } finally {
        this.loading = false
      }
    },
    async loadOne(id) {
      this.selectedLoading = true
      this.selectedError = null
      this.selected = null
      try {
        this.selected = await fetchProductById(id)
      } catch (e) {
        this.selectedError = e?.message || String(e)
      } finally {
        this.selectedLoading = false
      }
    }
  }
})
