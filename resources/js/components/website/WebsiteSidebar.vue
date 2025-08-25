<template>
    <div class="bg-white border-end website-sidebar">
        <div class="p-2 p-md-3">
            <nav class="nav flex-column">
                <button 
                    v-for="item in dynamicTabs" 
                    :key="item.key" 
                    @click="$emit('change-tab', item.key)" 
                    :class="[
                        'nav-link d-flex align-items-center text-start border-0 bg-transparent w-100 py-2 px-2 px-md-3 rounded mb-1',
                        activeTab === item.key ? 'bg-primary bg-opacity-10 text-primary' : 'text-muted'
                    ]"
                >
                    <i :class="item.icon + ' me-2 me-md-3 flex-shrink-0'"></i>
                    <span class="text-truncate">{{ item.label }}</span>
                </button>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activeTab: String,
    websiteData: Object,
    tabs: Array // Nhận danh sách tab từ component cha
})
const emit = defineEmits(['change-tab'])

const dynamicTabs = computed(() => {
    let updatedTabs = [...props.tabs];

    // Cập nhật nhãn cho 'information' tab
    const infoTab = updatedTabs.find(tab => tab.key === 'information');
    if (infoTab && props.websiteData && props.websiteData.information && props.websiteData.information.sheet_name) {
        infoTab.label = `${props.websiteData.information.sheet_name} sheet`;
    }

    // Cập nhật nhãn cho 'content' tab
    const contentTab = updatedTabs.find(tab => tab.key === 'content');
    if (contentTab && props.websiteData && props.websiteData.content && props.websiteData.content.sheet_name) {
        contentTab.label = `${props.websiteData.content.sheet_name} sheet`;
    }

    // Lọc menu dựa trên websiteData.type
    if (props.websiteData && props.websiteData.type === 1) {
        // Nếu type = 1, loại bỏ menu 'orders'
        updatedTabs = updatedTabs.filter(tab => tab.key !== 'orders');
    }
    // Nếu type = 2 hoặc không có type, hiển thị tất cả menu (mặc định)

    return updatedTabs;
});
</script>

<style scoped>
.website-sidebar {
    width: 240px;
    min-width: 240px;
    min-height: calc(100vh - 80px);
    flex-shrink: 0;
}

/* Mobile responsive */
@media (max-width: 767.98px) {
    .website-sidebar {
        width: 100%;
        min-width: 100%;
        min-height: auto;
        border-bottom: 1px solid #dee2e6;
        border-right: none;
    }
    
    .nav {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    
    .nav-link {
        white-space: nowrap;
        min-width: auto;
        flex-shrink: 0;
        margin-right: 0.5rem;
        margin-bottom: 0 !important;
    }
    
    .nav-link span {
        font-size: 0.875rem;
    }
    
    .nav-link i {
        font-size: 0.875rem;
    }
}

/* Tablet responsive */
@media (min-width: 768px) and (max-width: 991.98px) {
    .website-sidebar {
        width: 260px;
        min-width: 260px;
    }
}

/* Desktop responsive */
@media (min-width: 992px) {
    .website-sidebar {
        width: 280px;
        min-width: 280px;
    }
}

/* Hover effects */
.nav-link {
    transition: all 0.2s ease;
}

.nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
    transform: translateX(2px);
}

.nav-link.bg-primary {
    transform: translateX(4px);
}

/* Focus states for accessibility */
.nav-link:focus {
    outline: 2px solid rgba(var(--bs-primary-rgb), 0.5);
    outline-offset: 2px;
}

/* Scrollbar styling for mobile horizontal scroll */
@media (max-width: 767.98px) {
    .nav::-webkit-scrollbar {
        height: 4px;
    }
    
    .nav::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .nav::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }
    
    .nav::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
}
</style>