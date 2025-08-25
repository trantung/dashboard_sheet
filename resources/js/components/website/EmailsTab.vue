<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Emails</h4>
                <p class="text-muted mb-0">Manage collected emails</p>
            </div>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm"
                        :class="{ active: filters.type === null }" @click="setFilter('type', null)">
                        All Types
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" :class="{ active: filters.type === 1 }"
                        @click="setFilter('type', 1)">
                        Feedback
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" :class="{ active: filters.type === 2 }"
                        @click="setFilter('type', 2)">
                        Subscribe
                    </button>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm"
                        :class="{ active: filters.status === null }" @click="setFilter('status', null)">
                        All Status
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm"
                        :class="{ active: filters.status === 1 }" @click="setFilter('status', 1)">
                        Success
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        :class="{ active: filters.status === 2 }" @click="setFilter('status', 2)">
                        Failed
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4" v-if="stats">
            <div class="col-md-3">
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-0">Total Emails</h6>
                                <h4 class="mb-0">{{ stats.total }}</h4>
                            </div>
                            <i class="bi bi-envelope fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-0">Feedback</h6>
                                <h4 class="mb-0">{{ stats.feedback }}</h4>
                            </div>
                            <i class="bi bi-chat-dots fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-0">Subscribers</h6>
                                <h4 class="mb-0">{{ stats.subscribe }}</h4>
                            </div>
                            <i class="bi bi-person-plus fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-0">Success Rate</h6>
                                <h4 class="mb-0">{{ successRate }}%</h4>
                            </div>
                            <i class="bi bi-check-circle fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search emails or content..."
                        v-model="searchQuery" @input="debounceSearch">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <select class="form-select d-inline-block w-auto" v-model="perPage" @change="fetchEmails">
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>

        <div v-if="loading" class="card border-1">
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading emails...</p>
            </div>
        </div>

        <div v-else-if="error" class="card border-1">
            <div class="card-body text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
                <h5 class="mb-2 text-danger">Error Loading Emails</h5>
                <p class="text-muted mb-3">{{ error }}</p>
                <button class="btn btn-primary" @click="fetchEmails">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Retry
                </button>
            </div>
        </div>

        <div v-else-if="emails.length === 0" class="card border-1">
            <div class="card-body text-center py-5">
                <i class="bi bi-envelope fs-1 text-muted mb-3"></i>
                <h5 class="mb-2">No Emails Found</h5>
                <p class="text-muted">No emails match your current filters</p>
            </div>
        </div>

        <div v-else class="card border-1">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Email</th>
                                <th scope="col">Content</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="email in emails" :key="email.id">
                                <td>{{ email.id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope me-2 text-muted"></i>
                                        <span>{{ email.email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" :title="email.content">
                                        {{ email.content || '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="email.type === 1 ? 'bg-info' : 'bg-warning'">
                                        {{ email.type_text }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="email.status === 1 ? 'bg-success' : 'bg-danger'">
                                        {{ email.status_text }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted" :title="email.created_at">
                                        {{ email.created_at_human }}
                                    </small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <nav v-if="pagination && pagination.last_page > 1" class="mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <ul class="pagination mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <button class="page-link" @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1">
                            Previous
                        </button>
                    </li>

                    <li v-for="page in visiblePages" :key="page" class="page-item"
                        :class="{ active: page === pagination.current_page }">
                        <button class="page-link" @click="changePage(page)">
                            {{ page }}
                        </button>
                    </li>

                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <button class="page-link" @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page">
                            Next
                        </button>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router'
import axios from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

// --- State Variables (reactive)
const emails = ref([]);
const stats = ref(null);
const pagination = ref(null);
const loading = ref(false);
const error = ref(null);
const searchQuery = ref('');
const searchTimeout = ref(null);
const perPage = ref(15);
const currentPage = ref(1);
const filters = ref({
    type: null,
    status: null
});

const route = useRoute()

const websiteId = ref(route.params.id) // Biến để lưu trữ websiteId

// --- Computed Properties
const successRate = computed(() => {
    if (!stats.value || stats.value.total === 0) return 0;
    return Math.round((stats.value.success / stats.value.total) * 100);
});

const visiblePages = computed(() => {
    if (!pagination.value) return [];

    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const pages = [];

    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// --- Methods
const fetchEmails = async () => {
    loading.value = true;
    error.value = null;

    try {
        const queryParams = {
            page: currentPage.value,
            per_page: perPage.value
        };

        if (searchQuery.value) {
            queryParams.search = searchQuery.value;
        }

        if (filters.value.type !== null) {
            queryParams.type = filters.value.type;
        }

        if (filters.value.status !== null) {
            queryParams.status = filters.value.status;
        }

        const url = ziggyRoute('api.emails.index', { site: websiteId.value });

        const response = await axios.get(url, { params: queryParams });

        emails.value = response.data.data;
        pagination.value = response.data.pagination;

    } catch (err) {
        error.value = 'Network error occurred. Please try again.';
        console.error('Error fetching emails:', err);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await axios.get(ziggyRoute('api.emails.stats', { site: websiteId.value }));

        stats.value = response.data.data;
    } catch (err) {
        console.error('Error fetching stats:', err);
    }
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        currentPage.value = page;
        fetchEmails();
    }
};

const setFilter = (type, value) => {
    filters.value[type] = value;
    currentPage.value = 1;
    fetchEmails();
};

const debounceSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        currentPage.value = 1;
        fetchEmails();
    }, 500);
};

// --- Lifecycle Hook
onMounted(() => {
    fetchEmails();
    fetchStats();
});

</script>

<style scoped>
.table-responsive {
    border-radius: 0.375rem;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.pagination .page-link {
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>