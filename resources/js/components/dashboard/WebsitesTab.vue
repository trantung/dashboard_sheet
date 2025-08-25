<template>
    <div class="container-fluid px-2 px-md-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <h4 class="mb-0 fs-5 fs-md-4">My websites ({{ websites.length }})</h4>
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                <div class="input-group w-100 w-md-auto">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input v-model="searchQuery" type="text" class="form-control" placeholder="Search...">
                </div>
                <button @click="fetchWebsites" class="btn btn-primary btn-sm btn-md-normal" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    <span class="d-none d-sm-inline">Update</span>
                </button>
            </div>
        </div>

        <div class="row g-2 g-md-3">
            <!-- New Website Card -->
            <div class="col-12 col-sm-6 col-lg-4 mb-3 mb-md-4">
                <div @click="$router.push('/new-website')"
                    class="border border-2 border-dashed rounded p-3 p-md-5 text-center h-100 d-flex flex-column justify-content-center cursor-pointer"
                    style="cursor: pointer; min-height: 150px;">
                    <i class="bi bi-plus-circle fs-1 text-muted mb-2 mb-md-3"></i>
                    <h6 class="text-muted">New Website</h6>
                </div>
            </div>

            <!-- Existing Websites -->
            <div v-for="website in filteredWebsites" :key="website.id" class="col-12 col-sm-6 col-lg-4 mb-3 mb-md-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center me-2 me-md-3 flex-shrink-0"
                                style="width: 35px; height: 35px;">
                                <span class="text-white fw-bold small">{{ website.name?.charAt(0) || 'W' }}</span>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="mb-1 text-truncate">{{ website.name }}</h6>
                                <small class="text-muted text-truncate d-block">{{ website.domain_name }}</small>
                            </div>
                        </div>
                        <div class="mb-2 mb-md-3">
                            <span class="badge" :class="website.type === 1 ? 'bg-info' : 'bg-success'">
                                {{ website.type === 1 ? 'Blog' : 'E-commerce' }}
                            </span>
                        </div>
                        <p class="text-muted small mb-2 mb-md-3">Created {{ formatDate(website.created_at) }}</p>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button @click="$router.push(`/website/${website.id}/manage/dashboard`)"
                                class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-speedometer2 me-1"></i>
                                <span class="d-none d-sm-inline">Dashboard</span>
                                <span class="d-sm-none">Manage</span>
                            </button>
                            <button @click="viewWebsite(website)" class="btn btn-outline-secondary btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>
                                <span class="d-none d-sm-inline">View website</span>
                                <span class="d-sm-none">View</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'

const websites = ref([])
const searchQuery = ref('')
const loading = ref(false)

const filteredWebsites = computed(() => {
    if (!searchQuery.value) return websites.value
    return websites.value.filter(website => 
        website.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        website.domain_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const fetchWebsites = async () => {
    loading.value = true
    try {
        const response = await axios.get(ziggyRoute('api.sites.index'))
        websites.value = response.data
    } catch (error) {
        console.error('Error fetching websites:', error)
    } finally {
        loading.value = false
    }
}

const viewWebsite = (website) => {
    window.open(`https://${website.domain_name}`, '_blank')
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
    fetchWebsites()
})
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.min-width-0 {
    min-width: 0;
}

@media (max-width: 575.98px) {
    .card-body {
        padding: 0.75rem;
    }
}

@media (min-width: 768px) {
    .w-md-auto {
        width: auto !important;
    }
}
</style>