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
import LoginPage from '../views/LoginPage.vue'
import RegisterPage from '../views/RegisterPage.vue'
import ProfilePage from '../views/ProfilePage.vue'
import AdminPage from '../views/AdminPage.vue'
import TicketsPage from '../views/TicketsPage.vue'
import ChatPage from '../views/ChatPage.vue'


export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: CatalogPage },

    { path: '/catalog', name: 'catalog', component: CatalogPage },
    { path: '/catalog/:category', name: 'catalog-category', component: CatalogPage, props: true },

    { path: '/product/:id', name: 'product', component: ProductPage, props: true },
    
    { path: '/new', name: 'new', component: NewListingPage },

    { path: '/login', name: 'login', component: LoginPage },
    { path: '/register', name: 'register', component: RegisterPage },
    { path: '/profile', name: 'profile', component: ProfilePage },
    { path: '/chat', name: 'chat', component: ChatPage, meta: { requiresAuth: true } },
    { path: '/admin', name: 'admin', component: AdminPage, meta: { requiresAdmin: true } },

    { path: '/help', name: 'help', component: HelpPage },
    { path: '/help/how-to-post', name: 'how-to-post', component: HowToPostPage },
    { path: '/help/safety', name: 'safety', component: SafetyPage },
    { path: '/help/contacts', name: 'contacts', component: ContactsPage },
    { path: '/support', name: 'support', component: SupportPage },

    { path: '/tickets', name: 'tickets', component: TicketsPage, meta: { requiresSupport: true } },

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

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('demoAuth')
  const isAuth = !!token
  
  if (to.meta.requiresAuth && !isAuth) {
    next({ path: '/login', query: { redirect: to.fullPath } })
    return
  }
  
  if (to.path === '/login' && isAuth) {
    const redirect = to.query.redirect || '/profile'
    next({ path: redirect })
    return
  }
  
  if (to.meta.requiresAdmin) {
    const userData = JSON.parse(localStorage.getItem('userProfile') || '{}')
    if (!userData.roles?.includes('admin')) {
      next({ path: '/', query: { error: 'access_denied' } })
      return
    }
  }
  
  next()
})
