<template>
    <div class="min-vh-100 bg-light d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="text-center mb-4">
                        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-success rounded-circle"
                            style="width: 64px; height: 64px;">
                            <i class="bi bi-pause-fill text-white fs-3"></i>
                        </div>
                        <div class="bg-primary text-white px-3 py-2 rounded mb-2 d-inline-block">
                            <h2 class="mb-0 fs-5 fw-bold">Sign up for free</h2>
                        </div>
                        <p class="text-muted">Welcome To SheetExpress</p>
                    </div>

                    <form @submit.prevent="handleRegister" class="bg-white p-4 rounded shadow">
                        <div class="mb-3">
                            <label for="name" class="form-label text-muted">Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input id="name" v-model="form.name" type="text" class="form-control border-start-0"
                                    :class="{ 'is-invalid': errors.name }" placeholder="Enter your name" required />
                            </div>
                            <div v-if="errors.name" class="invalid-feedback d-block">
                                {{ errors.name[0] }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-muted">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input id="email" v-model="form.email" type="email" class="form-control border-start-0"
                                    :class="{ 'is-invalid': errors.email }" placeholder="Enter your email" required />
                            </div>
                            <div v-if="errors.email" class="invalid-feedback d-block">
                                {{ errors.email[0] }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input id="password" v-model="form.password" type="password"
                                    class="form-control border-start-0" :class="{ 'is-invalid': errors.password }"
                                    placeholder="Enter your password" required />
                            </div>
                            <div v-if="errors.password" class="invalid-feedback d-block">
                                {{ errors.password[0] }}
                            </div>
                            <!-- Password Strength Indicator -->
                            <div v-if="form.password" class="mt-2">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" :class="passwordStrengthClass"
                                        :style="{ width: passwordStrengthWidth }"></div>
                                </div>
                                <small :class="passwordStrengthTextClass">{{ passwordStrengthText }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label text-muted">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                    class="form-control border-start-0" placeholder="Confirm your password" required />
                            </div>
                            <div v-if="form.password_confirmation && form.password !== form.password_confirmation"
                                class="text-danger small mt-1">
                                Passwords do not match
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                By signing up, I agree to the
                                <a href="#" class="text-success text-decoration-none">Terms</a> and
                                <a href="#" class="text-success text-decoration-none">privacy policy</a>
                            </small>
                        </div>

                        <div v-if="error" class="alert alert-danger" role="alert">
                            {{ error }}
                        </div>

                        <button type="submit" :disabled="authStore.loading || !isFormValid"
                            class="btn btn-success w-100 py-2 fw-medium mb-3">
                            <span v-if="authStore.loading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing up...
                            </span>
                            <span v-else>Sign Up</span>
                        </button>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">
                                Already have an account?
                                <router-link to="/login" class="text-success text-decoration-none fw-medium">
                                    Sign In
                                </router-link>
                            </p>
                        </div>

                        <hr class="my-3">

                        <!-- Google Sign Up Button -->
                        <div id="google-signup-button" class="d-flex justify-content-center mb-3"></div>

                        <!-- Fallback Google Button -->
                        <!-- <button type="button" @click="handleGoogleSignup" :disabled="authStore.loading"
                            class="btn btn-outline-secondary w-100 py-2">
                            <span v-if="googleLoading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing up with Google...
                            </span>
                            <span v-else>
                                <i class="bi bi-google me-2"></i>
                                Sign up with Google
                            </span>
                        </button> -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const error = ref('')
const errors = ref({})
const googleLoading = ref(false)

// Google Sign-In configuration
const GOOGLE_CLIENT_ID = '127333669613-it7ts5m6dbkpiv97ar1orc0vt1nren7a.apps.googleusercontent.com' // Replace with your actual client ID

// Password strength validation
const passwordStrength = computed(() => {
    const password = form.value.password
    if (!password) return 0

    let strength = 0
    if (password.length >= 8) strength++
    if (/[a-z]/.test(password)) strength++
    if (/[A-Z]/.test(password)) strength++
    if (/[0-9]/.test(password)) strength++
    if (/[^A-Za-z0-9]/.test(password)) strength++

    return strength
})

const passwordStrengthClass = computed(() => {
    const strength = passwordStrength.value
    if (strength <= 2) return 'bg-danger'
    if (strength <= 3) return 'bg-warning'
    return 'bg-success'
})

const passwordStrengthWidth = computed(() => {
    return `${(passwordStrength.value / 5) * 100}%`
})

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value
    if (strength <= 2) return 'Weak password'
    if (strength <= 3) return 'Medium password'
    return 'Strong password'
})

const passwordStrengthTextClass = computed(() => {
    const strength = passwordStrength.value
    if (strength <= 2) return 'text-danger'
    if (strength <= 3) return 'text-warning'
    return 'text-success'
})

const isFormValid = computed(() => {
    return form.value.name &&
        form.value.email &&
        form.value.password &&
        form.value.password_confirmation &&
        form.value.password === form.value.password_confirmation &&
        passwordStrength.value >= 3
})

const handleRegister = async () => {
    error.value = ''
    errors.value = {}

    // Basic validation
    if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Passwords do not match'
        return
    }

    if (passwordStrength.value < 3) {
        error.value = 'Password is too weak. Please use a stronger password.'
        return
    }

    const result = await authStore.register(form.value)

    if (result.success) {
        router.push('/dashboard')
    } else {
        error.value = result.message
        errors.value = result.errors || {}
    }
}

const handleGoogleSignup = async () => {
    if (window.google && window.google.accounts) {
        // Use Google Identity Services
        window.google.accounts.id.prompt()
    } else {
        // Fallback: redirect to Google OAuth
        const googleAuthUrl = `https://accounts.google.com/oauth/authorize?client_id=${GOOGLE_CLIENT_ID}&redirect_uri=${encodeURIComponent(window.location.origin + '/auth/google/callback')}&scope=openid%20email%20profile&response_type=code`
        window.location.href = googleAuthUrl
    }
}

const handleCredentialResponse = async (response) => {
    googleLoading.value = true
    error.value = ''

    try {
        const result = await authStore.loginWithGoogle(response.credential)

        if (result.success) {
            router.push('/dashboard')
        } else {
            error.value = result.message
        }
    } catch (err) {
        error.value = 'Google sign-up failed. Please try again.'
    } finally {
        googleLoading.value = false
    }
}

const initializeGoogleSignIn = () => {
    if (window.google && window.google.accounts) {
        window.google.accounts.id.initialize({
            client_id: GOOGLE_CLIENT_ID,
            callback: handleCredentialResponse,
            auto_select: false,
            cancel_on_tap_outside: true
        })

        // Render the Google Sign-In button for signup
        window.google.accounts.id.renderButton(
            document.getElementById('google-signup-button'),
            {
                theme: 'outline',
                size: 'large',
                width: '100%',
                text: 'signup_with',
                shape: 'rectangular'
            }
        )
    }
}

const loadGoogleScript = () => {
    return new Promise((resolve, reject) => {
        if (window.google && window.google.accounts) {
            resolve()
            return
        }

        const script = document.createElement('script')
        script.src = 'https://accounts.google.com/gsi/client'
        script.async = true
        script.defer = true
        script.onload = resolve
        script.onerror = reject
        document.head.appendChild(script)
    })
}

onMounted(async () => {
    try {
        await loadGoogleScript()
        initializeGoogleSignIn()
    } catch (error) {
        console.error('Failed to load Google Sign-In:', error)
    }
})

onUnmounted(() => {
    // Clean up Google Sign-In
    if (window.google && window.google.accounts) {
        window.google.accounts.id.cancel()
    }
})
</script>

<style scoped>
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.input-group:focus-within .input-group-text {
    border-color: #28a745;
}

/* Google Sign-In button styling */
#google-signup-button {
    min-height: 44px;
}

#google-signup-button>div {
    width: 100% !important;
}

/* Password strength indicator */
.progress {
    border-radius: 2px;
}

.progress-bar {
    transition: width 0.3s ease;
}
</style>