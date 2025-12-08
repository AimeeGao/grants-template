<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    reportData: Object,
    filters: Object,
});

const reportType = ref(props.filters?.report_type || 'summary');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const generateReport = () => {
    router.get(route('institution.reports'), {
        report_type: reportType.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportReport = () => {
    const params = new URLSearchParams({
        start_date: startDate.value,
        end_date: endDate.value,
    });
    window.location.href = route('institution.reports.export', reportType.value) + '?' + params.toString();
};
</script>

<template>
    <div class="reports-list">
        <h2 class="mb-4">Reports & Analytics</h2>

        <!-- Report Type Selector and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Report Type</label>
                        <select v-model="reportType" class="form-select">
                            <option value="summary">Summary Report</option>
                            <option value="applications">Applications Report</option>
                            <option value="attestations">Attestations Report</option>
                            <option value="quota">Quota Usage Report</option>
                            <option value="monthly">Monthly Activity Report</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input v-model="startDate" type="date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input v-model="endDate" type="date" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button @click="generateReport" class="btn btn-primary w-100">
                            <i class="bi bi-bar-chart"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Report -->
        <div v-if="reportData?.type === 'summary'" class="report-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Summary Report</h4>
                <button @click="exportReport" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>

            <!-- Applications Summary -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Applications Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <h6 class="text-muted">Total</h6>
                            <div class="h2 text-primary">{{ reportData.applications.total }}</div>
                        </div>
                        <div class="col-md-9">
                            <h6 class="text-muted mb-3">By Status</h6>
                            <div class="row">
                                <div v-for="(count, status) in reportData.applications.byStatus" :key="status" class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-capitalize">{{ status.replace('_', ' ') }}:</span>
                                        <strong>{{ count }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">By Program Level</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Graduate:</span>
                                        <strong>{{ reportData.applications.byProgramLevel.graduate }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Undergraduate:</span>
                                        <strong>{{ reportData.applications.byProgramLevel.undergraduate }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attestations Summary -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Attestations Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <h6 class="text-muted">Total</h6>
                            <div class="h2 text-success">{{ reportData.attestations.total }}</div>
                        </div>
                        <div class="col-md-9">
                            <h6 class="text-muted mb-3">By Status</h6>
                            <div class="row">
                                <div v-for="(count, status) in reportData.attestations.byStatus" :key="status" class="col-md-3 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-capitalize">{{ status }}:</span>
                                        <strong>{{ count }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">By Type</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Graduate:</span>
                                        <strong>{{ reportData.attestations.byType.graduate }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Undergraduate:</span>
                                        <strong>{{ reportData.attestations.byType.undergraduate }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quota Usage -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Quota Usage</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6>Graduate Quota</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <div
                                    class="progress-bar"
                                    :class="reportData.quota.usage.graduate.percentageUsed >= 90 ? 'bg-danger' : 'bg-success'"
                                    :style="{width: reportData.quota.usage.graduate.percentageUsed + '%'}"
                                >
                                    {{ reportData.quota.usage.graduate.percentageUsed }}%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Issued: {{ reportData.quota.usage.graduate.issued }}</span>
                                <span>Remaining: {{ reportData.quota.usage.graduate.remaining }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6>Undergraduate Quota</h6>
                            <div class="progress mb-2" style="height: 25px;">
                                <div
                                    class="progress-bar"
                                    :class="reportData.quota.usage.undergraduate.percentageUsed >= 90 ? 'bg-danger' : 'bg-success'"
                                    :style="{width: reportData.quota.usage.undergraduate.percentageUsed + '%'}"
                                >
                                    {{ reportData.quota.usage.undergraduate.percentageUsed }}%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Issued: {{ reportData.quota.usage.undergraduate.issued }}</span>
                                <span>Remaining: {{ reportData.quota.usage.undergraduate.remaining }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quota Report -->
        <div v-else-if="reportData?.type === 'quota'" class="report-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Quota Usage Report</h4>
                <button @click="exportReport" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Total Quota</h6>
                                <div class="h2">{{ reportData.quotas.total }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Graduate Quota</h6>
                                <div class="h2">{{ reportData.quotas.graduate }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted">Undergraduate Quota</h6>
                                <div class="h2">{{ reportData.quotas.undergraduate }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Graduate Usage</h5>
                            <div class="progress mb-3" style="height: 30px;">
                                <div
                                    class="progress-bar"
                                    :class="reportData.usage.graduate.percentageUsed >= 90 ? 'bg-danger' : 'bg-success'"
                                    :style="{width: reportData.usage.graduate.percentageUsed + '%'}"
                                >
                                    {{ reportData.usage.graduate.percentageUsed }}%
                                </div>
                            </div>
                            <table class="table table-sm">
                                <tr>
                                    <td>Issued:</td>
                                    <td class="text-end"><strong>{{ reportData.usage.graduate.issued }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Remaining:</td>
                                    <td class="text-end"><strong>{{ reportData.usage.graduate.remaining }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Undergraduate Usage</h5>
                            <div class="progress mb-3" style="height: 30px;">
                                <div
                                    class="progress-bar"
                                    :class="reportData.usage.undergraduate.percentageUsed >= 90 ? 'bg-danger' : 'bg-success'"
                                    :style="{width: reportData.usage.undergraduate.percentageUsed + '%'}"
                                >
                                    {{ reportData.usage.undergraduate.percentageUsed }}%
                                </div>
                            </div>
                            <table class="table table-sm">
                                <tr>
                                    <td>Issued:</td>
                                    <td class="text-end"><strong>{{ reportData.usage.undergraduate.issued }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Remaining:</td>
                                    <td class="text-end"><strong>{{ reportData.usage.undergraduate.remaining }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Report Types (Applications, Attestations, Monthly) -->
        <div v-else-if="reportData" class="report-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-capitalize">{{ reportData.type }} Report</h4>
                <button @click="exportReport" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Report data loaded. Use the Export CSV button to download the full report.
                    </div>
                    <p class="mb-0">
                        <strong>Total Records:</strong> {{ reportData.total || 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-primary {
    color: #003366 !important;
}

.card-header.bg-primary {
    background-color: #003366 !important;
}

.progress-bar {
    font-weight: 600;
}
</style>