<template>
    <div class="min-vh-100 bg-light d-flex flex-column">
        <header class="border-bottom bg-white sticky-top">
            <nav class="navbar navbar-expand-lg navbar-light py-2">
                <div class="container">
                    <router-link to="/" class="navbar-brand d-flex align-items-center">
                        <div class="me-2 d-flex align-items-center justify-content-center bg-success rounded-circle" style="width: 32px; height: 32px;">
                            <i class="bi bi-lightning text-white"></i>
                        </div>
                        <span class="fs-5 fw-bold text-dark">SheetExpress</span>
                    </router-link>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="mainNav">
                        <ul class="navbar-nav me-auto gap-lg-2">
                            <li class="nav-item"><router-link class="nav-link" to="/templates">Templates</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/features">Features</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/pricing">Pricing</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/#showcase">Showcase</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/blog">Blog</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/roadmap">Roadmap</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/changelog">Changelog</router-link></li>
                            <li class="nav-item"><router-link class="nav-link" to="/contact">Contact</router-link></li>
                        </ul>

                        <div class="d-flex align-items-center">
                            <template v-if="!auth.isAuthenticated">
                                <router-link to="/login" class="btn btn-link text-muted text-decoration-none me-2 me-lg-3">
                                    Sign In
                                </router-link>
                                <router-link to="/new-website" class="btn btn-success">
                                    Create Website
                                </router-link>
                            </template>
                            <template v-else>
                                <router-link to="/dashboard" class="btn btn-success">
                                    Dashboard
                                </router-link>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-grow-1">
            <slot />
        </main>
    </div>
    
</template>

<script setup>
import { useAuthStore } from '../stores/auth.js'

const auth = useAuthStore()
</script>

<style scoped>
.nav-link {
    color: #6c757d !important;
}
.nav-link.router-link-active {
    color: #198754 !important;
    font-weight: 600;
}
</style>