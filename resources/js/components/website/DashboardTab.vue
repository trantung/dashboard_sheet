<template>
    <div class="container-fluid px-2 px-md-3">
        <NotificationAlert ref="notificationAlert" />
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <div>
                <h4 class="mb-1 fs-5 fs-md-4">Dashboard</h4>
                <p class="text-muted mb-0 small">Customize and update your website</p>
            </div>
        </div>

        <div class="row g-3 g-md-4 mb-3 mb-md-4">
            <!-- Customize your site -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-1 dashboard-card">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <h6 class="card-title mb-2">Customize your site</h6>
                        <p class="card-text text-muted small flex-grow-1 mb-3">
                            Customize site: show or hide action blocks, page size, etc.
                        </p>
                        <button class="btn btn-outline-secondary btn-sm mt-auto" @click="$emit('change-tab', 'settings')">
                            <span class="d-none d-sm-inline">Customize</span>
                            <span class="d-sm-none">Settings</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customize your domain -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-1 dashboard-card">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <h6 class="card-title mb-2">Customize your domain</h6>
                        <p class="card-text text-muted small flex-grow-1 mb-3">
                            Personalize your domain by connecting it to SheetExpress.
                        </p>
                        <button class="btn btn-outline-secondary btn-sm mt-auto" @click="$emit('change-tab', 'domain')">
                            <span class="d-none d-sm-inline">Connect domain</span>
                            <span class="d-sm-none">Domain</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pages -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-1 dashboard-card">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <h6 class="card-title mb-2">Pages</h6>
                        <p class="card-text text-muted small flex-grow-1 mb-3">
                            Create multiple pages from Google Docs.
                        </p>
                        <button class="btn btn-outline-secondary btn-sm mt-auto" @click="$emit('change-tab', 'pages')">
                            <span class="d-none d-sm-inline">Create page</span>
                            <span class="d-sm-none">Pages</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Embed your site into external websites -->
        <div class="card border-1 mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">
                <h6 class="card-title mb-2 mb-md-3">Embed your site into external websites</h6>
                <p class="card-text text-muted small mb-3">
                    Integrate your content or services into other websites through iframes for seamless visibility and
                    interaction. Access the settings to customize the display of sections such as the header, footer, hero section,
                    and more.
                </p>
                <div class="bg-light p-3 rounded position-relative">
                    <code class="small text-dark d-block text-break">
                        &lt;iframe src="https://testweb.microgem.io.vn" width="100%" height="100%" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;
                    </code>
                    <button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2 d-none d-md-block" 
                            @click="copyEmbedCode">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <!-- Mobile copy button -->
                <button class="btn btn-sm btn-outline-secondary mt-2 d-md-none w-100" @click="copyEmbedCode">
                    <i class="bi bi-clipboard me-1"></i>
                    Copy embed code
                </button>
            </div>
        </div>

        <!-- Password protected -->
        <div class="card border-1">
            <div class="card-body p-3 p-md-4">
                <h6 class="card-title mb-2 mb-md-3">Password protected</h6>
                <p class="card-text text-muted small mb-3">
                    Limit access to your website with a password. Each line represents one password. Leave it blank if
                    your website is public.
                </p>
                <textarea v-model="passwords" class="form-control mb-3" rows="4"
                    placeholder="Each line represents one password"></textarea>
                <button class="btn btn-success btn-sm w-100 w-sm-auto" @click="updatePasswords" :disabled="isUpdating">
                    <span v-if="isUpdating" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ isUpdating ? 'Updating...' : 'Update password' }}
                    <i v-if="!isUpdating" class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
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

const loading = ref(true)
const isUpdating = ref(false)
const passwords = ref('')

const isUrl = (string) => {
    try {
        new URL(string)
        return true
    } catch {
        return false
    }
}

const copyEmbedCode = async () => {
    const embedCode = '<iframe src="https://testweb.microgem.io.vn" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>';
    try {
        await navigator.clipboard.writeText(embedCode);
        notificationAlert.value?.showSuccess('Embed code copied to clipboard!');
    } catch (error) {
        console.error('Failed to copy embed code:', error);
        notificationAlert.value?.showError('Failed to copy embed code');
    }
}

const updatePasswords = async () => {
    isUpdating.value = true;
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        notificationAlert.value?.showSuccess('Passwords updated successfully!');
    } catch (error) {
        console.error('Failed to update passwords:', error);
        notificationAlert.value?.showError('Failed to update passwords');
    } finally {
        isUpdating.value = false;
    }
}

const fetchWebsiteInfo = async (id) => {
    try {
        loading.value = true
        const response = await axios.get(ziggyRoute('api.sites.show', { id }))

        if (response.data) {
            notificationAlert.value?.showSuccess('Data loaded successfully');
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
            notificationAlert.value?.showError('Failed to load data', 'Loading Error')
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    const websiteId = route.params.id
    if (websiteId) {
        fetchWebsiteInfo(websiteId)
    }
})
</script>

<style scoped>
/* Dashboard card hover effects */
.dashboard-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

/* Mobile optimizations */
@media (max-width: 575.98px) {
    .card-body {
        padding: 1rem !important;
    }
    
    .btn-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .text-break {
        word-break: break-all;
    }
}

/* Tablet optimizations */
@media (min-width: 576px) and (max-width: 767.98px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Desktop optimizations */
@media (min-width: 768px) {
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

/* Button improvements */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Code block responsive */
code {
    font-size: 0.75rem;
}

@media (min-width: 768px) {
    code {
        font-size: 0.875rem;
    }
}

/* Textarea responsive */
textarea {
    resize: vertical;
    min-height: 100px;
}

@media (min-width: 768px) {
    textarea {
        min-height: 120px;
    }
}
</style>