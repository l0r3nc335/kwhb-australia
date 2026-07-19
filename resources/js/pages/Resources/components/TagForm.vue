<template>
    <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
        <div class="tw-flex tw-justify-between py-2">
            <div class="tw-rounded tw-flex tw-border tw-border-orange-400 tw-rounded-t">
                
            </div>
            <div>
                
            </div>
        </div>    
           
        <div class="tw-flex tw-py-1 tw-mb-2">
            <VTextField v-model="name" placeholder="Title" size="sm"></VTextField>
        </div>

        <ColorSelector v-model:color="selectedColor" />

        
        <div class="tw-flex tw-justify-end tw-py-3">
            <VBtn class="tw-mr-2" color="secondary" @click="$emit('close')">Cancel</VBtn>
            <VBtn color="success" :disabled="!formValidated" @click="saveTag">Save</VBtn>
        </div>
    </div>
</template>

<script>
    import resourceApi from "@/api/resources/resourceApi";
    import TagSelector from "@/components/selectors/TagSelector.vue";
    import ColorSelector from "@/components/selectors/ColorSelector.vue";

    export default {
        components: {
            TagSelector,
            ColorSelector
        },

        props: {
            category: {
                default: null
            }
        },

        data() {
            return {
                name: null,
                selectedColor: 'black'
            }
        },

        computed: {
            formValidated() {
                if (this.type === 1) {
                    return this.name && this.file
                }

                return this.name
            }
        },

        methods: {
            selectTags(tags) {
                this.tags = tags
            },

            resetForm() {
                this.name = null
               
            },

            async saveTag() {

                let form = new FormData();

                form.append('name', this.name)
                form.append('color', this.selectedColor)

                await resourceApi.saveTag(form)
                this.resetForm()

                this.$toast.success('Tag saved!')
                this.$emit('close')
                this.$emit('reload-resources')
            }
        }
    }
</script>

<style >
#privacySelect .v-select .v-field .v-text-field__prefix, .v-select .v-field .v-text-field__suffix, .v-select .v-field .v-field__input, .v-select .v-field.v-field {
    padding: 0 3px !important;
}
input[type='date']::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}
</style>
