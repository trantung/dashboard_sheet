import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth.js";
import LandingPage from "../pages/LandingPage.vue";
import LoginPage from "../pages/LoginPage.vue";
import DashboardPage from "../pages/DashboardPage.vue";
import NewWebsitePage from "../pages/NewWebsitePage.vue";
import WebsiteAddPage from "../pages/WebsiteAddPage.vue";
import WebsiteManagePage from "../pages/WebsiteManagePage.vue"
import GoogleCallbackPage from "../pages/GoogleCallbackPage.vue"
import TemplatesPage from "../pages/TemplatesPage.vue"

// Marketing pages
const FeaturesPage = () => import('../pages/FeaturesPage.vue')
const PricingPage = () => import('../pages/PricingPage.vue')
const ShowcasePage = () => import('../pages/ShowcasePage.vue')
const BlogPage = () => import('../pages/BlogPage.vue')
const RoadmapPage = () => import('../pages/RoadmapPage.vue')
const ChangelogPage = () => import('../pages/ChangelogPage.vue')
const ContactPage = () => import('../pages/ContactPage.vue')

import DashboardTab from '../components/website/DashboardTab.vue'
import InformationSheetTab from '../components/website/InformationSheetTab.vue'
import ContentSheetTab from '../components/website/ContentSheetTab.vue'
import CustomDomainTab from '../components/website/CustomDomainTab.vue'
import CustomCodeTab from '../components/website/CustomCodeTab.vue'
import GeneralSettingsTab from '../components/website/GeneralSettingsTab.vue'
import IntegrationsTab from '../components/website/IntegrationsTab.vue'
import NavbarTab from '../components/website/NavbarTab.vue'
import PagesTab from '../components/website/PagesTab.vue'
import EmailsTab from '../components/website/EmailsTab.vue'
import OrdersTab from '../components/website/OrdersTab.vue'
import FeedbacksTab from '../components/website/FeedbacksTab.vue'
import WebhooksTab from '../components/website/WebhooksTab.vue'
import SitemapTab from '../components/website/SitemapTab.vue'
import RSSTab from '../components/website/RSSTab.vue'

const routes = [
    {
        path: "/",
        name: "Landing",
        component: LandingPage,
    },
    {
        path: "/login",
        name: "Login",
        component: LoginPage,
        meta: { requiresGuest: true },
    },
    // Marketing routes
    { path: "/templates", name: "Templates", component: TemplatesPage },
    { path: "/features", name: "Features", component: FeaturesPage },
    { path: "/pricing", name: "Pricing", component: PricingPage },
    { path: "/showcase", name: "Showcase", component: ShowcasePage },
    { path: "/blog", name: "Blog", component: BlogPage },
    { path: "/roadmap", name: "Roadmap", component: RoadmapPage },
    { path: "/changelog", name: "Changelog", component: ChangelogPage },
    { path: "/contact", name: "Contact", component: ContactPage },
    {
        path: "/register",
        name: "Register",
        component: () => import("../pages/RegisterPage.vue"),
        meta: { requiresGuest: true },
    },
    {
      path: "/auth/google/callback",
      name: "GoogleCallback",
      component: GoogleCallbackPage,
      meta: { requiresGuest: true },
    },
    {
        path: "/dashboard",
        name: "Dashboard",
        component: DashboardPage,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                redirect: "/dashboard/websites",
            },
            {
                path: "profile",
                name: "DashboardProfile",
                component: DashboardPage,
                meta: { tab: "profile" },
            },
            {
                path: "websites",
                name: "DashboardWebsites",
                component: DashboardPage,
                meta: { tab: "websites" },
            },
            {
                path: "files",
                name: "DashboardFiles",
                component: DashboardPage,
                meta: { tab: "files" },
            },
            {
                path: "pricing",
                name: "DashboardPricing",
                component: DashboardPage,
                meta: { tab: "pricing" },
            },
            {
                path: "workspaces",
                name: "DashboardWorkspaces",
                component: DashboardPage,
                meta: { tab: "workspaces" },
            },
            {
                path: "api",
                name: "DashboardAPI",
                component: DashboardPage,
                meta: { tab: "api" },
            },
        ],
    },
    {
        path: "/new-website",
        name: "NewWebsite",
        component: NewWebsitePage,
        meta: { requiresAuth: true },
    },
    {
        path: "/website/add/:templateId",
        name: "WebsiteAdd",
        component: WebsiteAddPage,
        meta: { requiresAuth: true },
    },
    // Thêm route mới vào routes array
    {
        path: "/website/:id/manage",
        component: WebsiteManagePage,
        props: true,
        meta: { requiresAuth: true },
        children: [
            { path: "", redirect: "dashboard" }, // default tab

            { path: "dashboard", name: "WebsiteDashboardTab", component: DashboardTab },
            { path: "information", name: "WebsiteInformationTab", component: InformationSheetTab },
            { path: "content", name: "WebsiteContentTab", component: ContentSheetTab },
            { path: "domain", name: "WebsiteDomainTab", component: CustomDomainTab },
            { path: "code", name: "WebsiteCodeTab", component: CustomCodeTab },
            { path: "settings", name: "WebsiteSettingsTab", component: GeneralSettingsTab },
            { path: "integrations", name: "WebsiteIntegrationsTab", component: IntegrationsTab },
            { path: "navbar", name: "WebsiteNavbarTab", component: NavbarTab },
            { path: "pages", name: "WebsitePagesTab", component: PagesTab },
            { path: "emails", name: "WebsiteEmailsTab", component: EmailsTab },
            { path: "orders", name: "WebsiteOrdersTab", component: OrdersTab },
            { path: "feedbacks", name: "WebsiteFeedbacksTab", component: FeedbacksTab },
            { path: "webhooks", name: "WebsiteWebhooksTab", component: WebhooksTab },
            { path: "sitemap", name: "WebsiteSitemapTab", component: SitemapTab },
            { path: "rss", name: "WebsiteRSSTab", component: RSSTab },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Keep native back/forward behavior
        if (savedPosition) return savedPosition
        // Hash anchor scrolling (e.g., /#showcase)
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
                top: 80 // offset for sticky header
            }
        }
        return { top: 0 }
    }
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (!authStore.isAuthChecked) {
        await authStore.checkAuth(); // Đợi xác thực
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next("/login");
    } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next("/dashboard");
    } else {
        next();
    }
});

export default router;
