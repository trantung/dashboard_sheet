<template>
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold text-dark mb-4">
                    Frequently asked questions
                </h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Discover all the answers to your most frequently asked questions about using SheetExpress.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div v-for="(faq, index) in faqs" :key="index" class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" :data-bs-toggle="`collapse`"
                                    :data-bs-target="`#faq${index}`" :aria-expanded="false"
                                    :aria-controls="`faq${index}`">
                                    {{ faq.question }}
                                </button>
                            </h2>
                            <div :id="`faq${index}`" class="accordion-collapse collapse"
                                :data-bs-parent="`#faqAccordion`">
                                <div class="accordion-body text-muted">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const faqs = [
    {
        question: "Is there a trial version available?",
        answer: "Yes, we offer a free trial that allows you to explore all features before committing to a paid plan."
    },
    {
        question: "Do I need any coding skills to use SheetExpress?",
        answer: "No coding skills required! SheetExpress is designed to be user-friendly and accessible to everyone, regardless of technical background."
    },
    {
        question: "Do I need to worry about hosting with SheetExpress?",
        answer: "Not at all! We handle all the hosting for you. Your websites are automatically deployed and maintained on our secure infrastructure."
    },
    {
        question: "How can I setup and manage blog posts?",
        answer: "Simply add your blog content to your Google Sheet, and SheetExpress will automatically generate and update your blog posts on your website."
    },
    {
        question: "Is the data updated continuously?",
        answer: "Yes, your website data syncs automatically with your Google Sheets, ensuring your content is always up-to-date."
    },
    {
        question: "Do you offer refunds?",
        answer: "We offer a 30-day money-back guarantee if you're not satisfied with our service."
    },
    {
        question: "Can I cancel my subscription at any time?",
        answer: "Yes, you can cancel your subscription at any time from your account dashboard. No long-term commitments required."
    },
    {
        question: "Is there a development roadmap for SheetExpress?",
        answer: "Yes, we regularly update our roadmap based on user feedback and feature requests. You can view our upcoming features on our roadmap page."
    },
    {
        question: "What is the difference between unlimited publish and custom domain?",
        answer: "Unlimited publish allows you to create and publish unlimited websites, while custom domain lets you use your own domain name instead of our subdomain."
    },
    {
        question: "Have more questions?",
        answer: "Feel free to contact our support team through the help center or email us directly. We're here to help!"
    }
]
</script>