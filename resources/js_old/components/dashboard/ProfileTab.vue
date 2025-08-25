<template>
    <div class="container-fluid px-2 px-md-3">
        <div class="row">
            <div class="col-12">
                <!-- User Info Card -->
                <div class="bg-info rounded p-3 p-md-4 mb-3 mb-md-4 text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <div v-if="profileForm.avatar || authStore.user?.avatar" class="position-relative">
                            <img :src="profileForm.avatar || authStore.user?.avatar" :alt="authStore.user?.name"
                                class="rounded-circle border border-3 border-white shadow"
                                style="width: 60px; height: 60px; object-fit: cover;" 
                                :style="{ width: $isMobile ? '60px' : '80px', height: $isMobile ? '60px' : '80px' }"
                                @error="handleImageError" />
                            <button @click="removeAvatar"
                                class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0"
                                style="width: 20px; height: 20px; padding: 0; font-size: 10px;" title="Remove avatar">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <div v-else
                            class="d-flex align-items-center justify-content-center bg-primary rounded-circle mx-auto border border-3 border-white shadow"
                            :style="{ width: $isMobile ? '60px' : '80px', height: $isMobile ? '60px' : '80px' }">
                            <span class="text-white fw-bold" :class="$isMobile ? 'fs-4' : 'fs-2'">{{ getInitials(authStore.user?.name) }}</span>
                        </div>

                        <!-- Upload Avatar Button -->
                        <button @click="triggerFileUpload"
                            class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0"
                            style="width: 24px; height: 24px; padding: 0;" title="Upload avatar" :disabled="uploading">
                            <i v-if="uploading" class="bi bi-arrow-clockwise" style="font-size: 10px;"></i>
                            <i v-else class="bi bi-camera" style="font-size: 10px;"></i>
                        </button>

                        <!-- Hidden file input -->
                        <input ref="fileInput" type="file" accept="image/*" @change="handleFileUpload" class="d-none" />
                    </div>

                    <h4 class="mb-2 fs-5 fs-md-4">{{ profileForm.name || authStore.user?.name }}</h4>
                    <span class="badge bg-warning text-dark mb-2">Trial Account</span>
                    <p class="text-muted mb-0 small">Expire date: 02 Aug 2025</p>

                    <!-- Google Account Badge -->
                    <div v-if="authStore.user?.google_id" class="mt-2">
                        <span class="badge bg-success">
                            <i class="bi bi-google me-1"></i>
                            Connected with Google
                        </span>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="bg-white rounded p-3 p-md-4 shadow-sm">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2">
                        <h5 class="mb-0">Your Profile</h5>
                        <div v-if="hasChanges" class="text-muted small">
                            <i class="bi bi-circle-fill text-warning me-1" style="font-size: 8px;"></i>
                            Unsaved changes
                        </div>
                    </div>

                    <form @submit.prevent="updateProfile">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope text-muted"></i>
                                        </span>
                                        <input v-model="profileForm.email" type="email" class="form-control bg-light"
                                            readonly />
                                        <span v-if="authStore.user?.email_verified_at"
                                            class="input-group-text bg-success text-white">
                                            <i class="bi bi-check-circle" title="Verified"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person text-muted"></i>
                                        </span>
                                        <input v-model="profileForm.name" type="text" class="form-control"
                                            :class="{ 'is-invalid': errors.name }" placeholder="Enter your name"
                                            required />
                                    </div>
                                    <div v-if="errors.name" class="invalid-feedback d-block">
                                        {{ errors.name[0] }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-telephone text-muted"></i>
                                        </span>
                                        <input v-model="profileForm.phone" type="tel" class="form-control"
                                            :class="{ 'is-invalid': errors.phone }" placeholder="+1 (555) 123-4567" />
                                    </div>
                                    <div v-if="errors.phone" class="invalid-feedback d-block">
                                        {{ errors.phone[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted">Bio</label>
                            <textarea v-model="profileForm.bio" class="form-control"
                                :class="{ 'is-invalid': errors.bio }" rows="3" placeholder="Tell us about yourself..."
                                maxlength="500"></textarea>
                            <div class="d-flex justify-content-between">
                                <div v-if="errors.bio" class="invalid-feedback d-block">
                                    {{ errors.bio[0] }}
                                </div>
                                <small class="text-muted">{{ profileForm.bio?.length || 0 }}/500</small>
                            </div>
                        </div>

                        <!-- Success/Error Messages -->
                        <div v-if="successMessage" class="alert alert-success d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ successMessage }}
                        </div>

                        <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ errorMessage }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <button type="submit" class="btn btn-success" :disabled="loading || !hasChanges">
                                <span v-if="loading">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Updating...
                                </span>
                                <span v-else>
                                    <i class="bi bi-check me-1"></i>
                                    Update profile
                                </span>
                            </button>

                            <button type="button" @click="resetForm" class="btn btn-outline-secondary"
                                :disabled="loading || !hasChanges">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div v-if="showPasswordModal" class="modal d-block" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered mx-2 mx-md-auto">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="btn-close" @click="closePasswordModal"></button>
                    </div>
                    <form @submit.prevent="changePassword">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input v-model="passwordForm.current_password" type="password" class="form-control"
                                    :class="{ 'is-invalid': passwordErrors.current_password }" required />
                                <div v-if="passwordErrors.current_password" class="invalid-feedback">
                                    {{ passwordErrors.current_password[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input v-model="passwordForm.password" type="password" class="form-control"
                                    :class="{ 'is-invalid': passwordErrors.password }" required />
                                <div v-if="passwordErrors.password" class="invalid-feedback">
                                    {{ passwordErrors.password[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input v-model="passwordForm.password_confirmation" type="password" class="form-control"
                                    required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closePasswordModal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="passwordLoading">
                                <span v-if="passwordLoading">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Updating...
                                </span>
                                <span v-else>Update Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'

const authStore = useAuthStore()

// Form data
const profileForm = ref({
    name: '',
    email: '',
    phone: '',
    bio: '',
    avatar: ''
})

// Original data for comparison
const originalData = ref({})

// State
const loading = ref(false)
const uploading = ref(false)
const errors = ref({})
const successMessage = ref('')
const errorMessage = ref('')

// Password change
const showPasswordModal = ref(false)
const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: ''
})
const passwordErrors = ref({})
const passwordLoading = ref(false)

// File upload
const fileInput = ref(null)

// Computed
const hasChanges = computed(() => {
    return JSON.stringify(profileForm.value) !== JSON.stringify(originalData.value)
})

// Methods
const getInitials = (name) => {
    if (!name) return 'U'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const loadProfile = async () => {
    try {
        const response = await axios.get(ziggyRoute('api.profile.show'))
        const userData = response.data

        profileForm.value = {
            name: userData.name || '',
            email: userData.email || '',
            phone: userData.phone || '',
            bio: userData.bio || '',
            avatar: userData.avatar || ''
        }

        originalData.value = { ...profileForm.value }
    } catch (error) {
        console.error('Failed to load profile:', error)
    }
}

const updateProfile = async () => {
    loading.value = true
    errors.value = {}
    successMessage.value = ''
    errorMessage.value = ''

    try {
        const response = await axios.put(ziggyRoute('api.profile.update', profileForm.value))

        if (response.data.success) {
            successMessage.value = 'Profile updated successfully!'
            originalData.value = { ...profileForm.value }
            authStore.user = { ...authStore.user, ...response.data.user }

            setTimeout(() => {
                successMessage.value = ''
            }, 3000)
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to update profile'
        }
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    profileForm.value = { ...originalData.value }
    errors.value = {}
    successMessage.value = ''
    errorMessage.value = ''
}

const triggerFileUpload = () => {
    fileInput.value?.click()
}

const handleFileUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Please select a valid image file'
        return
    }

    if (file.size > 5 * 1024 * 1024) {
        errorMessage.value = 'Image size must be less than 5MB'
        return
    }

    uploading.value = true
    errorMessage.value = ''

    try {
        const formData = new FormData()
        formData.append('avatar', file)

        await axios.get("/sanctum/csrf-cookie");

        const response = await axios.post(
            ziggyRoute('api.profile.avatar.upload'),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        if (response.data.success) {
            profileForm.value.avatar = response.data.avatar_url
            successMessage.value = 'Avatar updated successfully!'

            setTimeout(() => {
                successMessage.value = ''
            }, 3000)
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to upload avatar'
    } finally {
        uploading.value = false
        if (fileInput.value) {
            fileInput.value.value = ''
        }
    }
}

const removeAvatar = async () => {
    try {
        const response = await axios.delete(ziggyRoute('api.profile.avatar.remove'))

        if (response.data.success) {
            profileForm.value.avatar = ''
            successMessage.value = 'Avatar removed successfully!'

            setTimeout(() => {
                successMessage.value = ''
            }, 3000)
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to remove avatar'
    }
}

const handleImageError = () => {
    profileForm.value.avatar = ''
}

const changePassword = async () => {
    passwordLoading.value = true
    passwordErrors.value = {}

    try {
        const response = await axios.put(
            ziggyRoute('api.profile.password.update'),
            passwordForm.value
        )

        if (response.data.success) {
            closePasswordModal()
            successMessage.value = 'Password changed successfully!'

            setTimeout(() => {
                successMessage.value = ''
            }, 3000)
        }
    } catch (error) {
        if (error.response?.status === 422) {
            passwordErrors.value = error.response.data.errors || {}
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to change password'
        }
    } finally {
        passwordLoading.value = false
    }
}

const closePasswordModal = () => {
    showPasswordModal.value = false
    passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
    }
    passwordErrors.value = {}
}

watch(() => authStore.user, (newUser) => {
    if (newUser) {
        profileForm.value.name = newUser.name || ''
        profileForm.value.email = newUser.email || ''
        profileForm.value.avatar = newUser.avatar || ''
    }
}, { immediate: true })

onMounted(() => {
    loadProfile()
})
</script>

<style scoped>
.modal {
    z-index: 1050;
}

.btn:focus {
    box-shadow: none;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.input-group:focus-within .input-group-text {
    border-color: #28a745;
}

.position-relative .btn {
    transition: all 0.2s ease-in-out;
}

.position-relative .btn:hover {
    transform: scale(1.1);
}

@media (max-width: 767.98px) {
    .modal-dialog {
        margin: 1rem;
    }
}
</style>