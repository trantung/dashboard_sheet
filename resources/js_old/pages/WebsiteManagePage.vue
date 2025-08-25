<template>
    <DashboardLayout>
        <WebsiteHeader 
            :websiteName="websiteName" 
            :linkGoogleSheet="linkGoogleSheet" 
            :linkWebsite="linkWebsite" 
            :websiteId="websiteId"
            @sync-success="handleSyncSuccess"
        />

        <div class="d-flex flex-grow-1">
            <WebsiteSidebar 
                class="d-none d-lg-flex"
                :activeTab="activeTab" 
                @change-tab="setActiveTab" 
                :tabs="baseTabs" 
                :websiteData="websiteData" 
            />

            <main class="flex-fill p-4 overflow-auto main-content-mobile-padding" style="background-color: #f8f9fa;">
                <DashboardTab v-if="activeTab === 'dashboard'" @change-tab="setActiveTab" />
                <InformationSheetTab v-if="activeTab === 'information'" :syncTrigger="syncTrigger" />
                <ContentSheetTab v-if="activeTab === 'content'" :syncTrigger="syncTrigger" />
                <CustomDomainTab v-if="activeTab === 'domain'" />
                <CustomCodeTab v-if="activeTab === 'code'" />
                <GeneralSettingsTab v-if="activeTab === 'settings'" />
                <IntegrationsTab v-if="activeTab === 'integrations'" @change-tab-dashboard="setActiveTabDashboard" />
                <NavbarTab v-if="activeTab === 'navbar'" />
                <PagesTab v-if="activeTab === 'pages'" />
                <EmailsTab v-if="activeTab === 'emails'" />
                <OrdersTab v-if="activeTab === 'orders'" />
                <FeedbacksTab v-if="activeTab === 'feedbacks'" />
                <WebhooksTab v-if="activeTab === 'webhooks'" />
                <SitemapTab v-if="activeTab === 'sitemap'" />
                <RSSTab v-if="activeTab === 'rss'" />
            </main>
        </div>

        <MobileBottomBar 
            :activeTab="activeTab" 
            @change-tab="setActiveTab" 
            :tabs="baseTabs"
            :websiteData="websiteData" 
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import { useAuthStore } from '../stores/auth.js'

import DashboardLayout from '@/layouts/DashboardLayout.vue'
import WebsiteSidebar from '../components/website/WebsiteSidebar.vue'
import WebsiteHeader from '../components/website/WebsiteHeader.vue'
import MobileBottomBar from '../components/website/MobileBottomBar.vue'

// Import components
import DashboardTab from '../components/website/DashboardTab.vue'
import InformationSheetTab from '../components/website/InformationSheetTab.vue'
import ContentSheetTab from '../components/website/ContentSheetTab.vue'
import CustomDomainTab from '../components/website/CustomDomainTab.vue'
import CustomCodeTab from '../components/website/CustomCodeTab.vue'
import GeneralSettingsTab from '../components/website/GeneralSettingsTab.vue'
import IntegrationsTab from '../components/website/IntegrationsTab.vue'
import NavbarTab from '../components/website/NavbarTab.vue'
import PagesTab from '../components/website/PagesTab.vue'
import EmailsTab from '../components/website/EmailsTab.vue'
import OrdersTab from '../components/website/OrdersTab.vue'
import FeedbacksTab from '../components/website/FeedbacksTab.vue'
import WebhooksTab from '../components/website/WebhooksTab.vue'
import SitemapTab from '../components/website/SitemapTab.vue'
import RSSTab from '../components/website/RSSTab.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const activeTab = ref('dashboard')
const websiteName = ref('')
const linkGoogleSheet = ref('')
const linkWebsite = ref('')
const websiteData = ref(null)
const isLoading = ref(true)
const error = ref(null)

const syncTrigger = ref(0)

let websiteId = route.params.id

// Định nghĩa các tab cơ bản ở đây
const baseTabs = [
    { key: 'dashboard', label: 'Dashboard', shortLabel: 'Dash', icon: 'bi bi-speedometer2' },
    { key: 'information', label: 'Information sheet', shortLabel: 'Info', icon: 'bi bi-info-circle' },
    { key: 'content', label: 'Content sheet', shortLabel: 'Content', icon: 'bi bi-table' },
    { key: 'domain', label: 'Custom domain', shortLabel: 'Domain', icon: 'bi bi-globe' },
    { key: 'code', label: 'Custom code', shortLabel: 'Code', icon: 'bi bi-code-slash' },
    { key: 'settings', label: 'General settings', shortLabel: 'Settings', icon: 'bi bi-gear' },
    { key: 'integrations', label: 'Integrations', shortLabel: 'Integrations', icon: 'bi bi-puzzle' },
    { key: 'navbar', label: 'Navbar', shortLabel: 'Navbar', icon: 'bi bi-menu-button-wide' },
    { key: 'pages', label: 'Pages', shortLabel: 'Pages', icon: 'bi bi-file-earmark' },
    { key: 'emails', label: 'Emails', shortLabel: 'Emails', icon: 'bi bi-envelope' },
    { key: 'orders', label: 'Orders', shortLabel: 'Orders', icon: 'bi bi-bag' },
    { key: 'feedbacks', label: 'Feedbacks', shortLabel: 'Feedbacks', icon: 'bi bi-chat-dots' },
    { key: 'webhooks', label: 'Webhooks', shortLabel: 'Webhooks', icon: 'bi bi-webhook' },
    { key: 'sitemap', label: 'Sitemap', shortLabel: 'Sitemap', icon: 'bi bi-diagram-3' },
    { key: 'rss', label: 'RSS', shortLabel: 'RSS', icon: 'bi bi-rss' }
];

const setActiveTab = (tab) => {
    activeTab.value = tab
    const newPath = `/website/${websiteId}/manage/${tab}`
    if (router.currentRoute.value.path !== newPath) {
        router.replace(newPath)
    }
}

const setActiveTabDashboard = (tab) => {
    activeTab.value = tab
    const newPath = `/dashboard/${tab}`
    if (router.currentRoute.value.path !== newPath) {
        router.replace(newPath)
    }
}

const handleLogout = async () => {
    await authStore.logout()
    router.push('/')
}

const fetchWebsiteInfo = async (id) => {
    if (!id) {
        error.value = 'Website ID is required'
        isLoading.value = false
        return
    }

    try {
        isLoading.value = true
        error.value = null
        
        const res = await axios.get(ziggyRoute('api.sites.show', { id }))

        if (res.data) {
            websiteData.value = res.data
            websiteName.value = res.data.name || 'Unnamed Website'
            linkGoogleSheet.value = res.data.google_sheet || ''
            linkWebsite.value = 'https://' + res.data.domain_name || ''
        } else {
            error.value = 'No website data found'
        }
    } catch (err) {
        console.error('Failed to fetch website info:', err)
        
        if (err.response?.status === 404) {
            error.value = 'Website not found'
            router.push('/dashboard/websites')
        } else if (err.response?.status === 403) {
            error.value = 'Access denied'
        } else {
            error.value = 'Failed to load website data'
        }
        
        websiteName.value = 'Unknown Website'
        linkGoogleSheet.value = ''
        linkWebsite.value = ''
    } finally {
        isLoading.value = false
    }
}

const handleSyncSuccess = () => {
    syncTrigger.value++
}

watch(() => route.params.id, (newId) => {
    if (newId && newId !== websiteId) {
        websiteId = newId
        fetchWebsiteInfo(newId)
    }
})

watch(() => route.fullPath, () => {
    const tab = route.path.split('/').pop()
    if (tab && tab !== activeTab.value) {
        activeTab.value = tab
    }
}, { immediate: true })

onMounted(() => {
    if (websiteId) {
        fetchWebsiteInfo(websiteId)
    }
    
    if (route.params.tab) {
        activeTab.value = route.params.tab
    }
})
</script>

<style scoped>
/* Mobile-first responsive design */
@media (max-width: 575.98px) {
    main {
        padding: 0.75rem !important;
        min-height: calc(100vh - 120px) !important;
    }
}

/* Tablet optimizations */
@media (min-width: 576px) and (max-width: 991.98px) {
    main {
        padding: 1rem !important;
        min-height: calc(100vh - 130px) !important;
    }
}

/* Desktop optimizations */
@media (min-width: 992px) {
    main {
        min-height: calc(100vh - 140px) !important;
    }
}

/* Loading state styling */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Smooth transitions */
.flex-grow-1 {
    transition: all 0.3s ease;
}

/* Error state styling */
.error-state {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 0.375rem;
    padding: 1rem;
    margin: 1rem 0;
}

/* Responsive order adjustments */
@media (max-width: 991.98px) {
    .order-0 {
        order: 0;
    }
    
    .order-1 {
        order: 1;
    }
}

@media (min-width: 992px) {
    .order-lg-0 {
        order: 0;
    }
    
    .order-lg-1 {
        order: 1;
    }
}

/* Ensure proper scrolling on mobile */
@media (max-width: 991.98px) {
    .overflow-auto {
        -webkit-overflow-scrolling: touch;
    }
    
    .main-content-mobile-padding {
        padding-bottom: 70px !important;
    }
}

/* Focus management for accessibility */
main:focus {
    outline: none;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    main {
        border: 1px solid;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .flex-grow-1 {
        transition: none;
    }
}
</style>