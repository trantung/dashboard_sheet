<template>
    <div class="container-fluid px-2 px-md-3">
        <NotificationAlert ref="notificationAlert" />
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <div>
                <h4 class="mb-1 fs-5 fs-md-4">Custom domain</h4>
                <p class="text-muted mb-0 small">Manage your custom domain and subdomain for this website.</p>
            </div>
        </div>

        <!-- Subdomain Section -->
        <div class="card border-1 mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3 flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                            <i class="bi bi-globe text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-2">Subdomain</h6>
                        <p class="text-muted small mb-0">
                            Your <strong>*.microgem.io.vn</strong> subdomain.<br>
                            You can easily update it here.
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-medium">
                        Choose your subdomain <span class="text-danger">*</span>
                    </label>
                    
                    <!-- Desktop/Tablet Input Group -->
                    <div class="input-group d-none d-md-flex">
                        <span class="input-group-text bg-light">https://</span>
                        <input 
                            v-model="subdomain" 
                            type="text" 
                            class="form-control" 
                            placeholder="your-subdomain"
                            :class="{ 'is-invalid': subdomainError }"
                            @input="validateSubdomain"
                        >
                        <span class="input-group-text text-muted bg-light">.microgem.io.vn</span>
                        <button 
                            class="btn btn-success" 
                            @click="updateSubdomain" 
                            :disabled="isUpdating"
                        >
                            <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            {{ isUpdating ? 'Updating...' : 'Update' }}
                            <i v-if="!isUpdating" class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                    
                    <!-- Mobile Stacked Layout -->
                    <div class="d-md-none">
                        <div class="mb-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light small">https://</span>
                                <input 
                                    v-model="subdomain" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="your-subdomain"
                                    :class="{ 'is-invalid': subdomainError }"
                                    @input="validateSubdomain"
                                >
                                <span class="input-group-text text-muted bg-light small">.microgem.io.vn</span>
                            </div>
                            <div v-if="subdomainError" class="invalid-feedback d-block">
                                {{ subdomainError }}
                            </div>
                        </div>
                        <button 
                            class="btn btn-success w-100" 
                            @click="updateSubdomain" 
                            :disabled="isUpdating"
                        >
                            <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            {{ isUpdating ? 'Updating...' : 'Update Subdomain' }}
                            <i v-if="!isUpdating" class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                    
                    <!-- Current URL Display -->
                    <div v-if="currentFullDomain" class="mt-3 p-3 bg-light rounded">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div>
                                <small class="text-muted d-block">Current URL:</small>
                                <a :href="`https://${currentFullDomain}`" target="_blank" class="text-decoration-none fw-medium">
                                    {{ `https://${currentFullDomain}` }}
                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" @click="copyUrl">
                                <i class="bi bi-clipboard me-1"></i>
                                <span class="d-none d-sm-inline">Copy</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Validation Error -->
                    <div v-if="subdomainError" class="invalid-feedback d-block d-md-none mt-2">
                        {{ subdomainError }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Domain Section (Pro Feature) -->
        <div class="card border-1">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">
                        Custom domain
                        <span class="badge bg-dark ms-2">Pro</span>
                    </h6>
                    <button class="btn btn-success btn-sm">Upgrade to Pro →</button>
                </div>

                <div class="bg-light p-4 rounded text-center">
                    <div class="text-muted">
                        <p class="mb-2">The custom domain feature is available with **Pro** plans.</p>
                        <p class="mb-0">Upgrade to connect your own domain to your website.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue';

const route = useRoute();
const notificationAlert = ref(null);

const loading = ref(true);
const subdomain = ref('');
const currentFullDomain = ref('');
const siteData = ref(null);
const isUpdating = ref(false);
const subdomainError = ref('');

// Function to extract subdomain
const extractSubdomain = (fullDomain) => {
    if (fullDomain) {
        const parts = fullDomain.split('.microgem.io.vn');
        if (parts.length > 0 && parts[0]) {
            return parts[0];
        }
    }
    return '';
};

// Function to copy URL
const copyUrl = async () => {
    if (!currentFullDomain.value) return;
    
    try {
        await navigator.clipboard.writeText(`https://${currentFullDomain.value}`);
        notificationAlert.value?.showSuccess('URL copied to clipboard!');
    } catch (error) {
        console.error('Failed to copy URL:', error);
        notificationAlert.value?.showError('Failed to copy URL');
    }
};

// Function to handle subdomain update
const updateSubdomain = async () => {
    if (!route.params.id) {
        notificationAlert.value?.showError('Website ID is missing. Cannot update subdomain.');
        return;
    }

    const value = subdomain.value.trim();
    
    if (!value) {
        subdomainError.value = 'Subdomain is required';
        return;
    }
    
    if (value.length < 3) {
        subdomainError.value = 'Subdomain must be at least 3 characters';
        return;
    }
    
    if (value.length > 63) {
        subdomainError.value = 'Subdomain must be less than 63 characters';
        return;
    }
    
    // Check for valid characters (alphanumeric and hyphens, but not starting/ending with hyphen)
    const validPattern = /^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?$/;
    if (!validPattern.test(value)) {
        subdomainError.value = 'Subdomain can only contain letters, numbers, and hyphens (not at start/end)';
        return;
    }
    
    subdomainError.value = '';
    isUpdating.value = true;

    try {
        const response = await axios.post(ziggyRoute('api.sites.subdomain.update', { site: route.params.id }), {
            subdomain: value
        });

        if (response.data && response.data.domain_name) {
            siteData.value.domain_name = response.data.domain_name;
            currentFullDomain.value = response.data.domain_name;
            subdomain.value = extractSubdomain(response.data.domain_name); 
            notificationAlert.value?.showSuccess('Subdomain updated successfully!');
        } else {
            currentFullDomain.value = `${value}.microgem.io.vn`;
            notificationAlert.value?.showSuccess('Subdomain updated successfully!');
        }
    } catch (error) {
        console.error('Failed to update subdomain:', error);

        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to update this subdomain.', 'Access Denied');
        } else if (error.response?.status === 422 && error.response.data.errors?.subdomain) {
            subdomainError.value = error.response.data.errors.subdomain[0];
            notificationAlert.value?.showError(error.response.data.errors.subdomain[0], 'Validation Error');
        } else if (error.response?.status === 409) {
            subdomainError.value = 'This subdomain is already taken';
            notificationAlert.value?.showError('This subdomain is already taken', 'Conflict');
        } else {
            notificationAlert.value?.showError('Failed to update subdomain. Please try again.', 'Update Error');
        }
    } finally {
        isUpdating.value = false;
    }
};

const fetchWebsiteInfo = async (id) => {
    try {
        loading.value = true;
        const response = await axios.get(ziggyRoute('api.sites.show', { id }));

        if (response.data) {
            siteData.value = response.data;
            currentFullDomain.value = siteData.value.domain_name;
            subdomain.value = extractSubdomain(siteData.value.domain_name);
            notificationAlert.value?.showSuccess('Website information loaded successfully.');
        } else {
            notificationAlert.value?.showWarning('No website information found.');
        }
    } catch (error) {
        console.error('Failed to fetch website info:', error);

        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to access this website.', 'Access Denied');
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found.', 'Not Found');
        } else {
            notificationAlert.value?.showError('Failed to load website information.', 'Loading Error');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const websiteId = route.params.id;
    if (websiteId) {
        fetchWebsiteInfo(websiteId);
    }
});
</script>

<style scoped>
/* Mobile optimizations */
@media (max-width: 575.98px) {
    .container-fluid {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .input-group-text {
        font-size: 0.875rem;
        padding: 0.375rem 0.5rem;
    }
    
    .btn {
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.65rem;
    }
}

/* Tablet optimizations */
@media (min-width: 576px) and (max-width: 767.98px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}

/* Desktop optimizations */
@media (min-width: 768px) {
    .container-fluid {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }
}

/* Input group styling */
.input-group-text {
    background-color: var(--bs-light);
    border-color: var(--bs-border-color);
    color: var(--bs-secondary);
}

.form-control:focus {
    border-color: var(--bs-success);
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.form-control.is-invalid {
    border-color: var(--bs-danger);
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Button improvements */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:disabled {
    transform: none;
}

/* Card improvements */
.card {
    transition: box-shadow 0.2s ease;
}

.card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

/* Icon styling */
.rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rounded-circle i {
    font-size: 1.25rem;
}

/* Badge styling */
.badge {
    font-weight: 500;
}

/* Pro feature styling */
.bg-light {
    background-color: var(--bs-gray-100) !important;
}

/* Link styling */
a {
    color: var(--bs-primary);
    transition: color 0.2s ease;
}

a:hover {
    color: var(--bs-primary);
    text-decoration: underline !important;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Validation styling */
.invalid-feedback {
    font-size: 0.875rem;
}

/* Feature list styling */
.bi-check-circle-fill {
    font-size: 1rem;
}

/* Responsive adjustments for feature grid */
@media (max-width: 575.98px) {
    .col-sm-6 {
        flex: 0 0 auto;
        width: 100%;
    }
    
    .d-flex.align-items-center {
        margin-bottom: 0.5rem;
    }
}

/* Focus management */
.btn:focus,
.form-control:focus {
    outline: none;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .card {
        border: 2px solid;
    }
    
    .input-group-text {
        border: 1px solid;
    }
}

/* Print styles */
@media print {
    .btn,
    .badge {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}

/* Animation for success states */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-success:not(:disabled):active {
    animation: pulse 0.3s ease;
}
</style>