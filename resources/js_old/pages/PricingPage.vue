<template>
    <GuestLayout>
        <!-- Hero -->
        <section class="py-5 bg-white border-bottom">
            <div class="container py-4 text-center">
                <h2 class="fw-bold mb-2">Plans & Pricing</h2>
                <p class="text-muted mx-auto" style="max-width: 720px;">
                    Choose the plan that's right for you or your company. 10 days money‑back guarantee.
                    Start for free today, upgrade later.
                </p>

                <div class="d-inline-flex border rounded-pill overflow-hidden mt-3">
                    <button class="btn btn-sm" :class="billing==='monthly' ? 'btn-success' : 'btn-light'" @click="billing='monthly'">Monthly</button>
                    <button class="btn btn-sm position-relative" :class="billing==='yearly' ? 'btn-success' : 'btn-light'" @click="billing='yearly'">
                        Yearly
                        <span class="badge bg-success position-absolute translate-middle-y ms-1" style="top: -6px; right: -6px;">Save 40%</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Plans -->
        <section class="py-4 py-md-5">
            <div class="container">
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div v-for="plan in plans" :key="plan.name" class="col-12 col-md-6 col-lg-3">
                        <div class="card h-100" :class="{ 'border-success': plan.popular }">
                            <div v-if="plan.popular" class="card-header bg-success text-white text-center py-2">
                                <small>Most Popular</small>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <h5 class="card-title">{{ plan.name }}</h5>
                                <div class="mb-2">
                                    <span class="h3">{{ plan.price[billing] }}</span>
                                    <span class="text-muted">/mo</span>
                                </div>
                                <p class="text-muted small mb-3">{{ plan.subtitle }}</p>
                                <button class="btn" :class="plan.popular ? 'btn-success' : 'btn-dark'" style="width: 100%;">Start a free trial →</button>

                                <ul class="list-unstyled small mt-3 mb-0">
                                    <li v-for="(f,i) in plan.features" :key="i" class="mb-2">
                                        <i :class="['bi', 'me-2', plan.popular ? 'bi-check-circle text-success' : 'bi-check-circle text-muted']"></i>
                                        {{ f }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />

                <!-- Lifetime Plan -->
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-6 col-xl-5">
                        <div class="card shadow-sm">
                            <div class="card-body p-3 p-md-4 text-center">
                                <h5 class="card-title">Agency Pro Lifetime</h5>
                                <div class="mb-2"><span class="h3">$999</span></div>
                                <p class="text-muted small mb-2">One-time payment</p>
                                <div class="alert alert-warning d-inline-flex align-items-center py-2 px-3 small mb-3">
                                    <i class="bi bi-fire me-2"></i>
                                    15 spots left ($1499 soon)
                                </div>
                                <button class="btn btn-primary w-100 mb-3">Claim Lifetime Access →</button>

                                <p class="small mb-2">Everything in <strong>Enterprise</strong>, and:</p>
                                <ul class="list-unstyled text-start small mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i><strong>Unlimited</strong> Custom domain</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i><strong>Unlimited</strong> Workspace</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i>Webhook and API</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i>Prioritize feature requests</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i>Highest support priority</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <CTASection />
        <FooterSection />
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import GuestLayout from '../layouts/GuestLayout.vue'
import CTASection from '../components/CTASection.vue'
import FooterSection from '../components/FooterSection.vue'

const billing = ref('monthly')

const baseFeatures = [
    '1 Custom domain',
    'SSL (HTTPS)',
    'Google Analytics',
    'Search, Filters & Sorting',
    'Collect Emails',
    'Optimized SEO',
]

const plans = [
    {
        name: 'Basic',
        subtitle: 'Perfect for getting started',
        price: { monthly: '$9', yearly: '$6' },
        features: baseFeatures,
        popular: false,
    },
    {
        name: 'Premium',
        subtitle: 'For individuals and small teams',
        price: { monthly: '$19', yearly: '$12' },
        features: ['4 Custom domain', ...baseFeatures],
        popular: false,
    },
    {
        name: 'Business',
        subtitle: 'For professional teams',
        price: { monthly: '$39', yearly: '$24' },
        features: ['10 Custom domain', ...baseFeatures],
        popular: true,
    },
    {
        name: 'Enterprise',
        subtitle: 'For large businesses',
        price: { monthly: '$69', yearly: '$42' },
        features: ['100 Custom domain', ...baseFeatures],
        popular: false,
    },
]
</script>

<style scoped>
.h3 { font-size: 1.75rem; }
@media (max-width: 767.98px) {
  .card-body { padding: 1rem; }
}
</style>


