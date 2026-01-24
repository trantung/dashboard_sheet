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
                    <span class="fw-bold text-success fs-5">Sheetany</span>
                </div>

                <button @click="exitFlow" class="btn btn-outline-secondary">
                    <i class="bi bi-x me-1"></i>
                    Exit
                </button>
            </div>
        </header>

        <!-- Progress Steps -->
        <div class="bg-white border-bottom px-4 py-3">
            <div class="d-flex justify-content-center">
                <div class="d-flex align-items-center gap-4">
                    <!-- Step 1 -->
                    <div class="d-flex align-items-center">
                        <div :class="['rounded-circle d-flex align-items-center justify-content-center me-2', currentStep >= 1 ? 'bg-success text-white' : 'bg-light text-muted']"
                            style="width: 32px; height: 32px;">
                            <i v-if="currentStep > 1" class="bi bi-check"></i>
                            <span v-else>1</span>
                        </div>
                        <span :class="currentStep >= 1 ? 'text-success fw-medium' : 'text-muted'">Create Google Sheet</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="d-flex align-items-center">
                        <div :class="['rounded-circle d-flex align-items-center justify-content-center me-2', currentStep >= 2 ? 'bg-success text-white' : 'bg-light text-muted']"
                            style="width: 32px; height: 32px;">
                            <i v-if="currentStep > 2" class="bi bi-check"></i>
                            <span v-else>2</span>
                        </div>
                        <span :class="currentStep >= 2 ? 'text-success fw-medium' : 'text-muted'">Data Mapping</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="d-flex align-items-center">
                        <div :class="['rounded-circle d-flex align-items-center justify-content-center me-2', currentStep >= 3 ? 'bg-success text-white' : 'bg-light text-muted']"
                            style="width: 32px; height: 32px;">
                            <span>3</span>
                        </div>
                        <span :class="currentStep >= 3 ? 'text-success fw-medium' : 'text-muted'">Select subdomain</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="container py-5">
            <!-- Step 1: Create Google Sheet -->
            <div v-if="currentStep === 1" class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold mb-3">Create content with Google Sheets</h3>
                        <p class="text-muted">
                            Bring your own Google Sheets or "Make a copy" our Google Sheets
                        </p>
                    </div>

                    <div class="bg-white rounded p-5 text-center shadow-sm">
                        <p class="text-muted mb-4">
                            Make a copy our Google Sheets template include information and content
                        </p>
                        <button @click="makeACopy" class="btn btn-outline-secondary mb-4">
                            Make a copy <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </button>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button @click="nextStep" class="btn btn-dark px-4" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Data Mapping -->
            <div v-if="currentStep === 2" class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold mb-3">Mapping data from your template to site</h3>
                        <p class="text-muted">
                            To build the site, you need to connect two <strong>sheets</strong>: information and
                            <strong>content</strong> from Google Sheets.
                        </p>
                    </div>

                    <div class="bg-white rounded p-5 shadow-sm">
                        <div v-if="!isConnected" class="text-center">
                            <h5 class="mb-3">Google Sheets is not connected</h5>
                            <p class="text-muted mb-4">Please enter public Google Sheets URL of your site</p>

                            <div class="mb-4">
                                <label class="form-label text-start d-block">Google Sheets URL <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input v-model="googleSheetsUrl" type="url" class="form-control"
                                        placeholder="https://docs.google.com/spreadsheets/d/xxx">
                                    <button @click="connectGoogleSheets" class="btn btn-outline-secondary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        Connect <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center">
                            <h5 class="mb-3 text-success">Google Sheets is connected</h5>
                            <p class="text-muted mb-4">Please enter public Google Sheets URL of your site</p>

                            <div class="mb-4">
                                <label class="form-label text-start d-block">Google Sheets URL <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input v-model="googleSheetsUrl" type="url" class="form-control" readonly>
                                    <button @click="connectGoogleSheets" class="btn btn-outline-secondary">
                                        Connect <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-success d-flex align-items-center mb-4">
                                <i class="bi bi-check-circle me-2"></i>
                                <div class="text-start">
                                    Connected to your Google Sheets. Choose the sheet on the right that matches the
                                    information on the left to build your site.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-start d-block fw-medium">Information</label>
                                    <p class="text-muted small text-start">Information Sheet</p>
                                    <select v-model="selectedInfoSheet" class="form-select">
                                        <option v-for="sheet in availableSheets" :key="sheet" :value="sheet">{{ sheet }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-start d-block fw-medium">Content</label>
                                    <p class="text-muted small text-start">Content Sheet</p>
                                    <select v-model="selectedContentSheet" class="form-select">
                                        <option v-for="sheet in availableSheets" :key="sheet" :value="sheet">{{ sheet }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button @click="prevStep" class="btn btn-outline-secondary px-4">Back</button>
                        <button @click="nextStep" :disabled="!canProceedStep2 || loading" class="btn btn-dark px-4">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Select subdomain -->
            <div v-if="currentStep === 3" class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold mb-3">You're all set to go!</h3>
                        <p class="text-muted">
                            Choose a sheetany.site subdomain for your site. Don't worry,<br>
                            you can change and add your own domain later.
                        </p>
                    </div>

                    <div class="bg-white rounded p-5 shadow-sm">
                        <p class="text-muted mb-4 text-center">
                            Make sure you enter only the first part of the subdomain.
                        </p>

                        <div class="mb-4">
                            <label class="form-label">Enter your site's name <span class="text-danger">*</span></label>
                            <input v-model="siteName" type="text" class="form-control" placeholder="Test Web">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Choose your subdomain <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">https://</span>
                                <input v-model="subdomain" type="text" class="form-control" placeholder="testweb">
                                <span class="input-group-text text-muted">.ieltscheckmate.edu.vn</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button @click="prevStep" class="btn btn-outline-secondary px-4">Back</button>
                        <button @click="finishWebsite" :disabled="!canFinish || loading" class="btn btn-success px-4">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            Finish
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '../components/NotificationAlert.vue'

const router = useRouter()
const route = useRoute()

const currentStep = ref(1)
const tempId = ref('')
const googleSheetsUrl = ref('')
const isConnected = ref(false)
const availableSheets = ref([])
const selectedInfoSheet = ref('')
const selectedContentSheet = ref('')
const siteName = ref('')
const subdomain = ref('')
const loading = ref(false)
const notification = ref(null)

const canProceedStep2 = computed(() => {
    return isConnected.value && selectedInfoSheet.value && selectedContentSheet.value
})

const canFinish = computed(() => {
    return siteName.value.trim() && subdomain.value.trim()
})

const makeACopy = () => {
    // This will be populated based on the template type
    const templateUrls = {
        // 1: 'https://docs.google.com/spreadsheets/d/1EPVH68GntuAK5-onj85ORQlWgbHf0VRjCN52_AoEJB8/edit?gid=0#gid=0',
        1: 'https://docs.google.com/spreadsheets/d/1YyeccCB0F4S7Es4-7WHfrPJuqsFRHdntfWITBMV4l_k/edit?gid=0#gid=0',
        2: 'https://docs.google.com/spreadsheets/d/1TBQ4rDrwMIJhEBhNsFohP-HqvMZIKx8CZ-qrO6e0Zoc/edit?gid=0#gid=0'
    }
    
    // For now, open the first template
    window.open(templateUrls[1], '_blank')
}

const connectGoogleSheets = async () => {
    if (!googleSheetsUrl.value) {
        notification.value?.showWarning('Please enter a Google Sheets URL first.', 'URL Required')
        return
    }

    if (!googleSheetsUrl.value) return

    loading.value = true
    try {
        // Update temp with Google Sheet URL
        // await axios.put(`/api/temps/${tempId.value}/google-sheet`, {
        //     google_sheet: googleSheetsUrl.value
        // })

        const tempIdVal = tempId.value;

        await axios.put(ziggyRoute('api.temps.google_sheet.update', { temp: tempIdVal }), {
            google_sheet: googleSheetsUrl.value
        })

        notification.value?.showInfo('Connecting to Google Sheets...', 'Connecting')

        // Get sheet data from server
        // const response = await axios.get(`/api/temps/${tempId.value}/google-sheet-data`)

        const response = await axios.get(ziggyRoute('api.temps.google_sheet.data', { temp: tempIdVal }))
        
        if (response.data.connected) {
            availableSheets.value = response.data.sheets
            isConnected.value = true

            // Auto-select sheets if available
            if (availableSheets.value.includes('Information')) {
                selectedInfoSheet.value = 'Information'
            }
            if (availableSheets.value.includes('Content')) {
                selectedContentSheet.value = 'Content'
            }

            notification.value?.showSuccess(
                `Successfully connected! Found ${availableSheets.value.length} sheets in your Google Sheets.`,
                'Connected Successfully!'
            )
        } else {
            notification.value?.showError('Unable to connect to Google Sheets. Please check the URL and permissions.', 'Connection Failed')
        }
    } catch (error) {
        console.error('Error connecting to Google Sheets:', error)
        
        if (error.response?.status === 403) {
            notification.value?.showError('Access denied. Please make sure the Google Sheet is public or shared with proper permissions.', 'Permission Error')
        } else if (error.response?.status === 404) {
            notification.value?.showError('Google Sheet not found. Please check the URL and try again.', 'Sheet Not Found')
        } else {
            notification.value?.showError('Failed to connect to Google Sheets. Please check the URL and make sure the sheet is public.', 'Connection Error')
        }
    } finally {
        loading.value = false
    }
}

const nextStep = async () => {
    if (currentStep.value === 1) {
        // Step 1 to 2: Just move to next step
        currentStep.value++
        notification.value?.showInfo('Please connect your Google Sheets to continue.', 'Next Step')
    } else if (currentStep.value === 2) {
        // Step 2 to 3: Send temp_id to server (already handled in connectGoogleSheets)
        currentStep.value++
        notification.value?.showInfo('Almost done! Please enter your site details.', 'Final Step')
    }
}

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
        notification.value?.clearNotifications()
    }
}

const finishWebsite = async () => {
    if (!siteName.value.trim()) {
        notification.value?.showWarning('Please enter a site name.', 'Site Name Required')
        return
    }

    if (!subdomain.value.trim()) {
        notification.value?.showWarning('Please enter a subdomain.', 'Subdomain Required')
        return
    }

    loading.value = true
    notification.value?.clearNotifications()
    
    try {
        notification.value?.showInfo('Creating your website...', 'Processing')

        const tempIdVal = tempId.value;

        // const response = await axios.post(`/api/temps/${tempId.value}/finish`, {
        //     site_name: siteName.value,
        //     site_domain: subdomain.value
        // })

        const response = await axios.post(ziggyRoute('api.temps.finish', { temp: tempIdVal }), {
            site_name: siteName.value,
            site_domain: subdomain.value
        })

        notification.value?.showSuccess('Your website has been created successfully!', 'Website Created!', {
            duration: 3000
        })

        // Redirect after showing success message
        setTimeout(() => {
            router.push('/dashboard/websites')
        }, 2000)

    } catch (error) {
        console.error('Error creating website:', error)
        
        if (error.response?.status === 422) {
            const errors = error.response.data.errors
            if (errors.site_domain) {
                notification.value?.showError('This subdomain is already taken. Please choose a different one.', 'Subdomain Unavailable')
            } else {
                notification.value?.showError('Please check your input and try again.', 'Validation Error')
            }
        } else if (error.response?.status === 409) {
            notification.value?.showError('A website with this subdomain already exists. Please choose a different subdomain.', 'Subdomain Conflict')
        } else {
            notification.value?.showError('Something went wrong while creating your website. Please try again.', 'Creation Failed')
        }
    } finally {
        loading.value = false
    }
}

const exitFlow = () => {
    if (confirm('Are you sure you want to exit? Your progress will be lost.')) {
        router.push('/dashboard/websites')
    }
}

onMounted(() => {
    tempId.value = route.params.templateId
    notification.value?.showInfo('Welcome to the website creation wizard!', 'Getting Started')
})
</script>

<style scoped>
.input-group-text {
    background-color: #f8f9fa;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
</style>