<template>
    <div class="min-vh-100" style="background-color: #f8f9fa;">
        <!-- Notification Component -->
        <NotificationAlert ref="notification" />

        <!-- Header -->
        <header class="bg-white border-bottom px-4 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center bg-success rounded-circle me-3"
                        style="width: 32px; height: 32px;">
                        <i class="bi bi-pause-fill text-white"></i>
                    </div>
                    <span class="fw-bold text-success fs-5">SheetExpress</span>
                </div>

                <button @click="exitWithConfirmation" class="btn btn-outline-secondary">
                    <i class="bi bi-x me-1"></i>
                    Exit
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">Create new website</h2>
                <p class="text-muted">
                    Pick one template to create your website. Don't worry, you can change the template later.
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="initialLoading" class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading templates...</p>
            </div>

            <!-- Templates Grid -->
            <div v-else class="row g-4 justify-content-center">
                <div v-for="template in templates" :key="template.id" class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm" 
                         :class="{ 'border-success': selectedTemplateId === template.site_type && loading }">
                        <div class="position-relative">
                            <img :src="template.image" :alt="template.name" class="card-img-top"
                                style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success">{{ template.category }}</span>
                            </div>
                            <!-- Loading Overlay -->
                            <div v-if="loading && selectedTemplateId === template.site_type" 
                                 class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                 style="background-color: rgba(255, 255, 255, 0.8);">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Creating...</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold">{{ template.name }}</h6>
                            <p class="card-text text-muted small flex-grow-1">{{ template.description }}</p>
                            <div class="d-flex gap-2 mt-auto">
                                <button @click="selectTemplate(template)" 
                                        class="btn btn-success btn-sm flex-fill" 
                                        :disabled="loading">
                                    <span v-if="loading && selectedTemplateId === template.site_type" 
                                          class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="bi bi-plus me-1"></i>
                                    {{ loading && selectedTemplateId === template.site_type ? 'Creating...' : 'Use template' }}
                                </button>
                                <button @click="previewTemplate(template)" 
                                        class="btn btn-outline-secondary btn-sm"
                                        :disabled="loading">
                                    <i class="bi bi-eye me-1"></i>
                                    Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State (if no templates) -->
            <div v-if="!initialLoading && templates.length === 0" class="text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">No templates available</h5>
                <p class="text-muted mb-4">Please try again later or contact support.</p>
                <button @click="loadTemplates" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Retry
                </button>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '../components/NotificationAlert.vue'
import sheetBlog from '@images/sheet-blog.png'
import sheetEcommerce from '@images/sheet-ecommerce.png'

const router = useRouter()
const notification = ref(null)
const loading = ref(false)
const initialLoading = ref(true)
const selectedTemplateId = ref(null)

const PREVIEW_URL_BLOG = import.meta.env.VITE_PREVIEW_URL_BLOG;
const PREVIEW_URL_ECOMMERCE = import.meta.env.VITE_PREVIEW_URL_ECOMMERCE;

const templates = ref([
    {
        id: 1,
        name: 'Blog',
        category: 'Blog',
        description: 'Perfect for personal or business blogs with clean design',
        image: `${sheetBlog}`,
        site_type: 1,
        // google_sheet_url: 'https://docs.google.com/spreadsheets/d/1EPVH68GntuAK5-onj85ORQlWgbHf0VRjCN52_AoEJB8/edit?gid=0#gid=0',
        google_sheet_url: 'https://docs.google.com/spreadsheets/d/1YyeccCB0F4S7Es4-7WHfrPJuqsFRHdntfWITBMV4l_k/edit?gid=0#gid=0',
        preview_url: PREVIEW_URL_BLOG
    },
    {
        id: 2,
        name: 'E-commerce',
        category: 'E-commerce',
        description: 'Sell products online with this store template',
        image: `${sheetEcommerce}`,
        site_type: 2,
        google_sheet_url: 'https://docs.google.com/spreadsheets/d/1vD7btlKwh00lhEmHhhKLVkafp8VhGrQYEYYyOuZozoQ/edit?gid=679145369#gid=679145369',
        preview_url: PREVIEW_URL_ECOMMERCE
    }
])

const loadTemplates = async () => {
    initialLoading.value = true
    try {
        // Simulate loading templates (in real app, this would be an API call)
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        if (templates.value.length > 0) {
            notification.value?.showSuccess('Templates loaded successfully!', 'Ready to create')
        }
    } catch (error) {
        console.error('Error loading templates:', error)
        notification.value?.showError('Failed to load templates. Please try again.', 'Loading Error')
    } finally {
        initialLoading.value = false
    }
}

const selectTemplate = async (template) => {
    if (loading.value) return

    loading.value = true
    selectedTemplateId.value = template.site_type

    try {
        notification.value?.showInfo(`Creating ${template.name} template...`, 'Please wait')
        
        // const response = await axios.post('/api/temps', {
        //     site_type: template.site_type
        // })
        const response = await axios.post(ziggyRoute('api.temps.store'), {
            site_type: template.site_type
        })

        notification.value?.showSuccess(`${template.name} template created successfully!`, 'Success!')
        
        // Small delay to show success message
        setTimeout(() => {
            // router.push(`/website/add/${response.data.temp_id}`)
            router.push(`/website/add/${response.data.temp_id}?site_type=${template.site_type}`)
        }, 1000)

    } catch (error) {
        console.error('Error creating temp:', error)
        
        if (error.response?.status === 403) {
            notification.value?.showError('You do not have permission to create websites.', 'Permission Denied')
        } else if (error.response?.status === 422) {
            notification.value?.showError('Invalid template selection. Please try again.', 'Validation Error')
        } else if (error.response?.status === 429) {
            notification.value?.showWarning('Too many requests. Please wait a moment before trying again.', 'Rate Limited')
        } else if (error.response?.status >= 500) {
            notification.value?.showError('Server error occurred. Please try again later.', 'Server Error')
        } else {
            notification.value?.showError('Failed to create template. Please try again.', 'Creation Error')
        }
    } finally {
        loading.value = false
        selectedTemplateId.value = null
    }
}

const previewTemplate = (template) => {
    if (!template.preview_url || template.preview_url.trim() === '') {
        notification.value?.showWarning(
            `Preview URL is not available for ${template.name}.`,
            'No Preview'
        )
        return
    }

    notification.value?.showInfo(`Opening ${template.name} template preview...`, 'Preview')

    try {
        window.open(template.preview_url, '_blank')
        notification.value?.showSuccess('Template preview opened in new tab.', 'Preview Ready')
    } catch (error) {
        console.error('Error opening preview:', error)
        notification.value?.showError('Failed to open template preview.', 'Preview Error')
    }
}

const exitWithConfirmation = () => {
    if (loading.value) {
        notification.value?.showWarning('Please wait for the current operation to complete.', 'Operation in Progress')
        return
    }

    const shouldExit = confirm('Are you sure you want to exit? Any unsaved progress will be lost.')
    if (shouldExit) {
        notification.value?.showInfo('Returning to dashboard...', 'Goodbye!')
        setTimeout(() => {
            router.push('/dashboard/websites')
        }, 500)
    }
}

onMounted(() => {
    loadTemplates()
})
</script>

<style scoped>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

.border-success {
    border: 2px solid #28a745 !important;
}

.btn:disabled {
    cursor: not-allowed;
}
</style>