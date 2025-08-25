<template>
    <div class="fixed-bottom d-lg-none bg-white border-top shadow-lg-top overflow-auto">
        <nav class="nav nav-pills flex-nowrap text-nowrap" style="width: max-content;">
            <button 
                v-for="item in dynamicTabs" 
                :key="item.key" 
                @click="$emit('change-tab', item.key)" 
                :class="[
                    'nav-link d-flex flex-column align-items-center justify-content-center rounded-0 py-2 px-3',
                    activeTab === item.key ? 'text-primary' : 'text-muted'
                ]"
            >
                <i :class="item.icon + ' fs-5 mb-1'"></i>
                <span class="small">{{ item.shortLabel }}</span>
            </button>
        </nav>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activeTab: String,
    websiteData: Object,
    tabs: Array // Nhận toàn bộ danh sách tab
});
const emit = defineEmits(['change-tab']);

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
.shadow-lg-top {
    box-shadow: 0 -0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Tối ưu hóa cho các tab trong bottom bar */
.nav-pills {
    display: flex;
    white-space: nowrap;
}

.nav-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0.5rem;
    min-width: 80px; /* Đảm bảo đủ rộng cho mỗi tab */
}
</style>