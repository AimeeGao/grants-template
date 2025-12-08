<template>
    <div class="dashboard-overview">
        <!-- Institution Name and Welcome -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6 mb-2">{{ institutionName }}</h1>
                <p class="text-muted">Welcome {{ userName }}</p>
            </div>
        </div>

        <!-- Top Row: Total, Reserved, Available Attestations -->
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Total Attestations Available</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="display-4 text-primary">{{ totalAttestations }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Reserved Grad. Attestations</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="display-4 text-success">{{ reservedGradAttestations }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Available Attestations</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="display-4 text-info">{{ availableAttestations }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Grad, Undergrad, Remaining -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Grad. Attestations</h6>
                    </div>
                    <div class="card-body py-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="d-block text-muted mb-2">Issued</small>
                                <div class="h2 text-success">{{ gradIssued }}</div>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-muted mb-2">Declined</small>
                                <div class="h2 text-danger">{{ gradDeclined }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">* For more information on Declined PALs see FAQ</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Undergrad. Attestations</h6>
                    </div>
                    <div class="card-body py-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="d-block text-muted mb-2">Issued</small>
                                <div class="h2 text-success">{{ undergradIssued }}</div>
                            </div>
                            <div class="col-6">
                                <small class="d-block text-muted mb-2">Declined</small>
                                <div class="h2 text-danger">{{ undergradDeclined }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">* For more information on Declined PALs see FAQ</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card metric-card">
                    <div class="card-header bg-white border-bottom text-center">
                        <h6 class="mb-0">Remaining Undergrad. Attestations</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="display-4 text-warning">{{ remainingUndergradAttestations }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DashboardOverview',
    props: {
        institutionName: {
            type: String,
            default: 'Unknown Institution'
        },
        userName: {
            type: String,
            default: 'User'
        },
        attestationData: {
            type: Object,
            default: () => ({
                totalAttestations: 0,
                reservedGradAttestations: 0,
                availableAttestations: 0,
                gradIssued: 0,
                gradDeclined: 0,
                undergradIssued: 0,
                undergradDeclined: 0,
                remainingUndergradAttestations: 0,
            })
        }
    },
    computed: {
        totalAttestations() {
            return this.attestationData?.totalAttestations || 0;
        },
        reservedGradAttestations() {
            return this.attestationData?.reservedGradAttestations || 0;
        },
        availableAttestations() {
            return this.attestationData?.availableAttestations || 0;
        },
        gradIssued() {
            return this.attestationData?.gradIssued || 0;
        },
        gradDeclined() {
            return this.attestationData?.gradDeclined || 0;
        },
        undergradIssued() {
            return this.attestationData?.undergradIssued || 0;
        },
        undergradDeclined() {
            return this.attestationData?.undergradDeclined || 0;
        },
        remainingUndergradAttestations() {
            return this.attestationData?.remainingUndergradAttestations || 0;
        }
    }
}
</script>

<style scoped>
.metric-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #dee2e6;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.1);
}

.card-header {
    background-color: #f8f9fa;
}

.card-header h6 {
    color: #003366;
    font-weight: 600;
}

.text-primary {
    color: #003366 !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}
</style>