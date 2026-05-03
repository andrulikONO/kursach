import { defineStore } from 'pinia'
import { fetchProductById, fetchProducts } from '../lib/api'

export const useProductStore = defineStore('products', {
  state: () => ({
    items: [],
    total: 0,
    page: 1,
    perPage: 25,
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
        this.total = typeof data?.total === 'number' ? data.total : Number(data?.total) || 0
        this.page = typeof data?.page === 'number' ? data.page : Number(data?.page) || 1
        this.perPage = typeof data?.perPage === 'number' ? data.perPage : Number(data?.perPage) || 25
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
