<template>
    <div>
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Pages</h4>
                <p class="text-muted mb-0">Manage your pages</p>
            </div>
            <button @click="showCreateForm" class="btn btn-success rounded-pill">
                <i class="bi bi-plus me-1"></i>
                Create page
            </button>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading pages...</p>
        </div>

        <div v-else-if="pages.length > 0" class="row">
            <div v-for="page in pages" :key="page.id" class="col-12">
                <div class="card border mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div>
                                <h5 class="mb-1">{{ page.title }}</h5>
                                <p class="text-muted mb-0">/{{ page.page_address }}</p>
                            </div>
                            <div class="d-flex flex-wrap flex-md-nowrap gap-2">
                                <button @click="editPage(page)" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- <button @click="refreshPage(page)" class="btn btn-sm btn-outline-secondary" title="Refresh">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button @click="duplicatePage(page)" class="btn btn-sm btn-outline-secondary"
                                    title="Duplicate">
                                    <i class="bi bi-files"></i>
                                </button> -->
                                <button @click="confirmDeletePage(page)" class="btn btn-sm btn-outline-secondary"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <a :href="`${linkWebsite}/pages/${page.page_address}`" target="_blank"
                                    class="btn btn-sm btn-outline-primary" title="View page">
                                    View page <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">You don't have any pages</h5>
                <p class="text-muted">Create a page and display your content</p>
                <button @click="showCreateForm" class="btn btn-success">
                    <i class="bi bi-plus me-1"></i>
                    Create a new page
                </button>
            </div>
        </div>

        <!-- Page Form Modal -->
        <PageForm v-if="showForm" :page="selectedPage" :isEdit="isEditing" @cancel="closeForm" @submit="submitForm" />

        <!-- Confirm Delete Modal -->
        <ConfirmDialog v-if="showDeleteConfirm" :show="showDeleteConfirm" title="Delete this page?"
            message="Deleting this page will remove it from this list and remove it from your website. This action cannot be undone."
            type="danger" confirmText="Delete" @cancel="showDeleteConfirm = false" @confirm="deletePage" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue'
import PageForm from '@/components/website/PageForm.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const route = useRoute()
const notificationAlert = ref(null)

const loading = ref(true)
const pages = ref([])
const showForm = ref(false)
const isEditing = ref(false)
const selectedPage = ref(null)
const showDeleteConfirm = ref(false)
const pageToDelete = ref(null)
const linkWebsite = ref('')

const websiteId = ref(route.params.id) // Biến để lưu trữ websiteId

const fetchPages = async () => {
    try {
        loading.value = true
        // const response = await axios.get('/api/pages')
        const response = await axios.get(ziggyRoute('api.pages.index', { site: websiteId.value }))
        
        pages.value = response.data
        notificationAlert.value?.showSuccess('Pages loaded successfully')
    } catch (error) {
        console.error('Failed to fetch pages:', error)
        notificationAlert.value?.showError('Failed to load pages', 'Loading Error')
    } finally {
        loading.value = false
    }
}

const fetchWebsiteInfo = async (id) => {
    try {
        const response = await axios.get(ziggyRoute('api.sites.show', { id: websiteId.value }))

        if (response.data) {
            linkWebsite.value = 'https://' + response.data.domain_name || ''
        }
    } catch (error) {
        console.error('Failed to fetch website info:', error)

        // if (error.response?.status === 403) {
        //     notificationAlert.value?.showError('You do not have permission to access this website', 'Access Denied')
        // } else if (error.response?.status === 404) {
        //     notificationAlert.value?.showError('Website not found', 'Not Found')
        // } else {
        //     notificationAlert.value?.showError('Failed to load data', 'Loading Error')
        // }
    }
}

const showCreateForm = () => {
    selectedPage.value = null
    isEditing.value = false
    showForm.value = true
}

const editPage = (page) => {
    selectedPage.value = { ...page }
    isEditing.value = true
    showForm.value = true
}

const closeForm = () => {
    showForm.value = false
    selectedPage.value = null
}

const submitForm = async (formData) => {
    try {
        if (isEditing.value) {
            // Update existing page
            // const response = await axios.put(`/api/pages/${formData.id}`, formData)
            const pageIdVal = formData.id
            // const response = await axios.put(ziggyRoute('api.pages.update', { page: pageIdVal }), formData)

            const response = await axios.put(
                ziggyRoute('api.pages.update', {
                    site: websiteId.value,
                    page: pageIdVal
                }),
                formData
            );

            if(response.data.status){
                // Update the page in the list
                // const index = pages.value.findIndex(p => p.id === formData.id)
                // if (index !== -1) {
                //     pages.value[index] = response.data.data
                // }

                fetchPages();

                notificationAlert.value?.showSuccess('Page updated successfully', 'Success!')
            }else{
                notificationAlert.value?.showError(response.data.message, 'Error')
            }
        } else {
            // Create new page
            const response = await axios.post(ziggyRoute('api.pages.store', { site: websiteId.value }), formData)

            if(response.data.status){
                // pages.value.unshift(response.data.data)
                fetchPages();
                notificationAlert.value?.showSuccess('Page created successfully', 'Success!')
            }else{
                notificationAlert.value?.showError(response.data.message, 'Error')
            }
        }

        closeForm()
    } catch (error) {
        console.error('Failed to save page:', error)

        if (error.response?.status === 422) {
            const errors = error.response.data.errors
            const errorMessages = Object.values(errors).flat().join(', ')
            notificationAlert.value?.showError(errorMessages, 'Validation Error')
        } else {
            notificationAlert.value?.showError('Failed to save page', 'Error')
        }
    }
}

const confirmDeletePage = (page) => {
    pageToDelete.value = page
    showDeleteConfirm.value = true
}

const deletePage = async () => {
    try {
        // await axios.delete(`/api/pages/${pageToDelete.value.id}`)
        const pageIdVal = pageToDelete.value.id
        // await axios.delete(ziggyRoute('api.pages.destroy', { page: pageIdVal }))

        await axios.delete(
            ziggyRoute('api.pages.destroy', {
                site: websiteId.value,
                page: pageIdVal
            })
        );

        // Remove the page from the list
        // pages.value = pages.value.filter(p => p.id !== pageToDelete.value.id)
        fetchPages();

        notificationAlert.value?.showSuccess('Page deleted successfully', 'Success!')
        showDeleteConfirm.value = false
    } catch (error) {
        console.error('Failed to delete page:', error)
        notificationAlert.value?.showError('Failed to delete page', 'Error')
    }
}

const refreshPage = async (page) => {
    try {
        notificationAlert.value?.showInfo('Refreshing page...', 'Please wait')

        // Simulate refresh delay
        await new Promise(resolve => setTimeout(resolve, 1000))

        notificationAlert.value?.showSuccess('Page refreshed successfully', 'Success!')
    } catch (error) {
        console.error('Failed to refresh page:', error)
        notificationAlert.value?.showError('Failed to refresh page', 'Error')
    }
}

const duplicatePage = async (page) => {
    // try {
    //     // Create a copy of the page without the ID
    //     const pageCopy = { ...page }
    //     delete pageCopy.id
    //     pageCopy.title = `${pageCopy.title} (Copy)`
    //     pageCopy.page_address = `${pageCopy.page_address}-copy`

    //     const response = await axios.post('/api/pages', pageCopy)
    //     pages.value.unshift(response.data)

    //     notificationAlert.value?.showSuccess('Page duplicated successfully', 'Success!')
    // } catch (error) {
    //     console.error('Failed to duplicate page:', error)
    //     notificationAlert.value?.showError('Failed to duplicate page', 'Error')
    // }
}

onMounted(() => {
    fetchPages()
    fetchWebsiteInfo(websiteId.value)
})
</script>