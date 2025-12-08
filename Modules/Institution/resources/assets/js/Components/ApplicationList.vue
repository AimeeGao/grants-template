<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    applications: Object,
    applicationStats: Object,
    filters: Object,
    canEdit: Boolean,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const programLevelFilter = ref(props.filters?.program_level || '');

const applyFilters = () => {
    router.get(route('institution.applications'), {
        status: statusFilter.value,
        program_level: programLevelFilter.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    programLevelFilter.value = '';
    applyFilters();
};

const getStatusBadgeClass = (status) => {
    const statusMap = {
        'draft': 'bg-secondary',
        'submitted': 'bg-primary',
        'under_review': 'bg-info',
        'additional_info_required': 'bg-warning',
        'approved': 'bg-success',
        'rejected': 'bg-danger',
        'withdrawn': 'bg-dark',
        'expired': 'bg-secondary',
    };
    return statusMap[status] || 'bg-secondary';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-CA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '-';
    return new Intl.NumberFormat('en-CA', {
        style: 'currency',
        currency: 'CAD'
    }).format(amount);
};

const selectedApplication = ref(null);
const showDetailModal = ref(false);

const viewApplication = (application) => {
    selectedApplication.value = application;
    showDetailModal.value = true;
};

const closeModal = () => {
    showDetailModal.value = false;
    selectedApplication.value = null;
};
</script>

<template>
    <div class="application-list">
        <h2 class="mb-4">Student Applications</h2>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Applications</h6>
                        <div class="h2 text-primary">{{ applicationStats?.total || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Pending Review</h6>
                        <div class="h2 text-warning">{{ applicationStats?.pending || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Approved</h6>
                        <div class="h2 text-success">{{ applicationStats?.approved || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Rejected</h6>
                        <div class="h2 text-danger">{{ applicationStats?.rejected || 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="form-control"
                            placeholder="Application ID or student name..."
                            @keyup.enter="applyFilters"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select v-model="statusFilter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="under_review">Under Review</option>
                            <option value="additional_info_required">Info Required</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Program Level</label>
                        <select v-model="programLevelFilter" class="form-select">
                            <option value="">All Levels</option>
                            <option value="undergraduate">Undergraduate</option>
                            <option value="graduate">Graduate</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button @click="applyFilters" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button @click="clearFilters" class="btn btn-outline-secondary">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="card-body">
                <div v-if="applications?.data?.length > 0" class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Student Name</th>
                                <th>Program Level</th>
                                <th>Program Name</th>
                                <th>Requested Amount</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="app in applications.data"
                                :key="app.id"
                                class="cursor-pointer"
                            >
                                <td>
                                    <strong>{{ app.application_number }}</strong>
                                </td>
                                <td>{{ app.student_name }}</td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">
                                        {{ app.program_level }}
                                    </span>
                                </td>
                                <td>{{ app.program_name || '-' }}</td>
                                <td>{{ formatCurrency(app.requested_amount) }}</td>
                                <td>{{ formatDate(app.submitted_at) }}</td>
                                <td>
                                    <span
                                        class="badge text-capitalize"
                                        :class="getStatusBadgeClass(app.status)"
                                    >
                                        {{ app.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        @click="viewApplication(app)"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="applications.links" class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                <li
                                    v-for="(link, index) in applications.links"
                                    :key="index"
                                    class="page-item"
                                    :class="{ active: link.active, disabled: !link.url }"
                                >
                                    <a
                                        v-if="link.url"
                                        :href="link.url"
                                        class="page-link"
                                        @click.prevent="router.visit(link.url)"
                                        v-html="link.label"
                                    ></a>
                                    <span v-else class="page-link" v-html="link.label"></span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <div v-else class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No applications found</p>
                </div>
            </div>
        </div>

        <!-- Application Detail Modal -->
        <div
            v-if="showDetailModal && selectedApplication"
            class="modal fade show d-block"
            tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"
            @click.self="closeModal"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Application Details - {{ selectedApplication.application_number }}
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Student Name:</strong>
                                <p>{{ selectedApplication.student_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    <span
                                        class="badge text-capitalize"
                                        :class="getStatusBadgeClass(selectedApplication.status)"
                                    >
                                        {{ selectedApplication.status.replace('_', ' ') }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Program Level:</strong>
                                <p class="text-capitalize">{{ selectedApplication.program_level }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Program Name:</strong>
                                <p>{{ selectedApplication.program_name || '-' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Requested Amount:</strong>
                                <p>{{ formatCurrency(selectedApplication.requested_amount) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Approved Amount:</strong>
                                <p>{{ formatCurrency(selectedApplication.approved_amount) }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Submitted Date:</strong>
                                <p>{{ formatDate(selectedApplication.submitted_at) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Reviewed Date:</strong>
                                <p>{{ formatDate(selectedApplication.reviewed_at) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedApplication.reviewer_name" class="row mb-3">
                            <div class="col-12">
                                <strong>Reviewed By:</strong>
                                <p>{{ selectedApplication.reviewer_name }}</p>
                            </div>
                        </div>

                        <div v-if="selectedApplication.review_notes" class="row mb-3">
                            <div class="col-12">
                                <strong>Review Notes:</strong>
                                <p>{{ selectedApplication.review_notes }}</p>
                            </div>
                        </div>

                        <div v-if="selectedApplication.rejection_reason" class="row mb-3">
                            <div class="col-12">
                                <strong>Rejection Reason:</strong>
                                <p class="text-danger">{{ selectedApplication.rejection_reason }}</p>
                            </div>
                        </div>

                        <div v-if="selectedApplication.student_notes" class="row mb-3">
                            <div class="col-12">
                                <strong>Student Notes:</strong>
                                <p>{{ selectedApplication.student_notes }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            Close
                        </button>
                        <button
                            v-if="canEdit && selectedApplication.status === 'submitted'"
                            type="button"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-pencil"></i> Review Application
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stats-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #dee2e6;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.1);
}

.cursor-pointer {
    cursor: pointer;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.text-primary {
    color: #003366 !important;
}

.modal.show {
    display: block;
}
</style>