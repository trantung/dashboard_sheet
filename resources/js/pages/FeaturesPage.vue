<template>
    <GuestLayout>
        <!-- Hero -->
        <section class="py-5 bg-white border-bottom">
            <div class="container py-4 text-center">
                <h2 class="fw-bold mb-2">Features</h2>
                <p class="text-muted mb-3">Key features of SheetExpress</p>
                <router-link to="/new-website" class="btn btn-success">Create Your Website →</router-link>
            </div>
        </section>

        <!-- Features grid -->
        <section class="py-4 py-md-5">
            <div class="container">
                <div class="row g-4">
                    <div v-for="(f, i) in features" :key="i" class="col-12 col-md-6 col-lg-4">
                        <div class="d-flex">
                            <div class="icon-wrap me-3 flex-shrink-0">
                                <div class="rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i :class="['bi', f.icon, 'text-success']"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ f.title }}</h6>
                                <p class="text-muted small mb-0">{{ f.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Request feature -->
        <section class="py-5 bg-white border-top">
            <div class="container text-center">
                <h6 class="fw-bold mb-2">Want to add another feature?</h6>
                <p class="text-muted mb-3">Please request the feature you want on the Roadmap.</p>
                <router-link to="/roadmap" class="btn btn-outline-success">I want to add another feature</router-link>
            </div>
        </section>

        <CTASection />
        <FooterSection />
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '../layouts/GuestLayout.vue'
import CTASection from '../components/CTASection.vue'
import FooterSection from '../components/FooterSection.vue'

const features = [
  { icon: 'bi-arrow-repeat', title: 'Real-time updates', desc: 'Automatically sync Google Sheets changes to your website.' },
  { icon: 'bi-graph-up', title: 'SEO optimization', desc: 'Meta tags, favicon, open graph, and preview images for SEO.' },
  { icon: 'bi-shield-lock', title: 'HTTPS/SSL support', desc: 'Free SSL certificates for every website.' },

  { icon: 'bi-globe2', title: 'Custom domains', desc: 'Use your own domain or a free SheetExpress subdomain.' },
  { icon: 'bi-people', title: 'Multi Workspaces', desc: 'Invite team members to collaborate and manage websites easily.' },
  { icon: 'bi-file-earmark-text', title: 'Item details page', desc: 'Create dedicated pages for individual items.' },

  { icon: 'bi-window', title: 'Embed to external website', desc: 'Integrate your content via iframes.' },
  { icon: 'bi-funnel', title: 'Filters and categories', desc: 'Filter lists by one or multiple criteria.' },
  { icon: 'bi-sort-alpha-down', title: 'Sorting, search, and pagination', desc: 'Help visitors explore your content effortlessly.' },

  { icon: 'bi-file-spreadsheet', title: 'Google Sheets integration', desc: 'Make updates in Sheets, verify changes, and publish instantly.' },
  { icon: 'bi-activity', title: 'Google Analytics', desc: 'Connect Google Analytics to track performance.' },
  { icon: 'bi-journal-text', title: 'Blog management', desc: 'Write blogs in Google Docs and manage posts via Sheets.' },

  { icon: 'bi-envelope-paper', title: 'Email collection', desc: 'Collect visitor emails directly from your website.' },
  { icon: 'bi-magic', title: 'SEO auto-optimization', desc: 'Auto-optimize your website for better SEO performance.' },
  { icon: 'bi-grid-1x2', title: 'Custom templates', desc: 'Customize grids, lists, forms, and content display.' },

  { icon: 'bi-ui-checks', title: 'Table of contents', desc: 'Auto generate a table of contents for your content.' },
  { icon: 'bi-palette2', title: 'Themes', desc: 'Switch dark/light, customize color themes.' },
  { icon: 'bi-collection', title: 'Multiple templates', desc: 'Access a variety of ready‑to‑use templates.' },

  { icon: 'bi-chat-left-text', title: 'Feedback page', desc: 'Let users submit feedback through a dedicated page.' },
  { icon: 'bi-person-badge', title: 'About us page', desc: 'Display an About page with data from Google Sheets.' },
  { icon: 'bi-ui-radios', title: 'Content submission form', desc: 'Include a form for users to submit content to your site.' },

  { icon: 'bi-lightning', title: 'Lightning fast', desc: 'Optimized for page speed; great scores out of the box.' },
  { icon: 'bi-star', title: 'Featured cards', desc: 'Highlight certain items on your list to make them stand out.' },
  { icon: 'bi-cookie', title: 'Cookie banner', desc: 'Build trust between visitors and your website.' },

  { icon: 'bi-ticket-perforated', title: 'Promo codes', desc: 'Manage coupon codes and shipping.' },
  { icon: 'bi-badge-ad', title: 'Ads', desc: 'Manage advertising through banners.' },
  { icon: 'bi-substack', title: 'Free subdomain', desc: 'Get a unique subdomain on our default domain.' },

  { icon: 'bi-lock', title: 'Password protection', desc: 'Limit access to your website with passwords.' },
  { icon: 'bi-braces', title: 'Custom code', desc: 'Inject custom JS/CSS anywhere on your site.' },
  { icon: 'bi-type', title: 'Multi font', desc: 'Choose and change fonts for your website.' },

  { icon: 'bi-rss', title: 'RSS feed', desc: 'Create RSS feeds for your website in a few clicks.' },
  { icon: 'bi-diagram-3', title: 'Sitemap', desc: 'Auto‑generated sitemap that includes all pages.' },
  { icon: 'bi-paypal', title: 'PayPal checkout', desc: 'Fast and secure payments (coming soon).' },

  { icon: 'bi-stripe', title: 'Stripe checkout', desc: 'Popular secure checkout (coming soon).' },
  { icon: 'bi-emoji-smile', title: 'Integrate Lemon Squeezy', desc: 'Adaptive checkout for your customer’s device and location.' },
  { icon: 'bi-google', title: 'Integrate Google AdSense', desc: 'Connect AdSense to earn money with ads.' },

  { icon: 'bi-envelope', title: 'Email notification', desc: 'Receive email notifications for new orders.' },
  { icon: 'bi-diagram-2', title: 'Webhook', desc: 'Trigger actions by webhook.' },
  { icon: 'bi-braces-asterisk', title: 'API', desc: 'Create and manage websites via API.' },

  { icon: 'bi-list', title: 'Navbar', desc: 'Customize the display of the menu list.' },
  { icon: 'bi-bar-chart', title: 'Google Tag Manager', desc: 'Manage and deploy tracking tags without touching code.' },
]
</script>

<style scoped>
.icon-wrap i{ font-size: 1.1rem; }
@media (max-width: 767.98px){
  .icon-wrap div{ width:36px; height:36px; }
}
</style>


