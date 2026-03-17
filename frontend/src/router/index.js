import { createRouter, createWebHistory } from 'vue-router'
import CatalogPage from '../views/CatalogPage.vue'
import ProductPage from '../views/ProductPage.vue'
import NewListingPage from '../views/NewListingPage.vue'
import NotFoundPage from '../views/NotFoundPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'catalog', component: CatalogPage },
    { path: '/product/:id', name: 'product', component: ProductPage, props: true },
    { path: '/new', name: 'new', component: NewListingPage },
    { path: '/:pathMatch(.*)*', name: 'not_found', component: NotFoundPage }
  ]
})

