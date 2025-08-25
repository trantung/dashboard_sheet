<template>
  <div class="min-vh-100 d-flex">
    <Sidebar 
      :activeTab="currentTab" 
      :sidebarOpen="sidebarOpen"
      @change-tab="setActiveTab" 
      @logout="handleLogout"
      @close-sidebar="closeSidebar" 
    />

    <div class="flex-fill d-flex flex-column overflow-hidden">
      <Header 
        :sidebarOpen="sidebarOpen"
        @show-feedback="showFeedbackModal = true" 
        @change-tab="setActiveTab"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Main Content -->
      <main class="p-2 p-md-4" 
            :class="{ 'sidebar-open': sidebarOpen }"
            style="background-color: #f8f9fa; min-height: calc(100vh - 80px);">
        <slot />
      </main>
    </div>

    <FeedbackModal
      v-if="showFeedbackModal"
      @close="showFeedbackModal = false"
      @submit="submitFeedback"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import Sidebar from '@/components/Sidebar.vue'
import Header from '@/components/Header.vue'
import FeedbackModal from '@/components/FeedbackModal.vue'

const props = defineProps({
  activeTab: String,
})
const emit = defineEmits(['update:activeTab'])

const showFeedbackModal = ref(false)
const sidebarOpen = ref(false)
const router = useRouter()
const authStore = useAuthStore()

const currentTab = computed({
  get: () => props.activeTab,
  set: (val) => emit('update:activeTab', val),
})

const setActiveTab = (tab) => {
  currentTab.value = tab
  router.push(`/dashboard/${tab}`)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}

const submitFeedback = (text) => {
  console.log('Feedback submitted:', text)
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

// Close sidebar when clicking outside on mobile
const handleClickOutside = (event) => {
  if (sidebarOpen.value && window.innerWidth < 768) {
    const sidebar = document.querySelector('.mobile-sidebar')
    const header = document.querySelector('header')
    
    if (sidebar && !sidebar.contains(event.target) && !header.contains(event.target)) {
      closeSidebar()
    }
  }
}

// Close sidebar on escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && sidebarOpen.value) {
    closeSidebar()
  }
}

// Close sidebar when screen size changes to desktop
const handleResize = () => {
  if (window.innerWidth >= 768 && sidebarOpen.value) {
    closeSidebar()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscapeKey)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
  window.removeEventListener('resize', handleResize)
})

// Prevent body scroll when sidebar is open on mobile
watch(sidebarOpen, (isOpen) => {
  if (window.innerWidth < 768) {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }
})
</script>

<style scoped>
/* Prevent content shift when sidebar opens */
.sidebar-open {
  transition: transform 0.3s ease;
}

@media (max-width: 767.98px) {
  .sidebar-open {
    transform: translateX(0);
  }
}

/* Smooth transitions */
.flex-fill {
  transition: all 0.3s ease;
}

/* Focus trap for accessibility */
.min-vh-100:focus-within .mobile-sidebar {
  outline: none;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .mobile-sidebar {
    border: 2px solid;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .mobile-sidebar,
  .mobile-overlay,
  .sidebar-open {
    transition: none;
  }
}
</style>