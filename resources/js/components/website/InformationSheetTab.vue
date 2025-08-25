<template>
    <div>
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Information sheet</h4>
                <p class="text-muted mb-0">Information sheet mapped from your Google Sheets</p>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading information sheet...</p>
        </div>

        <div v-else-if="informationData.length > 0" class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-3 py-3 text-muted small bg-primary/10">#</th>
                                <th 
                                    v-for="(header, headerIndex) in sheetHeaders" 
                                    :key="header" 
                                    class="px-3 py-3 text-muted small"
                                    :class="{ 'bg-primary/10': headerIndex === 0 }"
                                >
                                    {{ header.toUpperCase() }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in informationData" :key="index">
                                <td class="px-3 py-3 bg-primary/10">{{ index + 1 }}</td>
                                <td 
                                    v-for="(header, headerIndex) in sheetHeaders" 
                                    :key="headerIndex" 
                                    class="px-3 py-3"
                                    :class="{ 
                                        'fw-medium': header === 'Property', 
                                        'bg-primary/10': headerIndex === 0 // Apply to the first dynamic data column
                                    }"
                                >
                                    <span v-if="isUrl(item[header])">
                                        <a :href="item[header]" target="_blank" class="text-decoration-none">
                                            {{ item[header] }}
                                        </a>
                                    </span>
                                    <span v-else>{{ item[header] }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-else class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-info-circle fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">No Information Sheet Data</h5>
                <p class="text-muted">No information sheet data found for this website</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue'

const props = defineProps({
    syncTrigger: Number, // Define the new prop
})

const route = useRoute()
const notificationAlert = ref(null)

const loading = ref(true)
const informationData = ref([])
const sheetHeaders = ref([])

const isUrl = (string) => {
    try {
        new URL(string)
        return true
    } catch {
        return false
    }
}

const fetchWebsiteInfo = async (id) => {
    try {
        loading.value = true
        // const response = await axios.get(`/api/sites/${id}`)
        const response = await axios.get(ziggyRoute('api.sites.show', { id }))

        if (response.data && response.data.sheets) {
            let infoSheet = null;

            // Ưu tiên tìm sheet bằng sheet_id từ trường 'information' nếu có
            if (response.data.information && typeof response.data.information.sheet_id !== 'undefined') {
                const infoSheetId = response.data.information.sheet_id;
                infoSheet = response.data.sheets.find(sheet => sheet.sheet_id === infoSheetId);
            }

            // Nếu không tìm thấy bằng sheet_id hoặc trường 'information' không có, fallback tìm bằng sheet_name
            if (!infoSheet) {
                infoSheet = response.data.sheets.find(sheet => sheet.sheet_name === 'Information');
            }
            
            if (infoSheet && infoSheet.sheet_data) {
                informationData.value = infoSheet.sheet_data;
                if (infoSheet.sheet_headers) {
                    sheetHeaders.value = infoSheet.sheet_headers;
                }
                notificationAlert.value?.showSuccess('Information sheet loaded successfully');
            } else {
                notificationAlert.value?.showWarning('No complete information sheet data found.');
            }
        } else {
            notificationAlert.value?.showWarning('No website data or sheets found.');
        }
    } catch (error) {
        console.error('Failed to fetch website info:', error)

        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to access this website', 'Access Denied')
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found', 'Not Found')
        } else {
            notificationAlert.value?.showError('Failed to load information sheet', 'Loading Error')
        }
    } finally {
        loading.value = false
    }
}

watch(() => props.syncTrigger, (newValue, oldValue) => {
    // Check if the trigger value has changed and it's not the initial render
    if (newValue !== oldValue) {
        const websiteId = route.params.id
        fetchWebsiteInfo(websiteId)
        console.log('Information sheet data synced successfully')
    }
})

onMounted(() => {
    const websiteId = route.params.id
    if (websiteId) {
        fetchWebsiteInfo(websiteId)
    }
})
</script>

<style scoped>
.bg-primary\/10 {
    background-color: rgba(15, 157, 96, .1);
}
</style>