<template>
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ isEdit ? 'Edit page' : 'Create a new page' }}</h5>
                    <button type="button" class="btn-close" @click="onCancel"></button>
                </div>
                <form @submit.prevent="onSubmit">
                    <div class="modal-body">
                        <!-- Page Content Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Page content</h6>

                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input v-model="formData.title" type="text" class="form-control"
                                    placeholder="Add a page title" required>
                            </div>

                            <!-- <div class="mb-3">
                                <label class="form-label">Content Type <span class="text-danger">*</span></label>
                                <select v-model="formData.content_type" class="form-select" required>
                                    <option value="text">Text</option>
                                    <option value="google_doc">Google Doc</option>
                                </select>
                            </div> -->

                            <div class="mb-3">
                                <label class="form-label">Content (text, HTML, or a link to publish your Google Docs)
                                    <span class="text-danger">*</span></label>
                                <textarea v-if="formData.content_type === 'google_doc'" v-model="formData.content"
                                    class="form-control" rows="8" placeholder="<!-- ✅ Meta Title -->
<title>Khóa học Lập trình Web từ A đến Z | Hocmai</title>

<!-- ✅ Meta Description -->
<meta name=&quot;description&quot; content=&quot;Tham gia khóa học lập trình web từ cơ bản đến nâng cao, hướng dẫn chi tiết HTML, CSS, JavaScript và PHP. Phù hợp cho người mới bắt đầu.&quot; />

<!-- ✅ Open Graph (Facebook, Zalo, LinkedIn...) -->
<meta property=&quot;og:type&quot; content=&quot;website&quot; />
<meta property=&quot;og:title&quot; content=&quot;Khóa học Lập trình Web từ A đến Z | Hocmai&quot; />
<meta property=&quot;og:description&quot; content=&quot;Tham gia khóa học lập trình web từ cơ bản đến nâng cao, hướng dẫn chi tiết HTML, CSS, JavaScript và PHP.&quot; />
<meta property=&quot;og:image&quot; content=&quot;https://yourdomain.com/images/seo-thumbnail.jpg&quot; />
<meta property=&quot;og:url&quot; content=&quot;https://yourdomain.com/khoa-hoc-lap-trinh-web&quot; />
<meta property=&quot;og:site_name&quot; content=&quot;Hocmai&quot; />

<!-- ✅ Twitter Card -->
<meta name=&quot;twitter:card&quot; content=&quot;summary_large_image&quot; />
<meta name=&quot;twitter:title&quot; content=&quot;Khóa học Lập trình Web từ A đến Z | Hocmai&quot; />
<meta name=&quot;twitter:description&quot; content=&quot;Tham gia khóa học lập trình web từ cơ bản đến nâng cao, hướng dẫn chi tiết HTML, CSS, JavaScript và PHP.&quot; />
<meta name=&quot;twitter:image&quot; content=&quot;https://yourdomain.com/images/seo-thumbnail.jpg&quot; />"
                                    required></textarea>

                                <input v-else v-model="formData.content" type="text" class="form-control"
                                    placeholder="Link to public your Google Docs">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Page address <span class="text-danger">*</span></label>
                                <input v-model="formData.page_address" type="text" class="form-control"
                                    placeholder="page-address" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Page width</label>
                                <select v-model="formData.page_width" class="form-select">
                                    <option value="max-w-xl">XL</option>
                                    <option value="max-w-2xl">2XL</option>
                                    <option value="max-w-4xl">3XL</option>
                                    <option value="max-w-screen-lg">4XL</option>
                                    <option value="max-w-7xl">5XL</option>
                                </select>
                            </div>
                        </div>

                        <!-- Navigation Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Navigation</h6>

                            <div class="mb-3">
                                <label class="form-label">Menu title</label>
                                <input v-model="formData.menu_title" type="text" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Menu type</label>
                                <select v-model="formData.menu_type" class="form-select">
                                    <option value="1">Link</option>
                                    <option value="2">Button</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Open page in</label>
                                <select v-model="formData.target" class="form-select">
                                    <option value="1">Same tab</option>
                                    <option value="2">New tab</option>
                                </select>
                            </div>

                            <div class="form-check">
                                <input v-model="formData.show_in_header" class="form-check-input" type="checkbox"
                                    id="showInHeader">
                                <label class="form-check-label" for="showInHeader">
                                    Show page from header
                                </label>
                            </div>
                        </div>

                        <!-- SEO Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">SEO</h6>

                            <div class="mb-3">
                                <label class="form-label">Meta title</label>
                                <input v-model="formData.meta_title" type="text" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta description</label>
                                <textarea v-model="formData.meta_description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image for social media sharing</label>
                                <input v-model="formData.image_share_url" type="url" class="form-control"
                                    placeholder="Direct link to image">
                            </div>

                            <div class="form-check">
                                <input v-model="formData.show_in_search" class="form-check-input" type="checkbox"
                                    id="showInSearch">
                                <label class="form-check-label" for="showInSearch">
                                    Show page from search results
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="onCancel">Cancel</button>
                        <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                            {{ isEdit ? 'Update page' : 'Create a new page' }}
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue'

const props = defineProps({
    page: {
        type: Object,
        default: null
    },
    isEdit: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['cancel', 'submit'])

const isSubmitting = ref(false)

// Default form data
const defaultFormData = {
    title: '',
    content_type: 'text',
    content: '',
    page_address: '',
    page_width: 'max-w-2xl',
    menu_title: '',
    menu_type: '1',
    target: '1',
    show_in_header: true,
    meta_title: '',
    meta_description: '',
    image_share_url: '',
    show_in_search: true
}

// Form data with reactive state
const formData = ref({ ...defaultFormData })

// If editing, populate form with page data
onMounted(() => {
    if (props.isEdit && props.page) {
        formData.value = {
            ...props.page,
            show_in_header: props.page.show_in_header === 1,
            show_in_search: props.page.show_in_search === 1
        };
    }
})

const onSubmit = () => {
    isSubmitting.value = true
    emit('submit', formData.value)
}

const onCancel = () => {
    emit('cancel')
}
</script>
