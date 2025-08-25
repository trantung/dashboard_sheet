<template>
    <div class="container-fluid px-2 px-md-3">
        <!-- Notification Component -->
        <NotificationAlert ref="notification" />

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
            <h4 class="mb-0 fs-5 fs-md-4">My workspaces ({{ workspaces.length }})</h4>
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input v-model="searchQuery" type="text" class="form-control" placeholder="Search...">
                </div>
                <button @click="showCreateModal = true" class="btn btn-success btn-sm btn-md-normal">
                    <i class="bi bi-plus me-1"></i>
                    <span class="d-none d-sm-inline">Create workspace</span>
                    <span class="d-sm-none">Create</span>
                </button>
            </div>
        </div>

        <!-- Workspaces List -->
        <div v-if="filteredWorkspaces.length > 0" class="row g-2 g-md-3">
            <div v-for="workspace in filteredWorkspaces" :key="workspace.id" class="col-12 col-sm-6 col-lg-4 mb-3 mb-md-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center flex-grow-1 min-width-0">
                                <div class="bg-success rounded d-flex align-items-center justify-content-center me-2 me-md-3 flex-shrink-0" 
                                     style="width: 35px; height: 35px;">
                                    <i class="bi bi-briefcase text-white small"></i>
                                </div>
                                <div class="min-width-0 flex-grow-1">
                                    <h6 class="mb-1 text-truncate">{{ workspace.name }}</h6>
                                    <small class="text-muted">{{ workspace.users?.length || 0 }} members</small>
                                </div>
                            </div>
                            
                            <div class="position-relative flex-shrink-0" @click.stop>
                                <button class="btn btn-sm btn-outline-secondary p-1" @click="toggleDropdown(workspace.id)">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul
                                    v-if="activeDropdownId === workspace.id"
                                    class="dropdown-menu show position-absolute"
                                    style="right: 0; top: 100%; z-index: 1000; min-width: 150px;"
                                >
                                    <li>
                                        <a class="dropdown-item py-2" href="#" @click.prevent="editWorkspace(workspace)">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="#" @click.prevent="manageMembers(workspace)">
                                            <i class="bi bi-people me-2"></i>Members
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li>
                                        <a class="dropdown-item text-danger py-2" href="#" @click.prevent="deleteWorkspace(workspace)">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-muted small mb-2 mb-md-3">Created {{ formatDate(workspace.created_at) }}</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-folder me-1"></i>
                                Open
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="workspaces.length === 0" class="bg-white rounded p-4 p-md-5 text-center">
            <i class="bi bi-briefcase fs-1 text-muted mb-3"></i>
            <h5 class="mb-2 fs-6 fs-md-5">You don't have any workspaces</h5>
            <p class="text-muted mb-3 mb-md-4 small">Create a workspace with a list of websites and invite members to join today.</p>
            <button @click="showCreateModal = true" class="btn btn-success">
                <i class="bi bi-plus me-1"></i>
                Create workspace
            </button>
        </div>

        <!-- Create/Edit Workspace Modal -->
        <div class="modal fade" :class="{ show: showCreateModal }" :style="{ display: showCreateModal ? 'block' : 'none' }" 
             tabindex="-1" @click.self="closeCreateModal">
            <div class="modal-dialog mx-2 mx-md-auto">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingWorkspace ? 'Edit' : 'Create' }} Workspace</h5>
                        <button type="button" class="btn-close" @click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveWorkspace">
                            <div class="mb-3">
                                <label class="form-label">Workspace Name <span class="text-danger">*</span></label>
                                <input v-model="workspaceForm.name" type="text" class="form-control" 
                                       placeholder="Enter workspace name" required>
                                <div v-if="formErrors.name" class="text-danger small mt-1">{{ formErrors.name }}</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeCreateModal">Cancel</button>
                        <button type="button" class="btn btn-success" @click="saveWorkspace" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ editingWorkspace ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Members Modal -->
        <div class="modal fade" :class="{ show: showMembersModal }" :style="{ display: showMembersModal ? 'block' : 'none' }" 
             tabindex="-1" @click.self="closeMembersModal">
            <div class="modal-dialog mx-2 mx-md-auto">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Members - {{ selectedWorkspace?.name }}</h5>
                        <button type="button" class="btn-close" @click="closeMembersModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Add Member -->
                        <div class="mb-4">
                            <label class="form-label">Add Member</label>
                            <div class="input-group">
                                <input v-model="memberEmail" type="email" class="form-control" 
                                       placeholder="Enter email address">
                                <button class="btn btn-outline-primary" @click="addMember" :disabled="loading || !memberEmail.trim()">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    Add
                                </button>
                            </div>
                            <div v-if="formErrors.email" class="text-danger small mt-1">{{ formErrors.email }}</div>
                        </div>

                        <!-- Members List -->
                        <div v-if="selectedWorkspace?.users?.length">
                            <h6>Current Members</h6>
                            <div class="list-group">
                                <div v-for="user in selectedWorkspace.users" :key="user.id" 
                                     class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div class="min-width-0 flex-grow-1">
                                        <strong class="d-block text-truncate">{{ user.name }}</strong>
                                        <small class="text-muted text-truncate d-block">{{ user.email }}</small>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <button v-if="user.id !== selectedWorkspace.user_id" 
                                                class="btn btn-sm btn-outline-danger" 
                                                @click="removeMember(user.id)">
                                            <span class="d-none d-sm-inline">Remove</span>
                                            <i class="bi bi-trash d-sm-none"></i>
                                        </button>
                                        <span v-else class="badge bg-primary">Owner</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Backdrop -->
        <div v-if="showCreateModal || showMembersModal" class="modal-backdrop fade show"></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '../NotificationAlert.vue'

const notification = ref(null)
const workspaces = ref([])
const searchQuery = ref('')
const showCreateModal = ref(false)
const showMembersModal = ref(false)
const loading = ref(false)
const editingWorkspace = ref(null)
const selectedWorkspace = ref(null)
const memberEmail = ref('')
const formErrors = ref({})

const workspaceForm = ref({
    name: ''
})

const filteredWorkspaces = computed(() => {
    if (!searchQuery.value) return workspaces.value
    return workspaces.value.filter(workspace => 
        workspace.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const fetchWorkspaces = async () => {
    try {
        const response = await axios.get(ziggyRoute('api.workspaces.index'))
        workspaces.value = response.data
        notification.value?.clearNotifications()
        
        if (workspaces.value.length === 0) {
            notification.value?.showInfo('No workspaces found. Create your first workspace to get started!', 'Welcome!')
        }
    } catch (error) {
        console.error('Error fetching workspaces:', error)
        notification.value?.showError('Failed to load workspaces. Please refresh the page.', 'Loading Error')
    }
}

const saveWorkspace = async () => {
    if (!workspaceForm.value.name.trim()) {
        formErrors.value.name = 'Workspace name is required'
        return
    }

    formErrors.value = {}
    loading.value = true
    
    try {
        if (editingWorkspace.value) {
            const response = await axios.put(
                ziggyRoute('api.workspaces.update', { workspace: editingWorkspace.value.id }),
                workspaceForm.value
            )
            const index = workspaces.value.findIndex(w => w.id === editingWorkspace.value.id)
            workspaces.value[index] = response.data
            notification.value?.showSuccess(`Workspace "${response.data.name}" updated successfully!`, 'Success!')
        } else {
            const response = await axios.post(
                ziggyRoute('api.workspaces.store'),
                workspaceForm.value
            )
            workspaces.value.push(response.data)
            notification.value?.showSuccess(`Workspace "${response.data.name}" created successfully!`, 'Success!')
        }
        closeCreateModal()
    } catch (error) {
        console.error('Error saving workspace:', error)
        
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors || {}
            notification.value?.showError('Please check the form for errors.', 'Validation Error')
        } else if (error.response?.status === 403) {
            notification.value?.showError('You do not have permission to perform this action.', 'Permission Denied')
        } else {
            notification.value?.showError('Failed to save workspace. Please try again.', 'Save Error')
        }
    } finally {
        loading.value = false
    }
}

const editWorkspace = (workspace) => {
    editingWorkspace.value = workspace
    workspaceForm.value.name = workspace.name
    formErrors.value = {}
    showCreateModal.value = true
    closeDropdown()
}

const deleteWorkspace = async (workspace) => {
    if (!confirm(`Are you sure you want to delete "${workspace.name}"? This action cannot be undone.`)) return

    try {
        await axios.delete(
            ziggyRoute('api.workspaces.destroy', { workspace: workspace.id })
        )
        workspaces.value = workspaces.value.filter(w => w.id !== workspace.id)
        notification.value?.showSuccess(`Workspace "${workspace.name}" deleted successfully.`, 'Deleted!')
    } catch (error) {
        console.error('Error deleting workspace:', error)
        
        if (error.response?.status === 403) {
            notification.value?.showError('You do not have permission to delete this workspace.', 'Permission Denied')
        } else if (error.response?.status === 409) {
            notification.value?.showError('Cannot delete workspace that contains websites. Please delete all websites first.', 'Delete Failed')
        } else {
            notification.value?.showError('Failed to delete workspace. Please try again.', 'Delete Error')
        }
    } finally {
        closeDropdown()
    }
}

const manageMembers = async (workspace) => {
    selectedWorkspace.value = workspace
    memberEmail.value = ''
    formErrors.value = {}
    showMembersModal.value = true
    closeDropdown()
    
    try {
        const response = await axios.get(
            ziggyRoute('api.workspaces.show', { workspace: workspace.id })
        )
        selectedWorkspace.value = response.data
    } catch (error) {
        console.error('Error fetching workspace details:', error)
        notification.value?.showWarning('Could not load member details.', 'Warning')
    }
}

const addMember = async () => {
    if (!memberEmail.value.trim()) {
        formErrors.value.email = 'Email is required'
        return
    }

    formErrors.value = {}
    loading.value = true
    
    try {
        await axios.post(
            ziggyRoute('api.workspaces.users.add', { workspace: selectedWorkspace.value.id }),
            { email: memberEmail.value }
        )

        memberEmail.value = ''
        notification.value?.showSuccess('Member added successfully!', 'Success!')
        
        const response = await axios.get(
            ziggyRoute('api.workspaces.show', { workspace: selectedWorkspace.value.id })
        )

        selectedWorkspace.value = response.data
        
        const index = workspaces.value.findIndex(w => w.id === selectedWorkspace.value.id)
        if (index > -1) {
            workspaces.value[index] = response.data
        }
    } catch (error) {
        console.error('Error adding member:', error)
        
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors || {}
            notification.value?.showError('Please check the email address.', 'Validation Error')
        } else if (error.response?.status === 404) {
            notification.value?.showError('User with this email address not found.', 'User Not Found')
        } else if (error.response?.status === 409) {
            notification.value?.showWarning('User is already a member of this workspace.', 'Already Member')
        } else {
            notification.value?.showError('Failed to add member. Please try again.', 'Add Member Error')
        }
    } finally {
        loading.value = false
    }
}

const removeMember = async (userId) => {
    if (!confirm('Are you sure you want to remove this member from the workspace?')) return

    try {
        await axios.delete(
            ziggyRoute('api.workspaces.users.remove', { workspace: selectedWorkspace.value.id }),
            {
                data: { user_id: userId }
            }
        )
        
        notification.value?.showSuccess('Member removed successfully.', 'Success!')
        
        const response = await axios.get(
            ziggyRoute('api.workspaces.show', { workspace: selectedWorkspace.value.id })
        )

        selectedWorkspace.value = response.data
        
        const index = workspaces.value.findIndex(w => w.id === selectedWorkspace.value.id)
        if (index > -1) {
            workspaces.value[index] = response.data
        }
    } catch (error) {
        console.error('Error removing member:', error)
        
        if (error.response?.status === 403) {
            notification.value?.showError('You do not have permission to remove this member.', 'Permission Denied')
        } else {
            notification.value?.showError('Failed to remove member. Please try again.', 'Remove Member Error')
        }
    }
}

const closeCreateModal = () => {
    showCreateModal.value = false
    editingWorkspace.value = null
    workspaceForm.value.name = ''
    formErrors.value = {}
}

const closeMembersModal = () => {
    showMembersModal.value = false
    selectedWorkspace.value = null
    memberEmail.value = ''
    formErrors.value = {}
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}

const activeDropdownId = ref(null)

const toggleDropdown = (id) => {
    activeDropdownId.value = activeDropdownId.value === id ? null : id
}

const closeDropdown = () => {
    activeDropdownId.value = null
}

// Close dropdown when click outside
document.addEventListener('click', closeDropdown)

onMounted(() => {
    fetchWorkspaces()
})
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
}

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
    
    .modal-dialog {
        margin: 1rem;
    }
}
</style>