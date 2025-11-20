<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout v-bind="$attrs">
        <div class="container py-4">
            <!-- Institution Name and Welcome -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="display-6 mb-2">{{ institutionName }}</h1>
                    <p class="text-muted">Welcome {{ $attrs.auth.user.first_name }} {{ $attrs.auth.user.last_name }}</p>
                </div>
            </div>

            <!-- Top Row: Total, Reserved, Available Attestations -->
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Total Attestations Available</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="display-4">{{ totalAttestations }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Reserved Grad. Attestations</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="display-4">{{ reservedGradAttestations }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Available Attestations</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="display-4">{{ availableAttestations }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Grad, Undergrad, Remaining -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Grad. Attestations</h6>
                        </div>
                        <div class="card-body py-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="d-block text-muted mb-2">Issued</small>
                                    <div class="h2">{{ gradIssued }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted mb-2">Declined</small>
                                    <div class="h2">{{ gradDeclined }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-muted">* For more information on Declined PALs see FAQ</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Undergrad. Attestations</h6>
                        </div>
                        <div class="card-body py-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="d-block text-muted mb-2">Issued</small>
                                    <div class="h2">{{ undergradIssued }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted mb-2">Declined</small>
                                    <div class="h2">{{ undergradDeclined }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-muted">* For more information on Declined PALs see FAQ</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-white border-bottom text-center">
                            <h6 class="mb-0">Remaining Undergrad. Attestations</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="display-4">{{ remainingUndergradAttestations }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import { Head } from '@inertiajs/vue3';

export default {
    name: 'Dashboard',
    components: {
        AuthenticatedLayout,
        Head
    },
    props: {
        institutionName: {
            type: String,
            default: 'College A Victoria'
        },
        totalAttestations: {
            type: Number,
            default: 0
        },
        reservedGradAttestations: {
            type: Number,
            default: 0
        },
        availableAttestations: {
            type: Number,
            default: 0
        },
        gradIssued: {
            type: Number,
            default: 0
        },
        gradDeclined: {
            type: Number,
            default: 0
        },
        undergradIssued: {
            type: Number,
            default: 0
        },
        undergradDeclined: {
            type: Number,
            default: 0
        },
        remainingUndergradAttestations: {
            type: Number,
            default: 0
        }
    }
}
</script>
