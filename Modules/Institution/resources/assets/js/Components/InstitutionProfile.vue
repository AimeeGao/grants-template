<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    institution: Object,
    canEdit: Boolean,
    message: String,
});

const isEditing = ref(false);

const form = useForm({
    legal_name: props.institution?.legal_name || '',
    primary_email: props.institution?.primary_email || '',
    primary_contact: props.institution?.primary_contact || '',
    address1: props.institution?.address1 || '',
    address2: props.institution?.address2 || '',
    city: props.institution?.city || '',
    province: props.institution?.province || '',
    postal_code: props.institution?.postal_code || '',
    economic_region: props.institution?.economic_region || '',
});

const submit = () => {
    form.put(route('institution.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const cancelEdit = () => {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
};
</script>

<template>
    <div class="institution-profile">
        <h2 class="mb-4">Institution Profile</h2>

        <!-- Alert for missing institution -->
        <div v-if="!institution" class="alert alert-warning">
            {{ message || 'Institution profile not found. Please contact administrator.' }}
        </div>

        <div v-if="institution">
            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Institution Name</label>
                            <p class="form-control-plaintext">{{ institution.name }}</p>
                            <small class="text-muted">This field cannot be edited</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Name Code</label>
                            <p class="form-control-plaintext">{{ institution.name_code }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="legal_name">Legal Name</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="legal_name"
                                v-model="form.legal_name"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.legal_name || '-' }}
                            </p>
                            <div v-if="form.errors.legal_name" class="text-danger small">
                                {{ form.errors.legal_name }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Category</label>
                            <p class="form-control-plaintext">{{ institution.category || '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Size</label>
                            <p class="form-control-plaintext">{{ institution.size || '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span
                                    class="badge"
                                    :class="institution.active_status ? 'bg-success' : 'bg-secondary'"
                                >
                                    {{ institution.active_status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">DLI</label>
                            <p class="form-control-plaintext">{{ institution.dli || '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="economic_region">Economic Region</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="economic_region"
                                v-model="form.economic_region"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.economic_region || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="primary_contact">Primary Contact</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="primary_contact"
                                v-model="form.primary_contact"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.primary_contact || '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="primary_email">Primary Email</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="primary_email"
                                v-model="form.primary_email"
                                type="email"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.primary_email || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Address</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address1">Address Line 1</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="address1"
                                v-model="form.address1"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.address1 || '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address2">Address Line 2</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="address2"
                                v-model="form.address2"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.address2 || '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city">City</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="city"
                                v-model="form.city"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.city || '-' }}
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="province">Province</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="province"
                                v-model="form.province"
                                type="text"
                                class="form-control"
                                maxlength="3"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.province || '-' }}
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code">Postal Code</label>
                            <input
                                v-if="isEditing && canEdit"
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                class="form-control"
                            />
                            <p v-else class="form-control-plaintext">
                                {{ institution.postal_code || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mb-4" v-if="canEdit">
                <button
                    v-if="!isEditing"
                    type="button"
                    @click="isEditing = true"
                    class="btn btn-primary"
                >
                    Edit Profile
                </button>

                <template v-else>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="btn btn-success"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="btn btn-secondary"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                </template>
            </div>

            <div v-else class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Note:</strong> Only users with Institution Admin role can edit the profile.
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-header.bg-primary {
    background-color: #003366 !important;
}

.form-control-plaintext {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    margin-bottom: 0;
    line-height: 1.5;
}
</style>
