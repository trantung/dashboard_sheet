<template>
    <!-- Mobile Sidebar Overlay -->
    <div 
        v-if="sidebarOpen" 
        class="mobile-overlay d-md-none"
        @click="$emit('close-sidebar')"
    ></div>

    <!-- Mobile Sidebar -->
    <div 
        class="mobile-sidebar d-md-none"
        :class="{ 'mobile-sidebar-open': sidebarOpen }"
    >
        <div class="mobile-sidebar-content">
            <!-- Mobile Sidebar Header -->
            <div class="mobile-sidebar-header">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 32px; height: 32px;">
                            <i class="bi bi-pause-fill text-white"></i>
                        </div>
                        <span class="fw-bold">Dashboard</span>
                    </div>
                    <button 
                        @click="$emit('close-sidebar')"
                        class="btn btn-sm btn-outline-secondary"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Items -->
            <div class="mobile-sidebar-body p-3">
                <nav class="nav flex-column">
                    <button
                        v-for="item in menuItems"
                        :key="item.tab"
                        @click="handleTabChange(item.tab)"
                        :class="['nav-link', 'btn', 'btn-link', 'text-start', 'd-flex', 'align-items-center', 'p-3', 'rounded', 'mb-2',
                            activeTab === item.tab ? 'active bg-success text-white' : 'text-dark']"
                    >
                        <i :class="['bi', item.icon, 'me-3', 'fs-5']"></i>
                        <span class="fw-medium">{{ item.label }}</span>
                        <i v-if="activeTab === item.tab" class="bi bi-check-circle ms-auto"></i>
                    </button>
                </nav>
            </div>

            <!-- Mobile Sidebar Footer -->
            <div class="mobile-sidebar-footer border-top p-3 mt-auto">
                <button
                    @click="$emit('logout')"
                    class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div class="d-none d-md-flex bg-light border-end flex-column align-items-center py-3" 
         style="width: 80px; min-width: 80px; max-width: 80px; min-height: 100vh;">
        <div class="d-flex flex-column align-items-center py-3">
            <!-- Logo -->
            <div class="mb-4 d-flex align-items-center justify-content-center bg-success rounded-circle"
                style="width: 32px; height: 32px;">
                <i class="bi bi-pause-fill text-white"></i>
            </div>

            <!-- Navigation Items -->
            <div class="d-flex flex-column gap-3">
                <button
                    v-for="item in menuItems"
                    :key="item.tab"
                    @click="$emit('change-tab', item.tab)"
                    :class="['btn', 'btn-sm', 'd-flex', 'flex-column', 'align-items-center', 'text-decoration-none', 'border-0',
                        activeTab === item.tab ? 'text-success bg-success bg-opacity-10' : 'text-muted bg-transparent']"
                    style="width: 60px;"
                >
                    <i :class="['bi', item.icon, 'fs-5', 'mb-1']"></i>
                    <small style="font-size: 10px;">{{ item.label }}</small>
                </button>
            </div>

            <!-- Logout Button -->
            <div class="mt-auto">
                <button
                    @click="$emit('logout')"
                    class="btn btn-sm d-flex flex-column align-items-center text-muted text-decoration-none border-0 bg-transparent"
                    style="width: 60px;"
                >
                    <i class="bi bi-box-arrow-right fs-5 mb-1"></i>
                    <small style="font-size: 10px;">Logout</small>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    activeTab: String,
    sidebarOpen: {
        type: Boolean,
        default: false
    }
})

const emits = defineEmits(['change-tab', 'logout', 'close-sidebar'])

const menuItems = [
    { tab: 'websites', label: 'Home', icon: 'bi-house' },
    { tab: 'files', label: 'Files', icon: 'bi-upload' },
    { tab: 'pricing', label: 'Pricing', icon: 'bi-currency-dollar' },
    { tab: 'profile', label: 'Profile', icon: 'bi-person' },
    { tab: 'workspaces', label: 'Workspaces', icon: 'bi-briefcase' },
    { tab: 'api', label: 'API', icon: 'bi-code-slash' },
]

const handleTabChange = (tab) => {
    emits('change-tab', tab)
    emits('close-sidebar')
}
</script>

<style scoped>
/* Mobile Sidebar Styles */
.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    animation: fadeIn 0.3s ease-out;
}

.mobile-sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background-color: white;
    z-index: 1050;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.mobile-sidebar-open {
    left: 0;
}

.mobile-sidebar-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.mobile-sidebar-header {
    flex-shrink: 0;
}

.mobile-sidebar-body {
    flex: 1;
    overflow-y: auto;
}

.mobile-sidebar-footer {
    flex-shrink: 0;
}

/* Navigation Styles */
.nav-link {
    border: none !important;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.nav-link:hover {
    background-color: #f8f9fa !important;
    transform: translateX(5px);
}

.nav-link.active {
    background-color: #28a745 !important;
    color: white !important;
    transform: translateX(5px);
}

.nav-link.active:hover {
    background-color: #218838 !important;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideInLeft {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(0);
    }
}

/* Responsive adjustments */
@media (max-width: 320px) {
    .mobile-sidebar {
        width: 100vw;
        left: -100vw;
    }
}

/* Desktop sidebar hover effects */
@media (min-width: 768px) {
    .btn:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }
}

/* Smooth scrolling for mobile sidebar */
.mobile-sidebar-body {
    scrollbar-width: thin;
    scrollbar-color: #28a745 #f1f1f1;
}

.mobile-sidebar-body::-webkit-scrollbar {
    width: 6px;
}

.mobile-sidebar-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.mobile-sidebar-body::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 3px;
}

.mobile-sidebar-body::-webkit-scrollbar-thumb:hover {
    background: #218838;
}

/* Focus states for accessibility */
.nav-link:focus {
    outline: 2px solid #28a745;
    outline-offset: 2px;
}

.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Loading state animation */
.nav-link.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Active indicator animation */
.nav-link.active .bi-check-circle {
    animation: bounceIn 0.3s ease-out;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}
</style>