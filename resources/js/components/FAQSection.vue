<template>
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-4 mb-md-5">
                <h3 class="fw-bold">Frequently asked questions</h3>
                <p class="text-muted">Answers to common questions about SheetExpress</p>
            </div>

            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqLanding">
                    <div v-for="(faq, i) in faqs" :key="i" class="accordion-item mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button"
                                    :data-bs-toggle="'collapse'" :data-bs-target="`#q${i}`">
                                {{ faq.q }}
                            </button>
                        </h2>
                        <div class="accordion-collapse collapse" :id="`q${i}`" :data-bs-parent="'#faqLanding'">
                            <div class="accordion-body text-muted">{{ faq.a }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const faqs = [
    { q: 'Is there a free plan or trial?', a: 'Yes, start free and upgrade anytime.' },
    { q: 'Do I need coding skills?', a: 'No. Everything is configurable with UI.' },
    { q: 'How do updates work?', a: 'We auto-sync from your Google Sheet.' },
    { q: 'Can I use a custom domain?', a: 'Yes on Pro plans, and subdomain is free.' },
]
</script>


