<template>
    <div>
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Custom Code</h4>
                <p class="text-muted mb-0">Customize JS and CSS code for your website</p>
            </div>
        </div>

        <div class="card border-1 mb-4">
            <div class="card-body">
                <h6 class="card-title">Inject custom JS code</h6>
                <p class="text-muted">
                    You can inject custom JS code here. This code will be applied across all pages.
                    You can choose the position to display the JS code on your website.
                </p>

                <div class="mb-3">
                    <label class="form-label">JS position</label>
                    <select v-model="jsPosition" class="form-select">
                        <option value="bodyClose">Body Close</option>
                        <option value="bodyOpen">Body Open</option>
                        <option value="head">Head</option>
                    </select>
                </div>

                <div class="mb-3">
                    <textarea v-model="customJS" class="form-control font-monospace" rows="8"
                        placeholder="Enter your JS code here..."></textarea>
                </div>
            </div>
        </div>

        <div class="card border-1 mb-4">
            <div class="card-body">
                <h6 class="card-title">Inject custom CSS code</h6>
                <p class="text-muted">
                    You can inject custom CSS code here, without the &lt;style&gt; tag. This code will be applied across
                    all pages.
                </p>

                <div class="mb-3">
                    <textarea v-model="customCSS" class="form-control font-monospace" rows="8"
                        placeholder="Enter your CSS code here..."></textarea>
                </div>
            </div>
        </div>

        <button class="btn btn-success" @click="updateCustomCode" :disabled="isUpdating">
            <span v-if="isUpdating" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            {{ isUpdating ? 'Updating...' : 'Update code' }}
            <i v-if="!isUpdating" class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue'

const route = useRoute()
const notificationAlert = ref(null)

const isLoading = ref(true) // Đổi tên từ 'loading' để rõ ràng hơn cho việc fetch ban đầu
const isUpdating = ref(false) // Thêm biến cho trạng thái cập nhật

const websiteId = ref(null) // Thêm ref để lưu websiteId
const jsPosition = ref('')
const customJS = ref('')
const customCSS = ref('')

// Function để fetch dữ liệu ban đầu
const fetchWebsiteInfo = async () => {
    isLoading.value = true;
    try {
        const id = route.params.id; // Lấy ID từ route params
        if (!id) {
            notificationAlert.value?.showError('Website ID not found in URL.', 'Error');
            isLoading.value = false;
            return;
        }
        websiteId.value = id; // Gán ID vào ref

        // const response = await axios.get(`/api/sites/${id}`)
        const response = await axios.get(ziggyRoute('api.sites.show', { id }))

        if (response.data) {
            jsPosition.value = response.data.custom_js_position || 'head'; // Đặt giá trị mặc định nếu null/undefined
            customJS.value = response.data.custom_js || '';
            customCSS.value = response.data.custom_css || '';
            notificationAlert.value?.showSuccess('Custom code loaded successfully');
        } else {
            notificationAlert.value?.showWarning('No website data received.');
        }
    } catch (error) {
        console.error('Failed to fetch website info:', error);
        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to access this website', 'Access Denied');
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found', 'Not Found');
        } else {
            notificationAlert.value?.showError('Failed to load custom code', 'Loading Error');
        }
    } finally {
        isLoading.value = false;
    }
};

// Function để gửi dữ liệu cập nhật
const updateCustomCode = async () => {
    if (!websiteId.value) {
        notificationAlert.value?.showError('Cannot update: Website ID is missing.', 'Error');
        return;
    }

    isUpdating.value = true;
    try {
        const payload = {
            custom_js_position: jsPosition.value,
            custom_js: customJS.value,
            custom_css: customCSS.value,
        };

        // Gửi PATCH request để cập nhật dữ liệu
        // Giả sử API endpoint là /api/sites/{id} và nó chấp nhận các trường này
        // const response = await axios.put(`/api/sites/${websiteId.value}`, payload);
        const websiteIdVal = websiteId.value
        const response = await axios.put(ziggyRoute('api.sites.update', { site: websiteIdVal }), payload)

        if (response.data.success) { // Giả sử API trả về { success: true, message: '...' }
            notificationAlert.value?.showSuccess(response.data.message || 'Custom code updated successfully!');
        } else {
            notificationAlert.value?.showError(response.data.message || 'Failed to update custom code.', 'Update Error');
        }
    } catch (error) {
        console.error('Failed to update custom code:', error);
        if (error.response?.data?.message) {
            notificationAlert.value?.showError(error.response.data.message, 'Update Error');
        } else if (error.response?.status === 422) { // Lỗi validation từ backend Laravel
             notificationAlert.value?.showError('Validation failed. Please check your input.', 'Validation Error');
        }
        else {
            notificationAlert.value?.showError('An unexpected error occurred during update.', 'Error');
        }
    } finally {
        isUpdating.value = false;
    }
};

onMounted(() => {
    fetchWebsiteInfo();
});
</script>