<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    attestations: Object,
    attestationStats: Object,
    filters: Object,
    canEdit: Boolean,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const typeFilter = ref(props.filters?.attestation_type || '');

const applyFilters = () => {
    router.get(route('institution.attestations'), {
        status: statusFilter.value,
        attestation_type: typeFilter.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    applyFilters();
};

const getStatusBadgeClass = (status, isExpired) => {
    if (isExpired) {
        return 'bg-secondary';
    }

    const statusMap = {
        'issued': 'bg-success',
        'declined': 'bg-danger',
        'expired': 'bg-secondary',
        'revoked': 'bg-warning',
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

const selectedAttestation = ref(null);
const showDetailModal = ref(false);

const viewAttestation = (attestation) => {
    selectedAttestation.value = attestation;
    showDetailModal.value = true;
};

const closeModal = () => {
    showDetailModal.value = false;
    selectedAttestation.value = null;
};
</script>

<template>
    <div class="attestation-list">
        <h2 class="mb-4">Attestations (PALs)</h2>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Graduate Attestations</h6>
                        <div class="row">
                            <div class="col-6 text-center border-end">
                                <small class="d-block text-muted">Issued</small>
                                <div class="h3 text-success mb-0">{{ attestationStats?.gradIssued || 0 }}</div>
                            </div>
                            <div class="col-6 text-center">
                                <small class="d-block text-muted">Declined</small>
                                <div class="h3 text-danger mb-0">{{ attestationStats?.gradDeclined || 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Undergrad Attestations</h6>
                        <div class="row">
                            <div class="col-6 text-center border-end">
                                <small class="d-block text-muted">Issued</small>
                                <div class="h3 text-success mb-0">{{ attestationStats?.undergradIssued || 0 }}</div>
                            </div>
                            <div class="col-6 text-center">
                                <small class="d-block text-muted">Declined</small>
                                <div class="h3 text-danger mb-0">{{ attestationStats?.undergradDeclined || 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Attestations</h6>
                        <div class="h2 text-primary">{{ attestationStats?.total || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Issued</h6>
                        <div class="h2 text-success">
                            {{ (attestationStats?.gradIssued || 0) + (attestationStats?.undergradIssued || 0) }}
                        </div>
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
                            placeholder="Attestation number or student name..."
                            @keyup.enter="applyFilters"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select v-model="statusFilter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="issued">Issued</option>
                            <option value="declined">Declined</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select v-model="typeFilter" class="form-select">
                            <option value="">All Types</option>
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

        <!-- Attestations Table -->
        <div class="card">
            <div class="card-body">
                <div v-if="attestations?.data?.length > 0" class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Attestation Number</th>
                                <th>Student Name</th>
                                <th>Type</th>
                                <th>Program Name</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="attestation in attestations.data"
                                :key="attestation.id"
                                class="cursor-pointer"
                            >
                                <td>
                                    <strong>{{ attestation.attestation_number }}</strong>
                                </td>
                                <td>{{ attestation.student_name }}</td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">
                                        {{ attestation.attestation_type }}
                                    </span>
                                </td>
                                <td>{{ attestation.program_name || '-' }}</td>
                                <td>{{ formatDate(attestation.issue_date) }}</td>
                                <td>
                                    <span
                                        v-if="attestation.expiry_date"
                                        :class="{
                                            'text-danger': attestation.is_expiring_soon,
                                            'text-muted': !attestation.is_expiring_soon
                                        }"
                                    >
                                        {{ formatDate(attestation.expiry_date) }}
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td>
                                    <span
                                        class="badge text-capitalize"
                                        :class="getStatusBadgeClass(attestation.status, attestation.is_expired)"
                                    >
                                        {{ attestation.is_expired ? 'Expired' : attestation.status }}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        @click="viewAttestation(attestation)"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="attestations.links" class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                <li
                                    v-for="(link, index) in attestations.links"
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
                    <i class="bi bi-award display-1 text-muted"></i>
                    <p class="text-muted mt-3">No attestations found</p>
                </div>
            </div>
        </div>

        <!-- Attestation Detail Modal -->
        <div
            v-if="showDetailModal && selectedAttestation"
            class="modal fade show d-block"
            tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"
            @click.self="closeModal"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Attestation Details - {{ selectedAttestation.attestation_number }}
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
                                <p>{{ selectedAttestation.student_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    <span
                                        class="badge text-capitalize"
                                        :class="getStatusBadgeClass(selectedAttestation.status, selectedAttestation.is_expired)"
                                    >
                                        {{ selectedAttestation.is_expired ? 'Expired' : selectedAttestation.status }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Attestation Type:</strong>
                                <p class="text-capitalize">{{ selectedAttestation.attestation_type }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Program Name:</strong>
                                <p>{{ selectedAttestation.program_name || '-' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Issue Date:</strong>
                                <p>{{ formatDate(selectedAttestation.issue_date) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Expiry Date:</strong>
                                <p>{{ formatDate(selectedAttestation.expiry_date) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedAttestation.issuer_name" class="row mb-3">
                            <div class="col-12">
                                <strong>Issued By:</strong>
                                <p>{{ selectedAttestation.issuer_name }}</p>
                            </div>
                        </div>

                        <div v-if="selectedAttestation.program_start_date" class="row mb-3">
                            <div class="col-md-6">
                                <strong>Program Start Date:</strong>
                                <p>{{ formatDate(selectedAttestation.program_start_date) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Program End Date:</strong>
                                <p>{{ formatDate(selectedAttestation.program_end_date) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedAttestation.decline_reason" class="row mb-3">
                            <div class="col-12">
                                <strong>Decline Reason:</strong>
                                <p class="text-danger">{{ selectedAttestation.decline_reason }}</p>
                            </div>
                        </div>

                        <div v-if="selectedAttestation.revocation_reason" class="row mb-3">
                            <div class="col-12">
                                <strong>Revocation Reason:</strong>
                                <p class="text-warning">{{ selectedAttestation.revocation_reason }}</p>
                            </div>
                            <div v-if="selectedAttestation.revoked_at" class="col-12">
                                <strong>Revoked At:</strong>
                                <p>{{ formatDate(selectedAttestation.revoked_at) }}</p>
                            </div>
                        </div>

                        <div v-if="selectedAttestation.notes" class="row mb-3">
                            <div class="col-12">
                                <strong>Notes:</strong>
                                <p>{{ selectedAttestation.notes }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            Close
                        </button>
                        <button
                            v-if="canEdit && selectedAttestation.status === 'issued' && !selectedAttestation.is_expired"
                            type="button"
                            class="btn btn-warning"
                        >
                            <i class="bi bi-x-circle"></i> Revoke Attestation
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