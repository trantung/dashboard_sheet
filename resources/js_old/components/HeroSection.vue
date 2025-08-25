<template>
    <section class="hero-gradient py-5">
        <div class="container py-5">
            <div class="text-center">
                <div
                    class="d-inline-flex align-items-center bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-4">
                    <i class="bi bi-fire me-2"></i>
                    <small class="fw-medium">BIG SALE: Get 20% OFF Lifetime Access to Agency Pro — Use Code
                        BIG20</small>
                    <i class="bi bi-lightning ms-2"></i>
                </div>

                <h1 class="display-3 fw-bold text-dark mb-4">
                    Google Sheets
                    <i class="bi bi-table text-success mx-2"></i>
                    <br>
                    to <span class="text-success text-decoration-underline-custom">Website</span> in minutes
                </h1>

                <p class="lead text-muted mb-5 mx-auto" style="max-width: 600px;">
                    Sheetany is a website builder that helps you quickly create websites
                    directly from your Google Sheets without design or development skills, for
                    <strong>Blogs, Directories, Job boards, E-commerce sites</strong>, and more.
                </p>

                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
                    <button class="btn btn-success btn-lg px-4 py-3">
                        Create Your Website
                        <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-lg px-4 py-3">
                        <i class="bi bi-eye me-2"></i>
                        View Examples
                    </button>
                </div>

                <p class="text-muted mb-5">Start for free. No credit card required.</p>

                <!-- Stats -->
                <div class="row text-center mb-5">
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-dark">2800+</div>
                        <div class="text-muted">Happy customers</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-dark">25+</div>
                        <div class="text-muted">Templates</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-dark">4200+</div>
                        <div class="text-muted">Websites created</div>
                    </div>
                </div>

                <!-- Customer avatars and rating -->
                <div class="d-flex flex-column align-items-center">
                    <div class="d-flex mb-3" style="margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 1"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 2"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 3"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 4"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 5"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                        <img class="rounded-circle border border-white border-3"
                            :src="doan" alt="Customer 6"
                            style="width: 40px; height: 40px; margin-left: -8px;">
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-warning me-2">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <span class="text-muted">Loved by 2800+ customers</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import doan from '@images/doan.webp'
// Hero section component
</script>