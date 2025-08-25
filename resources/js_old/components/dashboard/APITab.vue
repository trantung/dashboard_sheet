<template>
    <div class="container-fluid">
        <div class="bg-white rounded p-4 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-3">
                <h5 class="mb-0">API Key</h5>
                <i class="bi bi-info-circle text-muted"></i>
            </div>
            <p class="text-muted mb-4">
                The API key serves as the primary method to authenticate your requests to the API. You can
                use it by passing it as a Bearer token in the authorization header.
            </p>
            <p class="text-muted mb-4">
                <code>"Authorization: Bearer secret_here"</code>
            </p>

            <div class="bg-light p-3 rounded mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-monospace">sk-1234567890abcdef...</span>
                    <button class="btn btn-success btn-sm">
                        <i class="bi bi-clipboard me-1"></i>
                        Copy
                    </button>
                </div>
            </div>

            <button class="btn btn-outline-danger btn-sm">
                Regenerate API Key
            </button>
        </div>
    </div>
</template>