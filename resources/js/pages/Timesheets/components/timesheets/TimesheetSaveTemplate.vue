<template>
    <VDialog v-model="showDialog" width="300">
        <template #activator="{props}">
            <VBtn v-bind="props"  color="success" style="background-color: #E25204" class="tw-mr-4" @click="showDialog=true">
                <VIcon icon="tabler-device-floppy"/>
                <span class="tw-ml-1">Save As Template</span>
            </VBtn>
        </template>
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="showDialog=false" />

        <div class="tw-flex tw-flex-col tw-bg-white tw-p-4 tw-py-6 tw-rounded-lg">
            <VTextField v-model="name" label="Template Name" required></VTextField>
            <div class="tw-text-white tw-w-full tw-flex tw-mt-4 tw-justify-end">
                <VBtn color="success" @click="saveTemplate">
                    <VIcon icon="tabler-device-floppy" class="tw-mr-2"></VIcon>
                    Save
                </VBtn>
            </div>

        </div>
    </VDialog>
</template>
<script>
    export default {
        props: {
            defaultName: {
                required: true,
                type: String
            },
            template: {
                default: null
            }
        },

        data() {
            return {
                name: null,
                showDialog: false
            }
        },

        mounted() {
            if (this.template) {
                this.name = this.template.name
            } else {
                this.name = this.defaultName
            }
        },

        methods: {
            saveTemplate() {

                if (this.name) {
                    this.$toast.clear()
                    if (this.template) {
                        this.$emit('update', {
                            name: this.name,
                            template: this.template
                        })
                    } else {
                        this.$emit('save', this.name)
                    }
                    this.showDialog = false
                } else {
                    this.$toast.warning("Template name is required!")
                }
            }
        }

    }
</script>
