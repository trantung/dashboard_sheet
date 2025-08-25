<template>
    <div class="min-vh-100 bg-light d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="text-center mb-4">
                        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-success rounded-circle"
                            style="width: 64px; height: 64px;">
                            <i class="bi bi-check-circle text-white fs-3"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Welcome Back</h2>
                        <p class="text-muted">Please sign in to your account</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="bg-white p-4 rounded shadow">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input id="email" v-model="form.email" type="email" class="form-control"
                                    placeholder="Enter your email" required />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="password" v-model="form.password" type="password" class="form-control"
                                    placeholder="Enter your password" required />
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="#" class="text-success text-decoration-none">Forgot Password?</a>
                        </div>

                        <div v-if="error" class="alert alert-danger" role="alert">
                            {{ error }}
                        </div>

                        <button type="submit" :disabled="authStore.loading"
                            class="btn btn-success w-100 py-2 fw-medium mb-3">
                            <span v-if="authStore.loading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                            <span v-else>Sign In</span>
                        </button>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">
                                Don't have an account?
                                <router-link to="/register" class="text-success text-decoration-none fw-medium">Sign up
                                    now</router-link>
                            </p>
                        </div>

                        <hr class="my-4">

                        <!-- Google Sign In Button -->
                        <div id="google-signin-button" class="d-flex justify-content-center mb-3"></div>

                        <!-- Fallback Google Button -->
                        <!-- <button type="button" @click="handleGoogleLogin" :disabled="authStore.loading"
                            class="btn btn-outline-secondary w-100 py-2">
                            <span v-if="googleLoading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in with Google...
                            </span>
                            <span v-else>
                                <i class="bi bi-google me-2"></i>
                                Sign in with Google
                            </span>
                        </button> -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
    email: '',
    password: ''
})

const error = ref('')
const googleLoading = ref(false)

// Google Sign-In configuration
const GOOGLE_CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID;

const handleLogin = async () => {
    error.value = ''

    const result = await authStore.login(form.value)

    if (result.success) {
        router.push('/dashboard')
    } else {
        error.value = result.message
    }
}

const handleGoogleLogin = async () => {
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
        error.value = 'Google sign-in failed. Please try again.'
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

        // Render the Google Sign-In button
        window.google.accounts.id.renderButton(
            document.getElementById('google-signin-button'),
            {
                theme: 'outline',
                size: 'large',
                width: '100%',
                text: 'signin_with',
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
    await loadGoogleScript()
    initializeGoogleSignIn()
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
#google-signin-button {
    min-height: 44px;
}

#google-signin-button>div {
    width: 100% !important;
}
</style>