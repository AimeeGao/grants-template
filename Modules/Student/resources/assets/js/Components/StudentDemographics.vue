<template>
    <div v-if="demographics && demographics.length > 0" class="col-12">
        <hr />
        <h5 class="mb-3">Demographics Information:</h5>
        <p><i>The information provided below is collected exclusively for reporting and evaluation purposes to help the Province better understand where funding is most needed. It will not impact your eligibility for grant funding. Participation is entirely voluntary, and you may choose not to answer any questions that you find uncomfortable. Please refer to the FAQ page in the top banner for demographic information definitions.</i></p>

        <div class="row">
            <div v-for="demographic in demographics" :key="demographic.id" class="mb-3 col-md-6 col-sm-12">
                <Label :for="'demographic_' + demographic.id" class="form-label">
                    {{ demographic.question }}
                    <span v-if="demographic.required" class="text-danger">*</span>
                </Label>
                
                <!-- Text Input -->
                <Input 
                    v-if="demographic.type === 'text'"
                    :id="'demographic_' + demographic.id"
                    type="text" 
                    class="form-control"
                    :value="getDemographicAnswer(demographic.id)"
                    @input="updateDemographicAnswer(demographic.id, $event.target.value)"
                    :readonly="readonly"
                    :disabled="readonly"
                />
                
                <!-- Select Dropdown -->
                <Select 
                    v-else-if="demographic.type === 'select'"
                    :id="'demographic_' + demographic.id"
                    class="form-select"
                    :value="getDemographicAnswer(demographic.id)"
                    @change="updateDemographicAnswer(demographic.id, $event.target.value)"
                    :readonly="readonly"
                    :disabled="readonly"
                >
                    <option value="">Please select...</option>
                    <option 
                        v-for="option in demographic.options" 
                        :key="option.id"
                        :value="option.value || option.label"
                    >
                        {{ option.label }}
                    </option>
                </Select>
                
                <small v-if="demographic.description" class="form-text text-muted">
                    {{ demographic.description }}
                </small>
            </div>
        </div>
    </div>
</template>

<script>
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Label from '@/Components/Label.vue';

export default {
    name: 'StudentDemographics',
    components: {
        Input,
        Select,
        Label
    },
    props: {
        demographics: {
            type: Array,
            default: () => []
        },
        modelValue: {
            type: [Object, Array],
            default: () => ({})
        },
        existingDemographics: {
            type: [Object, Array],
            default: () => ({})
        },
        readonly: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue'],
    data() {
        const initialData = {};
        let sourceData = this.modelValue;
        
        if ((!sourceData || (Array.isArray(sourceData) && sourceData.length === 0) || (typeof sourceData === 'object' && Object.keys(sourceData).length === 0)) && this.existingDemographics) {
            sourceData = this.existingDemographics;
        }

        // Transform modelValue if it's in the formatted array format
        if (Array.isArray(sourceData)) {
            sourceData.forEach(item => {
                if (item.demographic_id && item.answers) {
                    initialData[item.demographic_id] = Array.isArray(item.answers) ? item.answers.join(',') : item.answers;
                }
            });
        } else if (sourceData && typeof sourceData === 'object') {
            Object.assign(initialData, sourceData);
        }
        
        return {
            demographicAnswers: initialData, 
            isUpdatingFromParent: false
        }
    },
    mounted() {
        this.emitFormattedData();
    },
    watch: {
        modelValue: {
            handler(newVal) {
                this.isUpdatingFromParent = true;
                
                // Transform formatted data back to internal format
                const internalFormat = {};
                
                if (Array.isArray(newVal)) {
                    newVal.forEach(item => {
                        if (item.demographic_id && item.answers) {
                            internalFormat[item.demographic_id] = item.answers.join(',');
                        }
                    });
                } else if (newVal && typeof newVal === 'object') {
                    // Handle the old format for backwards compatibility
                    Object.assign(internalFormat, newVal);
                }
                
                this.demographicAnswers = internalFormat;

                this.$nextTick(() => {
                    this.isUpdatingFromParent = false;
                });
            },
            deep: true
        },
        demographicAnswers: {
            handler(newVal) {
                if (this.isUpdatingFromParent) {
                    return;
                }

                this.emitFormattedData();
            },
            deep: true
        }
    },
    methods: {
        emitFormattedData() {
            const newVal = this.demographicAnswers;
            
            const formattedData = Object.keys(newVal).map(demographicId => {
                const answerValue = newVal[demographicId];
                let answers = [];
                
                if (answerValue) {
                    answers = String(answerValue).split(',').map(v => v.trim()).filter(v => v !== '');
                    
                    if (answers.length === 0 && String(answerValue).trim() !== '') {
                        answers = [String(answerValue).trim()];
                    }
                }
                
                return {
                    demographic_id: parseInt(demographicId),
                    answers: answers
                };
            }); 
            
            this.$emit('update:modelValue', formattedData);
        },

        getDemographicAnswer(demographicId) {
            return this.demographicAnswers[demographicId] || '';
        },
        
        getDemographicAnswerArray(demographicId) {
            const answer = this.demographicAnswers[demographicId];
            if (Array.isArray(answer)) {
                return answer;
            }
            return answer ? answer.split(',') : [];
        },
        
        updateDemographicAnswer(demographicId, value) {
            if (this.readonly) return;
            this.demographicAnswers[demographicId] = value;
        },
    }
}
</script>
