
<template>
    <div class="card">
        <div class="card-header">
            <div>Profile Details</div>
        </div>
        <form class="card-body" @submit.prevent="submitForm">
            <div class="row g-3">
                <div v-if="error != null" class="col-12 mb-3 text-danger">
                    {{ error }}
                </div>

                <div class="col-md-4">
                    <Label for="inputFirstName" value="First Name" />
                    <Input type="text" class="form-control bg-light" id="inputFirstName" v-model="form.first_name" disabled />
                </div>
                <div class="col-md-4">
                    <Label for="inputLastName" value="Last Name" />
                    <Input type="text" class="form-control bg-light" id="inputLastName" v-model="form.last_name" disabled />
                </div>
                <div class="col-md-4">
                    <Label for="inputEmail" value="Email" />
                    <Input type="email" class="form-control bg-light" id="inputEmail" v-model="form.email" disabled />
                </div>

                <hr />

                <div class="col-md-3">
                    <Label for="inputGender" class="form-label" value="Gender"/>
                    <Select class="form-select" id="inputGender" v-model="form.gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Prefer not to answer">Prefer not to answer</option>
                    </Select>
                </div>

                <div class="col-md-3">
                    <Label for="inputSin" class="form-label" value="SIN" />
                    <Input type="number" min="100000000" max="999999999" class="form-control" id="inputSin" v-model="form.sin" placeholder="999 999 999" />
                    <InputError :message="form.errors.sin" class="mt-1" />
                </div>

                <div class="col-md-3">
                    <Label for="inputDob" class="form-label" value="Birth Date" />
                    <Input type="date" class="form-control" id="inputDob" v-model="form.dob" />
                    <InputError :message="form.errors.dob" class="mt-1" />
                </div>

                <div class="col-md-6">
                    <Label for="inputAddress" class="form-label" value="Address" />
                    <Input type="text" class="form-control" id="inputAddress" v-model="form.address" />
                    <InputError :message="form.errors.address" class="mt-1" />
                </div>
                <div class="col-md-3">
                    <Label for="inputCity" class="form-label" value="City" />
                    <Input type="text" class="form-control" id="inputCity" v-model="form.city" />
                    <InputError :message="form.errors.city" class="mt-1" />
                </div>
                <div class="col-md-3">
                    <Label for="inputPostalCode" class="form-label" value="Postal Code" />
                    <Input type="text" maxlength="7" class="form-control" id="inputPostalCode" v-model="form.zip_code" />
                    <InputError :message="form.errors.zip_code" class="mt-1" />
                </div>

                <StudentDemographics 
                    v-if="demographics && demographics.length > 0"
                    :demographics="demographics"
                    :existing-demographics="existingDemographics"
                    v-model="form.demographics"
                    :errors="form.errors"
                />

                <div class="col-12">
                    <hr />
                    <h5 class="mb-3">Declarations:</h5>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked1" v-model="form.bc_resident" />
                        <label for="flexCheckChecked1" class="form-check-label">
                            I confirm that I am a resident of British Columbia.
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="flexCheckChecked2" v-model="form.info_consent" />
                        <label for="flexCheckChecked2" class="form-check-label">
                            I consent to the collection and use of my personal information.
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="card-footer mt-4 d-flex justify-content-between">
                <button type="submit" class="btn btn-success" :disabled="form.processing">
                    Update Profile
                </button>
                <button type="button" class="btn btn-danger" @click="deleteAccount">
                    Delete Account
                </button>
            </div>
            
            <FormSubmitAlert :form-state="formState" :success-msg="'Student record was updated successfully.'" />
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import {ref} from 'vue';
import Select from '@/Components/Select.vue';
import Input from '@/Components/Input.vue';
import InputError from '@/Components/InputError.vue';
import Label from '@/Components/Label.vue';
import FormSubmitAlert from '@/Components/FormSubmitAlert.vue';
import StudentDemographics from './StudentDemographics.vue';

const props = defineProps({
    profileData: Object,
    demographics: Array,
    existingDemographics: [Array, Object],
    error: String
});

const formState = ref(null);

const form = useForm({
    first_name: props.profileData?.first_name || '',
    last_name: props.profileData?.last_name || '',
    email: props.profileData?.email || '',
    gender: props.profileData?.gender || '',
    sin: props.profileData?.sin || '',
    dob: props.profileData?.dob || '',
    address: props.profileData?.address || '',
    city: props.profileData?.city || '',
    zip_code: props.profileData?.zip_code || '',
    bc_resident: props.profileData?.bc_resident || false,
    info_consent: props.profileData?.info_consent || false,
    demographics: props.existingDemographics || []
});

const submitForm = () => {
    formState.value = null;
    
    form.put(route('student.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            formState.value = true;
        },
        onError: () => {
            formState.value = false;
        }
    });
};

const deleteAccount = () => {
    if (confirm("Are you sure you want to delete your account? This action can be reversed by admin.")) {
        form.delete(route('student.profile.destroy'));
    }
};
</script>
