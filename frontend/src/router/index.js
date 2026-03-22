import { createRouter, createWebHistory } from 'vue-router'
import CatalogPage from '../views/CatalogPage.vue'
import ProductPage from '../views/ProductPage.vue'
import NewListingPage from '../views/NewListingPage.vue'
import NotFoundPage from '../views/NotFoundPage.vue'
import HelpPage from '../views/HelpPage.vue'
import AdsPage from '../views/AdsPage.vue'
import SecurityPage from '../views/SecurityPage.vue'
import ForBusinessPage from '../views/ForBusinessPage.vue'
import HowToPostPage from '../views/HowToPostPage.vue'
import SafetyPage from '../views/SafetyPage.vue'
import ContactsPage from '../views/ContactsPage.vue'
import PrivacyPage from '../views/PrivacyPage.vue'
import TermsPage from '../views/TermsPage.vue'
import OfferPage from '../views/OfferPage.vue'
import SupportPage from '../views/SupportPage.vue'


export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: CatalogPage },

    { path: '/catalog', name: 'catalog', component: CatalogPage },
    { path: '/catalog/:category', name: 'catalog-category', component: CatalogPage, props: true },

    { path: '/product/:id', name: 'product', component: ProductPage, props: true },
    
    { path: '/new', name: 'new', component: NewListingPage },
    
    { path: '/help', name: 'help', component: HelpPage },
    { path: '/help/how-to-post', name: 'how-to-post', component: HowToPostPage },
    { path: '/help/safety', name: 'safety', component: SafetyPage },
    { path: '/help/contacts', name: 'contacts', component: ContactsPage },
    { path: '/support', name: 'support', component: SupportPage },

    { path: '/ads', name: 'ads', component: AdsPage },
    { path: '/security', name: 'security', component: SecurityPage },
    { path: '/for-business', name: 'for-business', component: ForBusinessPage },
    
    { path: '/privacy', name: 'privacy', component: PrivacyPage },
    { path: '/terms', name: 'terms', component: TermsPage },
    { path: '/offer', name: 'offer', component: OfferPage },
    
    { path: '/:pathMatch(.*)', name: 'not_found', component: NotFoundPage }
  ],
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  }
})