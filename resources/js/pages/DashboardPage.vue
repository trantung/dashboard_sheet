<template>
    <DashboardLayout v-model:activeTab="activeTab">
        <ProfileTab v-if="activeTab === 'profile'" />
        <WebsitesTab v-if="activeTab === 'websites'" />
        <FilesTab v-if="activeTab === 'files'" @change-tab="setActiveTab" />
        <APITab v-if="activeTab === 'api'" />
        <WorkspacesTab v-if="activeTab === 'workspaces'" />
        <PricingTab v-if="activeTab === 'pricing'" />
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import DashboardLayout from '@/layouts/DashboardLayout.vue'

import ProfileTab from '@/components/dashboard/ProfileTab.vue'
import WebsitesTab from '@/components/dashboard/WebsitesTab.vue'
import FilesTab from '@/components/dashboard/FilesTab.vue'
import APITab from '@/components/dashboard/APITab.vue'
import WorkspacesTab from '@/components/dashboard/WorkspacesTab.vue'
import PricingTab from '@/components/dashboard/PricingTab.vue'

const route = useRoute()
const router = useRouter()
const activeTab = ref('websites')

const setActiveTab = (tab) => {
  activeTab.value = tab
  router.push(`/dashboard/${tab}`)
}

onMounted(() => {
    activeTab.value = route.meta?.tab || 'websites'
})
</script>

<style scoped>
.modal {
    z-index: 1050;
}

.btn:focus {
    box-shadow: none;
}

.nav-item.active {
    color: #28a745 !important;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.input-group:focus-within .input-group-text {
    border-color: #28a745;
}

/* Mobile responsive styles */
@media (max-width: 767.98px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    /* Ensure content doesn't overlap with mobile bottom navigation */
    :deep(.container-fluid) {
        padding-bottom: 1rem;
    }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 991.98px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Desktop styles */
@media (min-width: 992px) {
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Smooth transitions for responsive changes */
* {
    transition: padding 0.3s ease, margin 0.3s ease;
}

/* Fix for mobile viewport height issues */
@media (max-width: 767.98px) {
    :deep(.min-vh-100) {
        min-height: 100vh;
        min-height: -webkit-fill-available;
    }
}

/* Ensure proper spacing on all screen sizes */
:deep(.mb-4) {
    margin-bottom: 1rem !important;
}

@media (min-width: 768px) {
    :deep(.mb-md-4) {
        margin-bottom: 1.5rem !important;
    }
}

/* Responsive text sizing */
@media (max-width: 575.98px) {
    :deep(h4) {
        font-size: 1.1rem;
    }

    :deep(h5) {
        font-size: 1rem;
    }

    :deep(.fs-1) {
        font-size: 2rem !important;
    }
}
</style>