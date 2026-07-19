<template>
    <div class="tw-flex tw-flex-col tw-py-3">
        <div class="tw-p-6 tw-bg-gray-100">
            <div class="tw-text-[1.5rem]">Templates</div>
        </div>
        <div class="tw-py-4 tw-border tw-border-gray-100">
            <div v-if="templates.length > 0" class="tw-px-4">
                <div v-for="template in templates" class="tw-mb-3 tw-flex tw-justify-between  tw-cursor-pointer">
                    <div class="tw-px-3 tw-py-1 hover:tw-bg-blue-900  tw-rounded-lg  hover:tw-text-white tw-text-blue-900" @click="preview(template)">{{template.name}}</div>
                    <div class="tw-flex tw-text-gray-500">
                        <div v-if="!timesheetSubmitted" class="tw-text-orange-500 tw-py-1 tw-px-2 tw-rounded-lg tw-cursor-pointer tw-mr-4  hover:tw-bg-orange-500 hover:tw-text-white" @click="previewApply(template)">Apply</div>
                        <div class="tw-mr-6 tw-py-1 hover:tw-text-blue-700" @click="previewEdit(template)">
                            <VIcon icon="tabler-edit"></VIcon>
                        </div>
                        <div class="tw-mr-2 tw-py-1 hover:tw-text-red-700"  @click="showDeleteTemplateDialog(template)">
                            <VIcon icon="tabler-trash"></VIcon>
                        </div>
                        <div>
                            <IconBtn>
                                <VIcon icon="tabler-dots-vertical" />
                                <VMenu
                                    activator="parent"
                                >
                                    <VList>
                                        <VListItem v-if="!timesheetSubmitted" class="tw-cursor-pointer hover:tw-bg-gray-100" @click="previewApply(template)">Apply</VListItem>
                                        <VListItem class="tw-cursor-pointer hover:tw-bg-gray-100" @click="previewEdit(template)">Edit</VListItem>
                                        <VListItem class="tw-cursor-pointer hover:tw-bg-green-100" @click="showDeleteTemplateDialog(template)">Delete</VListItem>
                                    </VList>
                                </VMenu>
                            </IconBtn>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="tw-flex tw-justify-center">
                No templates yet.
            </div>
        </div>
        <VDialog width="1460" v-model="showPreviewTemplate">
            <DialogCloseBtn @click="showPreviewTemplate=false" />
            <div class="tw-flex tw-flex-col">
                <TemplatePreview @update-template="data => $emit('update-template', data)"
                                 @apply-template="data => $emit('apply-template', data)"
                                 @close="showPreviewTemplate=false"
                                 :template="selectedTemplate"
                                 :categories="categories"
                                 :view-mode="viewOnly"
                                 :apply-mode="applyMode"
                                 :edit-mode="editMode">
                </TemplatePreview>
            </div>
        </VDialog>
        <VDialog v-model="showDeleteDialog" width="400" class="">
            <div class="tw-flex tw-flex-col tw-rounded-lg tw-p-6 tw-bg-white tw-justify-center " >
                <div class="tw-font-bold">
                    Delete Template
                </div>
                <div class="tw-py-10 tw-justify-center tw-flex">
                    <VIcon icon="tabler-trash" class="tw-text-red-500 tw-mr-4"></VIcon> Are you sure?
                </div>
                <div class="tw-flex tw-justify-end">
                    <VBtn color="error" class="tw-mr-3" @click="deleteTemplate">Delete Template</VBtn>
                    <VBtn color="secondary" @click="closeDeleteDialog">Close</VBtn>
                </div>
            </div>

        </VDialog>
    </div>
</template>
<script>
    import TemplatePreview from "@/pages/Timesheets/components/timesheets/TemplatePreview.vue";
    import timesheetApi from "@/api/timesheet/timesheetApi";

    export default {
        emits: ['update-template', 'apply-template', 'reload-templates'],

        components: {
            TemplatePreview
        },

        props: {
            categories: {
                required: true
            },

            templates: {
                required: true,
                type: Array,
                default: []
            },

            timesheetSubmitted: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                selectedTemplate: null,
                showPreviewTemplate: false,
                showEditTemplate: false,
                viewOnly: false,
                editMode: false,
                applyMode: false,
                showDeleteDialog: false,
            }
        },

        methods: {
            async deleteTemplate() {
                await timesheetApi.deleteTemplate(this.selectedTemplate?.id)
                this.closeDeleteDialog()
                this.$emit('reload-templates')
            },

            closeDeleteDialog() {
                this.selectedTemplate = null
                this.showDeleteDialog = false
            },

            showDeleteTemplateDialog(template) {
                this.selectedTemplate = template
                this.showDeleteDialog = true
            },

            preview(template) {
                this.viewOnly = true
                this.editMode = false
                this.applyMode = false
                this.selectedTemplate = template
                this.showPreviewTemplate = true
            },

            previewApply(template) {
                this.applyMode = true
                this.editMode = false
                this.viewOnly = false
                this.selectedTemplate = template
                this.showPreviewTemplate = true
            },

            previewEdit(template) {
                this.applyMode = false
                this.editMode = true
                this.viewOnly = false
                this.selectedTemplate = template
                this.showPreviewTemplate = true
            }
        }
    }
</script>
