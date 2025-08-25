<template>
    <header class="border-bottom px-4 py-3">
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex flex-wrap flex-xl-nowrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center me-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <router-link to="/dashboard/websites" class="text-decoration-none text-muted">My websites</router-link>
                        </li>
                        <li class="breadcrumb-item active text-dark">{{ websiteName }}</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex flex-wrap flex-sm-nowrap align-items-center gap-2">
                <span class="text-muted small d-none d-lg-block">Synced: {{ lastSyncedText }}</span>
                
                <a 
                    :href="linkGoogleSheet" 
                    target="_blank" 
                    class="btn btn-success btn-sm text-decoration-none"
                    :class="{ 'disabled': !linkGoogleSheet }"
                >
                    <i class="bi bi-pencil"></i>
                    <span class="d-none d-md-inline ms-1">Edit Sheets</span>
                </a>
                
                <button class="btn btn-outline-secondary btn-sm" @click="syncSheets" :disabled="isSyncing">
                    <span v-if="isSyncing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <i class="bi bi-arrow-clockwise" :class="{ 'me-1': isSyncing }"></i>
                    <span v-if="!isSyncing" class="d-none d-md-inline">Sync</span>
                    <span v-else>Syncing...</span>
                </button>
                
                <a :href="linkWebsite" target="_blank" class="btn btn-outline-success btn-sm" :class="{ 'disabled': !linkWebsite }">
                    <span class="d-none d-md-inline">View website</span> 
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted, computed, defineEmits } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue'

const route = useRoute()
const notificationAlert = ref(null)

const props = defineProps({
    websiteName: {
        type: String,
        default: 'Loading...'
    },
    linkGoogleSheet: {
        type: String,
        default: ''
    },
    linkWebsite: {
        type: String,
        default: ''
    },
    websiteId: {
        type: [String, Number],
        required: true
    }
})

const emit = defineEmits(['sync-success'])

const isSyncing = ref(false)
const lastSyncedAt = ref(null)

const lastSyncedText = computed(() => {
    if (!lastSyncedAt.value) {
        return 'Never';
    }
    const now = new Date();
    const lastSyncDate = new Date(lastSyncedAt.value);
    const diffMinutes = Math.round((now - lastSyncDate) / (1000 * 60));

    if (diffMinutes < 1) {
        return 'just now';
    } else if (diffMinutes < 60) {
        return `${diffMinutes} minutes ago`;
    } else if (diffMinutes < 24 * 60) {
        const diffHours = Math.round(diffMinutes / 60);
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else {
        const diffDays = Math.round(diffMinutes / (60 * 24));
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }
});

const syncSheets = async () => {
    if (!props.websiteId) {
        notificationAlert.value?.showError('Website ID is missing. Cannot sync sheets.');
        return;
    }

    isSyncing.value = true;

    try {
        const response = await axios.post(ziggyRoute('api.sites.sync', { site: props.websiteId }));

        if (response.data.success) {
            notificationAlert.value?.showSuccess('Sheets synced successfully!');
            emit('sync-success')
            lastSyncedAt.value = new Date();
        } else {
            notificationAlert.value?.showWarning(response.data.message || 'Failed to sync sheets.');
        }
    } catch (error) {
        console.error('Failed to sync sheets:', error);
        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to sync sheets for this website.', 'Access Denied');
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found.', 'Not Found');
        } else {
            notificationAlert.value?.showError('An error occurred during sync. Please try again.', 'Sync Error');
        }
    } finally {
        isSyncing.value = false;
    }
};

const viewWebsite = (website) => {
    // Thêm logic kiểm tra xem linkWebsite có phải là URL đầy đủ không
    // Hoặc giả định linkWebsite là một đường dẫn tương đối
    if (website && (website.startsWith('http://') || website.startsWith('https://'))) {
        window.open(website, '_blank');
    } else if (website) {
        // Xử lý trường hợp không có http/https
        window.open(`http://${website}`, '_blank');
    } else {
        notificationAlert.value?.showWarning('Website link is not available.');
    }
};

onMounted(() => {
    // Có thể fetch thời gian đồng bộ ban đầu từ API tại đây
});
</script>