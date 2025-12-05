<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/Authenticated.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Label from '@/Components/Label.vue';
import InputError from '@/Components/InputError.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';

const props = defineProps({
    auth: Object,
    institution: Object,
    canEdit: Boolean,
    message: String,
});

const isEditing = ref(false);

const form = useForm({
    display_name: props.institution?.display_name || '',
    primary_contact_email: props.institution?.primary_contact_email || '',
    primary_contact_phone: props.institution?.primary_contact_phone || '',
    address_line_1: props.institution?.address_line_1 || '',
    address_line_2: props.institution?.address_line_2 || '',
    city: props.institution?.city || '',
    province: props.institution?.province || '',
    postal_code: props.institution?.postal_code || '',
    website: props.institution?.website || '',
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
    <AuthenticatedLayout>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Institution Profile</h1>

                    <!-- Alert for missing institution -->
                    <div v-if="!institution" class="alert alert-warning">
                        {{ message }}
                    </div>

                    <!-- Success/Error Alerts -->
                    <FormSubmitAlert
                        v-if="$page.props.success || $page.props.errors.error"
                        :success="$page.props.success"
                        :error="$page.props.errors.error"
                    />

                    <form v-if="institution" @submit.prevent="submit">
                        <!-- Basic Information Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <Label value="Official Institution Name" class="fw-bold" />
                                        <p class="form-control-plaintext">{{ institution.name }}</p>
                                        <small class="text-muted">This field cannot be edited</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label for="display_name" value="Display Name / Short Name" />
                                        <Input
                                            v-if="isEditing"
                                            id="display_name"
                                            v-model="form.display_name"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.display_name || '-' }}
                                        </p>
                                        <InputError :message="form.errors.display_name" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <Label value="BCeID Business GUID" class="fw-bold" />
                                        <p class="form-control-plaintext">
                                            <small class="font-monospace">{{ institution.bceid_business_guid }}</small>
                                        </p>
                                        <small class="text-muted">Internal reference</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label value="Institution Type" class="fw-bold" />
                                        <p class="form-control-plaintext">{{ institution.institution_type || '-' }}</p>
                                        <small class="text-muted">This field cannot be edited</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <Label value="DLI Number" class="fw-bold" />
                                        <p class="form-control-plaintext">{{ institution.dli_number || '-' }}</p>
                                        <small class="text-muted">Designated Learning Institution number</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label value="Status" class="fw-bold" />
                                        <p class="form-control-plaintext">
                                            <span
                                                class="badge"
                                                :class="institution.is_active ? 'bg-success' : 'bg-secondary'"
                                            >
                                                {{ institution.is_active ? 'Active' : 'Inactive' }}
                                            </span>
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
                                        <Label for="primary_contact_email" value="Primary Contact Email" />
                                        <Input
                                            v-if="isEditing"
                                            id="primary_contact_email"
                                            v-model="form.primary_contact_email"
                                            type="email"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.primary_contact_email || '-' }}
                                        </p>
                                        <InputError :message="form.errors.primary_contact_email" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label for="primary_contact_phone" value="Primary Contact Phone" />
                                        <Input
                                            v-if="isEditing"
                                            id="primary_contact_phone"
                                            v-model="form.primary_contact_phone"
                                            type="tel"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.primary_contact_phone || '-' }}
                                        </p>
                                        <InputError :message="form.errors.primary_contact_phone" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <Label for="website" value="Website" />
                                        <Input
                                            v-if="isEditing"
                                            id="website"
                                            v-model="form.website"
                                            type="url"
                                            class="form-control"
                                            :disabled="!canEdit"
                                            placeholder="https://example.com"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            <a
                                                v-if="institution.website"
                                                :href="institution.website"
                                                target="_blank"
                                                class="text-decoration-none"
                                            >
                                                {{ institution.website }}
                                            </a>
                                            <span v-else>-</span>
                                        </p>
                                        <InputError :message="form.errors.website" />
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
                                        <Label for="address_line_1" value="Address Line 1" />
                                        <Input
                                            v-if="isEditing"
                                            id="address_line_1"
                                            v-model="form.address_line_1"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.address_line_1 || '-' }}
                                        </p>
                                        <InputError :message="form.errors.address_line_1" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <Label for="address_line_2" value="Address Line 2" />
                                        <Input
                                            v-if="isEditing"
                                            id="address_line_2"
                                            v-model="form.address_line_2"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.address_line_2 || '-' }}
                                        </p>
                                        <InputError :message="form.errors.address_line_2" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <Label for="city" value="City" />
                                        <Input
                                            v-if="isEditing"
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.city || '-' }}
                                        </p>
                                        <InputError :message="form.errors.city" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label for="province" value="Province" />
                                        <Input
                                            v-if="isEditing"
                                            id="province"
                                            v-model="form.province"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.province || '-' }}
                                        </p>
                                        <InputError :message="form.errors.province" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <Label for="postal_code" value="Postal Code" />
                                        <Input
                                            v-if="isEditing"
                                            id="postal_code"
                                            v-model="form.postal_code"
                                            type="text"
                                            class="form-control"
                                            :disabled="!canEdit"
                                        />
                                        <p v-else class="form-control-plaintext">
                                            {{ institution.postal_code || '-' }}
                                        </p>
                                        <InputError :message="form.errors.postal_code" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <Label value="Country" />
                                        <p class="form-control-plaintext">{{ institution.country || 'Canada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attestation Quota Card (Read-only) -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Attestation Quotas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <Label value="Total Quota" class="fw-bold" />
                                        <p class="form-control-plaintext fs-4 text-primary">
                                            {{ institution.attestation_quota_total || 0 }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <Label value="Graduate Quota" class="fw-bold" />
                                        <p class="form-control-plaintext fs-4 text-primary">
                                            {{ institution.attestation_quota_grad || 0 }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <Label value="Undergraduate Quota" class="fw-bold" />
                                        <p class="form-control-plaintext fs-4 text-primary">
                                            {{ institution.attestation_quota_undergrad || 0 }}
                                        </p>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Quotas are managed by Ministry administrators and cannot be edited here.
                                </small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mb-4" v-if="canEdit">
                            <Button
                                v-if="!isEditing"
                                type="button"
                                @click="isEditing = true"
                                class="btn btn-primary"
                            >
                                Edit Profile
                            </Button>

                            <template v-else>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="btn btn-success"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </Button>
                                <Button
                                    type="button"
                                    @click="cancelEdit"
                                    class="btn btn-secondary"
                                    :disabled="form.processing"
                                >
                                    Cancel
                                </Button>
                            </template>
                        </div>

                        <div v-else class="alert alert-info">
                            <strong>Note:</strong> Only users with Institution Admin role can edit the profile.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.card-header {
    background-color: #003366;
}

.form-control-plaintext {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    margin-bottom: 0;
    line-height: 1.5;
}

.font-monospace {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
</style>