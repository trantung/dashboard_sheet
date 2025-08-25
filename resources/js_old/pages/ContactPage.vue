<template>
    <GuestLayout>
        <section class="py-5">
            <div class="container py-4">
                <h1 class="fw-bold mb-3 text-center">Contact</h1>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Your email</label>
                                    <input type="email" class="form-control" placeholder="you@example.com"/>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" rows="5" placeholder="How can we help?"></textarea>
                                </div>
                                <button class="btn btn-success">Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '../layouts/GuestLayout.vue'
</script>


