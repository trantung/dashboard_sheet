<template>
    <div>
        <NotificationAlert ref="notificationAlert" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">General settings</h4>
                <p class="text-muted mb-0">Customize the displayed information on your website</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Name</h6>
                        <p class="text-muted small">Website name displayed in the dashboard</p>
                        <input v-model="websiteName" type="text" class="form-control" placeholder="Test Web">
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Show dark mode</h6>
                                <p class="text-muted small mb-0">Display the dark mode button on your website menu</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="showDarkMode" class="form-check-input" type="checkbox" id="darkMode">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Hide footer</h6>
                                <p class="text-muted small mb-0">Hide your website's footer</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="hideFooter" class="form-check-input" type="checkbox" id="hideFooter">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Show collected emails</h6>
                                <p class="text-muted small mb-0">Show block to collect email information from registered
                                    users</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="showCollectedEmails" class="form-check-input" type="checkbox"
                                    id="showEmails">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Disable auto-sync</h6>
                                <p class="text-muted small mb-0">Turn off automatic data updates from Google Sheets</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="disableAutoSync" class="form-check-input" type="checkbox" id="autoSync">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Text center</h6>
                                <p class="text-muted small mb-0">Display the title and information at the center of the
                                    pages</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="textCenter" class="form-check-input" type="checkbox" id="textCenter">
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isEcommerce || isBlog" class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Detail page</h6>
                                <p class="text-muted small mb-0">Hide the website detail page</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="disableDetailPage" class="form-check-input" type="checkbox" id="disableDetailPage">
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isEcommerce || isBlog" class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Disable website indexing</h6>
                                <p class="text-muted small mb-0">Prevent search engines from indexing your website pages</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="disableIndex" class="form-check-input" type="checkbox" id="disableIndex">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div v-if="isEcommerce" class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Disable banner</h6>
                                <p class="text-muted small mb-0">Hide the banner section on your website</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="disableBanner" class="form-check-input" type="checkbox" id="disableBanner">
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Font Family</h6>
                        <p class="text-muted small">Display font for website</p>
                        <select v-model="fontFamily" class="form-select">
                            <option value="Arvo">Arvo</option>
                            <option value="Cabin">Cabin</option>
                            <option value="Droid Serif">Droid Serif</option>
                            <option value="Fira Sans">Fira Sans</option>
                            <option value="Inconsolata">Inconsolata</option>
                            <option value="Inter">Inter</option>
                            <option value="IBM Plex Sans Thai">IBM Plex Sans Thai</option>
                            <option value="Lora">Lora</option>
                            <option value="Merriweather">Merriweather</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Manrope">Manrope</option>
                            <option value="Noto Sans">Noto Sans</option>
                            <option value="Nunito">Nunito</option>
                            <option value="Open Sans">Open Sans</option>
                            <option value="Playfair Display">Playfair Display</option>
                            <option value="Poppins">Poppins</option>
                            <option value="Public Sans">Public Sans</option>
                            <option value="Quicksand">Quicksand</option>
                            <option value="Raleway">Raleway</option>
                            <option value="Roboto">Roboto</option>
                            <option value="Source Sans Pro">Source Sans Pro</option>
                            <option value="Ubuntu">Ubuntu</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">
                                    Build on SheetExpress
                                    <!-- Thay số 3 bằng icon khóa -->
                                    <span class="badge bg-dark ms-2"><i class="bi bi-lock-fill text-white"></i> </span>
                                </h6>
                                <p class="text-muted small mb-0">Display the Build on SheetExpress badge on your website</p>
                            </div>
                            <div class="form-check form-switch">
                                <input 
                                    v-model="buildOnSheetany" 
                                    class="form-check-input" 
                                    type="checkbox"
                                    id="buildOnSheetany"
                                    disabled="true"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Hide header</h6>
                                <p class="text-muted small mb-0">Hide your website's header</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="hideHeader" class="form-check-input" type="checkbox" id="hideHeader">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Hide hero</h6>
                                <p class="text-muted small mb-0">Hide your website's hero section</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="hideHero" class="form-check-input" type="checkbox" id="hideHero">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Show the About Us page</h6>
                                <p class="text-muted small mb-0">Display the About Us page using data updated in Google
                                    Sheets</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="showAboutUs" class="form-check-input" type="checkbox" id="showAboutUs">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Show feedback form</h6>
                                <p class="text-muted small mb-0">Display the feedback page for users to enter
                                    information</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="showFeedbackForm" class="form-check-input" type="checkbox"
                                    id="showFeedbackForm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Table of Content</h6>
                                <p class="text-muted small mb-0">Hide the table of content on detail pages</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="disableTOC" class="form-check-input" type="checkbox" id="disableTOC">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Font size</h6>
                                <p class="text-muted small mb-0">Display small text title</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="fontSize" class="form-check-input" type="checkbox" id="fontSize">
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isBlog" class="card border-1 mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Grid content</h6>
                        <p class="text-muted small">Display the number of content columns</p>
                        <select v-model="gridContent" class="form-select">
                            <option value="2">2 columns</option>
                            <option value="3">3 columns</option>
                        </select>
                    </div>
                </div>

                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Pagination Size</h6>
                        <p class="text-muted small">Set the number of data items to display on the website. It should be
                            between 10 and 50.</p>
                        <input v-model="paginationSize" type="number" class="form-control" min="10" max="50"
                            placeholder="10">
                    </div>
                </div>
                
                <div class="card border-1 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Publish website</h6>
                                <p class="text-muted small mb-0">Publish your website to the internet</p>
                            </div>
                            <div class="form-check form-switch">
                                <input v-model="publishWebsite" class="form-check-input" type="checkbox"
                                    id="publishWebsite">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button class="btn btn-success" @click="updateSettings" :disabled="isUpdating">
                <span v-if="isUpdating" class="spinner-border spinner-border-sm" role="status"
                    aria-hidden="true"></span>
                {{ isUpdating ? 'Updating...' : 'Update setting' }}
                <i v-if="!isUpdating" class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>

        <div class="card border-danger">
            <div class="card-body">
                <h6 class="card-title text-danger">Danger zone</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Delete website</h6>
                        <p class="text-muted small mb-0">This action will remove the website from your account</p>
                    </div>
                    <button class="btn btn-outline-danger btn-sm" @click="confirmDeleteWebsite">
                        <span v-if="isDeleting" class="spinner-border spinner-border-sm" role="status"
                            aria-hidden="true"></span>
                        <i v-if="!isDeleting" class="bi bi-trash me-1"></i>
                        {{ isDeleting ? 'Deleting...' : 'Delete website' }}
                    </button>
                </div>
            </div>
        </div>

        <ConfirmDialog v-if="showDeleteConfirm" :show="showDeleteConfirm" title="Delete this website?"
            message="Deleting this website will permanently remove it. This action cannot be undone."
            type="danger" confirmText="Delete" @cancel="showDeleteConfirm = false" @confirm="deleteWebsite" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router' // Import useRouter
import axios from 'axios'
import { route as ziggyRoute } from 'ziggy-js'
import NotificationAlert from '@/components/NotificationAlert.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const route = useRoute()
const router = useRouter() // Khởi tạo useRouter
const notificationAlert = ref(null)

const isLoading = ref(true) // Trạng thái loading ban đầu
const isUpdating = ref(false) // Trạng thái loading cho nút update
const isDeleting = ref(false) // Trạng thái loading cho nút delete

const websiteId = ref(null) // Biến để lưu trữ websiteId

const websiteName = ref('')
const siteType = ref(1)
const isBlog = computed(() => Number(siteType.value) === 1)
const isEcommerce = computed(() => Number(siteType.value) === 2)
const showDarkMode = ref(true)
const hideFooter = ref(false)
const hideHeader = ref(false)
const hideHero = ref(false)
const showCollectedEmails = ref(true)
const disableAutoSync = ref(false)
const textCenter = ref(false)
const showAboutUs = ref(true)
const showFeedbackForm = ref(false)
const fontSize = ref(false)
const disableDetailPage = ref(false)
const disableIndex = ref(false)
// const disableBanner = ref(false)
const disableTOC = ref(false)
const fontFamily = ref('Poppins')
const paginationSize = ref(10)
const publishWebsite = ref(true)
const buildOnSheetany = ref(true) // Đảm bảo ref này được khởi tạo và sử dụng
const gridContent = ref(3)

const showDeleteConfirm = ref(false)

const fetchWebsiteInfo = async () => {
    isLoading.value = true;
    try {
        const id = route.params.id;
        if (!id) {
            notificationAlert.value?.showError('Website ID not found in URL.', 'Error');
            isLoading.value = false;
            return;
        }
        websiteId.value = id; // Lưu websiteId vào ref

        const response = await axios.get(ziggyRoute('api.sites.show', { id }));

        if (response.data) {
            siteType.value = response.data.type ?? 1;
            websiteName.value = response.data.name || '';
            showDarkMode.value = response.data.dark_mode == 1 || false;
            hideFooter.value = response.data.hide_footer == 2 || false;
            hideHeader.value = response.data.hide_header == 2 || false;
            hideHero.value = response.data.disable_hero == 1 || false; // Dựa trên response.data.disable_hero
            showCollectedEmails.value = response.data.collect_email == 1 || false;
            disableAutoSync.value = response.data.disable_auto_sync == 1 || false;
            textCenter.value = response.data.text_center == 1 || false;
            showAboutUs.value = response.data.about_us == 1 || false;
            showFeedbackForm.value = response.data.feedback_form == 1 || false;
            fontSize.value = response.data.small_hero == 1 || false; // Dựa trên response.data.small_hero
            disableDetailPage.value = response.data.disable_detail_page == 1 || false;
            disableIndex.value = response.data.disable_index == 1 || false;
            disableTOC.value = response.data.disable_toc == 1 || false;
            // disableBanner.value = response.data.disable_banner == 1 || false;
            fontFamily.value = response.data.font_family || 'Poppins';
            paginationSize.value = response.data.pagination_size || 10;
            publishWebsite.value = response.data.published == 1 || false;
            buildOnSheetany.value = response.data.build_on_sheetany == 1 || false;
            gridContent.value = response.data.grid_content || 3;

            notificationAlert.value?.showSuccess('Website information loaded successfully.');
        } else {
            notificationAlert.value?.showWarning('No website information found.');
        }
    } catch (error) {
        console.error('Failed to fetch website info:', error);
        if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to access this website', 'Access Denied');
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found', 'Not Found');
        } else {
            notificationAlert.value?.showError('Failed to load general settings', 'Loading Error');
        }
    } finally {
        isLoading.value = false;
    }
};

// Hàm để cập nhật cài đặt chung
const updateSettings = async () => {
    if (!websiteId.value) {
        notificationAlert.value?.showError('Website ID is missing. Cannot update settings.', 'Error');
        return;
    }

    isUpdating.value = true;
    try {
        const payload = {
            name: websiteName.value,
            dark_mode: showDarkMode.value ? 1 : 2,
            hide_footer: hideFooter.value ? 2 : 1,
            hide_header: hideHeader.value ? 2 : 1,
            disable_hero: hideHero.value ? 1 : 2, // Mapping to disable_hero
            collect_email: showCollectedEmails.value ? 1 : 2,
            disable_auto_sync: disableAutoSync.value ? 1 : 2,
            text_center: textCenter.value ? 1 : 2,
            about_us: showAboutUs.value ? 1 : 2,
            feedback_form: showFeedbackForm.value ? 1 : 2,
            small_hero: fontSize.value ? 1 : 2, // Mapping to small_hero
            font_family: fontFamily.value,
            pagination_size: paginationSize.value,
            published: publishWebsite.value ? 1 : 2,
            build_on_sheetany: buildOnSheetany.value ? 1 : 2,
            grid_content: gridContent.value,
            disable_detail_page: disableDetailPage.value ? 1 : 2,
            disable_index: disableIndex.value ? 1 : 2,
            disable_toc: disableTOC.value ? 1 : 2,
        };

        if (isEcommerce.value) {
            // payload.disable_banner = disableBanner.value ? 1 : 2;
        }

        const response = await axios.put(ziggyRoute('api.sites.setting.update', { site: websiteId.value }), payload);

        if (response.data.success) {
            notificationAlert.value?.showSuccess(response.data.message || 'Website settings updated successfully!');
        } else {
            notificationAlert.value?.showError(response.data.message || 'Failed to update settings.', 'Update Error');
        }
    } catch (error) {
        console.error('Failed to update settings:', error);
        if (error.response?.data?.message) {
            notificationAlert.value?.showError(error.response.data.message, 'Update Error');
        } else if (error.response?.status === 422) {
            notificationAlert.value?.showError('Validation failed. Please check your input.', 'Validation Error');
        } else {
            notificationAlert.value?.showError('An unexpected error occurred during update.', 'Error');
        }
    } finally {
        isUpdating.value = false;
    }
};

const confirmDeleteWebsite = () => {
    showDeleteConfirm.value = true
}

// Hàm để xóa trang web
const deleteWebsite = async () => {
    if (!websiteId.value) {
        notificationAlert.value?.showError('Website ID is missing. Cannot delete website.', 'Error');
        return;
    }

    isDeleting.value = true;
    try {
        const response = await axios.delete(ziggyRoute('api.sites.destroy', { site: websiteId.value }));

        if (response.data.success) {
            notificationAlert.value?.showSuccess(response.data.message || 'Website deleted successfully!');
            // Chuyển hướng người dùng đến trang quản lý tổng thể hoặc dashboard
            router.push('/dashboard/websites')
        } else {
            notificationAlert.value?.showError(response.data.message || 'Failed to delete website.', 'Deletion Error');
        }
    } catch (error) {
        console.error('Failed to delete website:', error);
        if (error.response?.data?.message) {
            notificationAlert.value?.showError(error.response.data.message, 'Deletion Error');
        } else if (error.response?.status === 403) {
            notificationAlert.value?.showError('You do not have permission to delete this website', 'Access Denied');
        } else if (error.response?.status === 404) {
            notificationAlert.value?.showError('Website not found', 'Not Found');
        } else {
            notificationAlert.value?.showError('An unexpected error occurred during deletion.', 'Error');
        }
    } finally {
        isDeleting.value = false;
    }
};


onMounted(() => {
    fetchWebsiteInfo();
});
</script>