<template>
    <div class="__editor_header_wrapper">
        <!--Button - Return Button left side -->
        <button class="__editor_back_button" @click="cancelEdit($event); this.$emit('close-editor', this.type);">
            <IconBtn style=" width: 35px; height: 35px;">
                <VIcon icon="tabler-arrow-left" />
            </IconBtn>
        </button>
        <div class="__editor_filename_wrapper">
            <input
                class="__editor_filename"
                v-model="filename"
                ref="filenameRef"
            />
            <!--small style="display: block">Updated {{currentTime}} today</small-->
        </div>

        <div style="display: flex; justify-content: center; align-items: center; height: 100px; margin-left: auto">
            <div
                v-if="templateContentEdit?.status === 'Published'"
                class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_in_completed">
                {{templateContentEdit.status}}
            </div>
            <div
                v-else-if="templateContentEdit?.status === 'Archived'"
                class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_in_progress">
                Archived
            </div>
            <div
                v-else
                class="tw-font-bold tw-p-1 tw-rounded-lg d-flex tw-items-center tw-justify-center __stats_strip __status_strip_draft">
                Draft
            </div>
        </div>
    </div>

    <div ref="templateForm" class="tw-flex">
        <div class="__editor_draggable_fields_wrapper">
            <div class="__draggable_search_box">
                <input type="text" placeholder="Search Fields" style="width: 100%; height: 100%"/>
            </div>
            <div class="__draggable_items_wrapper tw-flex">
                <div class="__draggable_items_leftside_wrapper">
                    <v-icon class="__standard_field_ribbon">mdi-checkbox-blank-outline</v-icon>
                    <v-icon class="__stander_field_settings" style="transform: rotate(90deg);">mdi-wrench</v-icon>
                    <v-icon class="__stander_field_edit">mdi-pencil</v-icon>
                </div>

                <div class="__draggable_items_rightside_wrapper">
                    <h1 class="w-text-[1rem] tw-font-bold tw-text-gray-700 tw-mb-3">Standard Fields</h1>
                    <ul>
                        <li v-for="item in draggableStandardFields" :key="item.id">
                            <div
                                class="__draggable_item flex items-center"
                                draggable="true"
                                @dragstart="handleDragStart"
                                :id="item.name"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24">
                                    <circle cx="8" cy="6" r="2"></circle>
                                    <circle cx="16" cy="6" r="2"></circle>
                                    <circle cx="8" cy="12" r="2"></circle>
                                    <circle cx="16" cy="12" r="2"></circle>
                                    <circle cx="8" cy="18" r="2"></circle>
                                    <circle cx="16" cy="18" r="2"></circle>
                                </svg>

                                {{ item.label }}
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="__main_editor_wrapper">
            <div class="editor-wrapper">
                <div ref="toolbarContainer" class="toolbar-container"></div>
                <div class="editor-container">
                    <textarea ref="editorElement"></textarea>
                </div>
            </div>
        </div>

        <div class="__editor_draggable_fields_wrapper">
            <!--div class="__pages_previews_wrapper" style="position: relative; height: auto; ">
                <div class="__pages_previews preview-a" style="position: absolute; top: 0; left: 0; right: 0;"></div>
                <div class="__pages_previews preview-b" style="position: absolute; top: 0; left: 0; right: 0;"></div>
            </div-->
            <v-expansion-panels class="__editor_draggable_fields_wrapper"  @change="onAccordionChange">
                <v-expansion-panel>
                    <v-expansion-panel-title
                        class="border"
                        @click="openDocumentAccordion"
                    >
                        Document
                    </v-expansion-panel-title>
                    <v-expansion-panel-text>
                        <div style="height: 50px; margin: 8px 0;">
                            <p><small style="color: #75748D">{{ filename }}</small></p>
                            <p><small style="color: #afadad;">Pages: {{currentPageCount}}</small></p>
                        </div>
                        <div class="__pages_previews_wrapper" style="position: relative; overflow-y: scroll">
                            <div class="__pages_previews preview-a" style="position: absolute; top: 0; left: 0; right: 0; padding: 15px;"></div>
                            <div class="__pages_previews preview-b" style="position: absolute; top: 0; left: 0; right: 0;padding: 15px;"></div>
                        </div>
                    </v-expansion-panel-text>
                </v-expansion-panel>

                <v-expansion-panel>
                    <v-expansion-panel-title class="border">History</v-expansion-panel-title>
                    <v-expansion-panel-text>
                        <VCard v-for="version in versions" size="x-small" class="__vesions_card">
                            <p><small style="color: #2a2935"> Edited: {{ convertToAusDate(version.date) }}</small></p>
                            <p><small style="color: #2a2935"> By: {{ version.user }}</small></p>
                            <div class="__action_buttons" style="display: flex; justify-content: space-between; align-items: center;">
                                <button class="__history_preview_button">Preview</button>

                                <VIcon icon="mdi-content-copy" class="__copy_icon" size="15" style="cursor: pointer;" @click="copyVersion(version)" />
                            </div>
                        </VCard>
                    </v-expansion-panel-text>
                </v-expansion-panel>
            </v-expansion-panels>
        </div>
    </div>



    <VDialog v-model="showRequestExemptionSuccessDialog" width="550">
        <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
            <div class="tw-flex tw-justify-end">
                <div class="tw-py-2 tw-px-3 tw-rounded-lg tw-bg-gray-200" @click="showRequestExemptionSuccessDialog = false">
                    <VIcon icon="tabler-x"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center">
                <div class="tw-p-4 tw-rounded-full tw-my-8" style="background-color: #b0e1bb">
                    <VIcon icon="tabler-check" class="tw-text-[4rem] tw-text-emerald-500"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center tw-font-bold tw-text-[2rem]">
                Template Created
            </div>
            <div class="tw-flex tw-text-center tw-py-6 tw-px-16">
                A new template is now created and will be available in the templates tab.
            </div>
            <div class="tw-flex tw-justify-center tw-my-5">
                <VBtn @click="showRequestExemptionSuccessDialog = false; this.$emit('close-editor' ,type);" class="tw-px-4">Return to Templates</VBtn>
            </div>
        </div>
    </VDialog>

    <VDialog v-model="showSubmitSaveOrPublishDialog" width="550">
        <div class="tw-flex tw-flex-col tw-bg-white tw-p-6 tw-rounded-lg">
            <div class="tw-flex tw-justify-end">
                <div class="tw-py-2 tw-px-3 tw-rounded-lg tw-bg-gray-200" @click="showSubmitSaveOrPublishDialog = false">
                    <VIcon icon="tabler-x"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center">
                <div class="tw-p-4 tw-rounded-full tw-my-8" style="background-color: #FDF3DE">
                    <VIcon icon="tabler-help" class="tw-text-[4rem] tw-text-yellow-500"></VIcon>
                </div>
            </div>
            <div class="tw-flex tw-justify-center tw-font-bold tw-text-[2rem]">
                {{submitSaveOrPublishAction === 'draft'? 'Draft' : 'Publish'}} Template
            </div>
            <div class="tw-text-center tw-py-6 tw-px-16">
                Are you sure you want to {{submitSaveOrPublishAction === 'draft'? 'save as draft' : 'publish'}} this template?
            </div>
            <div class="tw-flex tw-justify-center tw-my-5">
                <VBtn @click="showSubmitSaveOrPublishDialog = false;" class="tw-px-4 tw-me-4" color="transparent">Cancel</VBtn>
                <VBtn @click="showSubmitSaveOrPublishDialog = false; submitSaveOrPublishExecute(submitSaveOrPublishAction)" class="tw-px-4 tw-ms-4">Yes</VBtn>
            </div>
        </div>
    </VDialog>

    <div ref="captureArea" class="__screenshot_area"></div>

    <div class="tw-absolute tw-bottom-0 tw-right-[40px] tw-p-4 tw-z-50 tw-mb-[-70px]">
        <v-btn size="small" color="transparent" class="tw-me-5" @click="submitSaveOrPublish('draft')">Save Draft</v-btn>
        <v-btn size="small" color="transparent" class="tw-me-5" @click="cancelEdit($event);this.$emit('close-editor', type);">Cancel</v-btn>
        <v-btn size="small" class="tw-me-5" @click="submitSaveOrPublish('published')">Publish</v-btn>
    </div>
</template>

<script>
import { parse } from "postcss";
import templateApi from "@/api/template/templateApi";
import html2canvas from 'html2canvas';
import { inject, ref } from 'vue'
import { useAuthStore } from '@/store/authStore'

export default {
    name: 'WysiwygEditor',
    props: {
        draggableStandardFields: {
            type: Array,
            required: true
        },
        type: {
            type: String,
            required: true
        },
    },
    setup() {
        // Inject values provided by parent
        const wysiwygTemplateCreator = inject('wysiwygTemplateCreator');
        const templateContentEdit = inject('templateContentEdit');

        return {
            wysiwygTemplateCreator,
            templateContentEdit
        };
    },
    data() {
        return {
            editorInstance: null,
            currentZoom: '100',
            user: null,
            filename: this.templateContentEdit?.templateName || 'Untitled Template',
            showRequestExemptionSuccessDialog: false,
            previewUpdateTimeout: null,
            currentPreview: 'a',
            currentPageCount: 1,
            versions: null,
            currentTime: null,
            showSubmitSaveOrPublishDialog: false,
            submitSaveOrPublishAction: null
        };
    },

    mounted() {
        this.initializeEditor()
        this.setUserData()
        window.vueZoomRef = this;
        this.fetchTemplateVersion()

        this.updateTime();
        setInterval(() => {
            this.updateTime();
        }, 60000);
    },

    beforeUnmount() {
        if (this.editorInstance) {
            this.editorInstance.destroy(true)
        }
    },

    methods: {
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false // 24-hour format
            });
        },
        convertToAusDate(inputDate){
            return inputDate.replace(/\//g, '-');
        },
        async fetchTemplateVersion(){
            try {
                this.versions = await templateApi.fetchTemplateVersion(this.templateContentEdit.id);
            } catch (error) {
                console.error('Error fetching template version:', error);
            }
        },
        updateEditorPageCount() {
            if (!this.editorInstance) return;

            const editable = this.editorInstance.editable();
            if (!editable) return;

            const pages = editable.find(".letter-page");
            this.currentPageCount = pages.count(); // ✅ Vue will reactively update bound UI
        },
        setUserData() {
            this.user = JSON.parse(localStorage.getItem('user'))
            console.log("dito: ")
            console.log(this.user)
        },

        handleDragStart(e) {
            const draggedText = ` <<${e.target.id}>> `;
            e.dataTransfer.setData('text/plain', draggedText);
        },

        cancelEdit() {
            this.templateContentEdit = null
            if (this.editorInstance) {
                console.log("Destroying CKEditor...");
                this.editorInstance.destroy(true);
                this.editorInstance = null;
                console.log("CKEditor destroyed.");
            }
            this.$emit('close-editor' ,this.type);
        },
        onAccordionChange(panelIndex) {
            if (panelIndex === 0) { // 0 = Document panel
                // Delay to allow DOM to render
                setTimeout(() => {
                    this.generateEditorPagePreviews();
                }, 200);
            }
        },
        openDocumentAccordion() {
            setTimeout(() => {
                this.generateEditorPagePreviews();
            }, 1000);
        },

        async generateEditorPagePreviews(images) {
            const wrapper = document.querySelector('.__pages_previews_wrapper');
            const previewA = wrapper.querySelector('.preview-a');
            const previewB = wrapper.querySelector('.preview-b');

            const [incoming, outgoing] = this.currentPreview === 'a' ? [previewB, previewA] : [previewA, previewB];

            incoming.innerHTML = '';
            incoming.style.opacity = '0';
            incoming.style.zIndex = '2';
            outgoing.style.zIndex = '1';

            if (!this.editorInstance || !this.editorInstance.editable()) {
                console.warn('Editor not ready.');
                return;
            }

            const editorBody = this.editorInstance.editable().$;
            const pages = Array.from(editorBody.querySelectorAll('.letter-page'));
            if (!pages.length) return;

            for (let i = 0; i < pages.length; i++) {
                const page = pages[i];

                try {
                    const canvas = await html2canvas(page, {
                        backgroundColor: null,
                        scale: 0.2,
                        useCORS: true,
                        windowWidth: page.scrollWidth,
                    });

                    const wrapperDiv = document.createElement('div');
                    wrapperDiv.classList.add('preview-page');
                    wrapperDiv.style.marginBottom = '16px';

                    const img = document.createElement('img');
                    img.src = canvas.toDataURL();
                    img.style.width = '100%';

                    const controls = document.createElement('div');
                    controls.style.display = 'flex';
                    controls.style.justifyContent = 'space-between';
                    controls.style.backgroundColor = '#D6D6D6';
                    controls.style.padding = '3px 10px';
                    controls.style.alignItems = 'center';

                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'page-label';
                    pageLabel.textContent = `Page ${i + 1}`;
                    pageLabel.style.fontSize = '12px';
                    pageLabel.style.color = '#666';

                    const trash = document.createElement('span');
                    trash.innerHTML = '🗑️';
                    trash.style.cursor = 'pointer';
                    trash.title = 'Delete this page';

                    trash.onclick = () => {
                        // 🔥 Remove preview
                        wrapperDiv.remove();

                        // 🔥 Remove .letter-page
                        const pageToDelete = pages[i];
                        if (pageToDelete && pageToDelete.parentNode) {
                            // 💡 Also check if the next sibling is .letter-footer
                            const next = pageToDelete.nextElementSibling;
                            if (next && next.classList.contains('letter-footer')) {
                                next.remove();
                            }
                            pageToDelete.remove();
                        }

                        // 🔁 Re-render preview from updated CKEditor DOM
                        this.generateEditorPagePreviews();
                    };

                    controls.appendChild(pageLabel);
                    controls.appendChild(trash);
                    wrapperDiv.appendChild(img);
                    wrapperDiv.appendChild(controls);
                    incoming.appendChild(wrapperDiv);
                } catch (err) {
                    console.error('Error rendering preview', err);
                }
            }

            incoming.style.transition = 'opacity 0.4s ease';
            requestAnimationFrame(() => {
                incoming.style.opacity = '1';
            });

            setTimeout(() => {
                outgoing.innerHTML = '';
                outgoing.style.opacity = '0';
            }, 400);

            this.currentPreview = this.currentPreview === 'a' ? 'b' : 'a';
            this.reindexEditorPagePreviews(incoming);
        },

        /*
        async generateEditorPagePreviewsWorkingDeletePageOnly(images) {
            const wrapper = document.querySelector('.__pages_previews_wrapper');
            const previewA = wrapper.querySelector('.preview-a');
            const previewB = wrapper.querySelector('.preview-b');

            const [incoming, outgoing] = this.currentPreview === 'a' ? [previewB, previewA] : [previewA, previewB];

            incoming.innerHTML = '';
            incoming.style.opacity = '0';
            incoming.style.zIndex = '2';
            outgoing.style.zIndex = '1';

            if (!this.editorInstance || !this.editorInstance.editable()) {
                console.warn('Editor not ready.');
                return;
            }

            const editorBody = this.editorInstance.editable().$;
            const pages = Array.from(editorBody.querySelectorAll('.letter-page'));
            if (!pages.length) return;

            for (let i = 0; i < pages.length; i++) {
                const page = pages[i];

                try {
                    const canvas = await html2canvas(page, {
                        backgroundColor: null,
                        scale: 0.2,
                        useCORS: true,
                        windowWidth: page.scrollWidth,
                    });

                    // Create wrapper for the preview image and controls
                    const wrapperDiv = document.createElement('div');
                    wrapperDiv.classList.add('preview-page');
                    wrapperDiv.style.marginBottom = '16px';

                    const img = document.createElement('img');
                    img.src = canvas.toDataURL();
                    img.style.width = '100%';

                    const controls = document.createElement('div');
                    controls.style.display = 'flex';
                    controls.style.justifyContent = 'space-between';
                    controls.style.backgroundColor = '#EBECF0';
                    controls.style.padding = '3px 10px';
                    controls.style.alignItems = 'center';

                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'page-label';
                    pageLabel.textContent = `Page ${i + 1}`;
                    pageLabel.style.fontSize = '12px';
                    pageLabel.style.color = '#666';

                    const trash = document.createElement('span');
                    trash.innerHTML = '🗑️';
                    trash.style.cursor = 'pointer';
                    trash.title = 'Delete this page';

                    trash.onclick = () => {
                        // 🧽 Remove preview
                        wrapperDiv.remove();

                        // 🧽 Remove CKEditor page
                        if (pages[i] && pages[i].parentNode) {
                            pages[i].parentNode.removeChild(pages[i]);
                        }

                        // 🔁 Re-render preview from updated CKEditor DOM
                        this.generateEditorPagePreviews();
                    };

                    controls.appendChild(pageLabel);
                    controls.appendChild(trash);
                    wrapperDiv.appendChild(img);
                    wrapperDiv.appendChild(controls);
                    incoming.appendChild(wrapperDiv);
                } catch (err) {
                    console.error('Error rendering preview', err);
                }
            }

            incoming.style.transition = 'opacity 0.4s ease';
            requestAnimationFrame(() => {
                incoming.style.opacity = '1';
            });

            setTimeout(() => {
                outgoing.innerHTML = '';
                outgoing.style.opacity = '0';
            }, 400);

            this.currentPreview = this.currentPreview === 'a' ? 'b' : 'a';
            this.reindexEditorPagePreviews(incoming);
        },
        */
        reindexEditorPagePreviews(container) {
            const pages = container.querySelectorAll('.preview-page');
            pages.forEach((el, index) => {
                const label = el.querySelector('.page-label');
                if (label) {
                    label.textContent = `Page ${index + 1}`;
                }
            });
            this.currentPageCount = pages.length;
        },

        async generateEditorPagePreviewsOldBeforeImagePageBelow() {
            const wrapper = document.querySelector('.__pages_previews_wrapper');
            const previewA = wrapper.querySelector('.preview-a');
            const previewB = wrapper.querySelector('.preview-b');

            const [incoming, outgoing] = this.currentPreview === 'a' ? [previewB, previewA] : [previewA, previewB];

            // Start with incoming hidden
            incoming.innerHTML = '';
            incoming.style.opacity = '0';
            incoming.style.zIndex = '2';
            outgoing.style.zIndex = '1';

            if (!this.editorInstance || !this.editorInstance.editable()) {
                console.warn('Editor not ready.');
                return;
            }

            const editorBody = this.editorInstance.editable().$;
            const pages = editorBody.querySelectorAll('.letter-page');
            if (!pages.length) return;

            for (const page of pages) {
                try {
                    const canvas = await html2canvas(page, {
                        backgroundColor: null,
                        scale: 0.2,
                        useCORS: true,
                        windowWidth: page.scrollWidth,
                    });

                    const img = document.createElement('img');
                    img.src = canvas.toDataURL();
                    img.style.width = '100%';
                    img.style.marginBottom = '8px';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '4px';

                    incoming.appendChild(img);
                } catch (err) {
                    console.error('Error rendering preview', err);
                }
            }

            // Fade in new previews
            incoming.style.transition = 'opacity 0.4s ease';
            requestAnimationFrame(() => {
                incoming.style.opacity = '1';
            });

            // After fade, clear the old one
            setTimeout(() => {
                outgoing.innerHTML = '';
                outgoing.style.opacity = '0';
            }, 400);

            // Switch active preview container
            this.currentPreview = this.currentPreview === 'a' ? 'b' : 'a';
        },
        getEditorPageCount() {
            if (!this.editorInstance) return 0;
            const editable = this.editorInstance.editable();
            const pages = editable.find(".letter-page");
            return pages.count(); // ✅ Number of pages
        },

        initializeEditor() {
            const loader = this.$loading.show({
                container: this.$refs.templateForm,
            });

            const initialContent =
                (this.templateContentEdit && this.templateContentEdit.content) ||
                `
                  <style>
                  p {
                  outline: none; padding: 0 0; text-align: justify; font-family: Arial; font-size: 12px;
                  }
                  </style>
                  <div class="letter-page" style="background-color: white; margin-bottom: 0; height: 1000px; width: 800px;">
                    <img contenteditable="false" src="/letter_header.png" alt="Company Logo" style="width: 100%; height: 122px; margin-top: 40px;" />

                    <div
                      class="content"
                      contenteditable="true"
                      style="background-repeat: no-repeat; background-size: contain; background-image: url('/letter_background.png'); padding: 0 1in; margin: 0; color: black; height: 700px; width: 602px;"
                      >
                      <p style="outline: none; padding: 0 0; text-align: justify; font-family: Arial; font-size: 12px;">

                      </p>
                    </div>
                  </div>
                  <div class="letter-footer" contenteditable="false" style="height: 100px!important; width: 800px!important; margin-bottom: 5px!important; border-tops: 1px dashed #acacac; background-color: white;"></div>
                `;

            const NEW_LETTER_PAGE_TEMPLATE =
                `<style>
                  p {
                    outline: none; padding: 0 0; text-align: justify; font-family: Arial; font-size: 12px;
                  }
                  </style>
                  <div class="letter-page" style="background-color: white; margin-bottom: 0; height: 1000px; width: 800px;">
                    <img contenteditable="false" src="/letter_header.png" alt="Company Logo" style="width: 100%; height: 122px; margin-top: 40px;" />

                    <div
                      class="content"
                      contenteditable="true"
                      style="background-repeat: no-repeat; background-size: contain; background-image: url('/letter_background.png'); padding: 0 1in; margin: 0; color: black; height: 700px; width: 602px;"
                      >
                      <p style="outline: none; padding: 0 0; text-align: justify; font-family: Arial; font-size: 12px;">

                      </p>
                    </div>
                  </div>
                  <div class="letter-footer" contenteditable="false" style="height: 100px!important; width: 800px!important; margin-bottom: 5px!important; border-tops: 1px dashed #acacac; background-color: white;"></div>
                `;

            if (!CKEDITOR.plugins.get("zoom")) {
                CKEDITOR.plugins.add("zoom", {
                    init: (editor) => {
                        editor.ui.addRichCombo("Zoom", {
                            label: this.currentZoom + "%",
                            title: "Zoom",
                            toolbar: "zoom",
                            panel: {
                                css: [CKEDITOR.skin.getPath("editor")],
                                multiSelect: false,
                            },
                            init: function () {
                                this.startGroup("Zoom Levels");
                                ["50", "75", "100", "125", "150"].forEach((level) => {
                                    this.add(level, `${level}%`, `${level}%`);
                                });
                            },
                            onClick: function (value) {
                                this.setValue(value);
                                const scale = parseInt(value) / 100;
                                const editorContainer = document.querySelector(".editor-container");
                                if (editorContainer) {
                                    editorContainer.style.transform = `scale(${scale})`;
                                    editorContainer.style.transformOrigin = "top center";
                                }

                                if (window.vueZoomRef) {
                                    window.vueZoomRef.currentZoom = value;
                                }
                            },
                        });
                    },
                });
            }

            this.editorInstance = CKEDITOR.replace(this.$refs.editorElement, {
                contentsCss: [
                    'body { background-color: #D3D3D3!important; font-family: Arial, sans-serif; font-size: 12px;}',
                    '.cke_inner { background-color: #d3d3d3!important; }',
                ],
                removePlugins: "maximize,resize,notification",
                extraPlugins: "uploadimage,zoom,font,toolbar",
                allowedContent: true,
                filebrowserUploadUrl: "/upload-image",
                filebrowserUploadMethod: "form",
                toolbar: [
                    { name: "basicstyles", items: ["Bold", "Italic", "Underline", "Strike", "Font", "FontSize"] },
                    { name: "paragraph", items: ["BulletedList", "NumberedList", "Outdent", "Indent"] },
                    /*{ name: "links", items: ["Link", "Unlink"] },*/
                    /*{ name: "insert", items: ["Image"] },*/
                    { name: "undo", items: ["Undo", "Redo"] },
                    /*{ name: "zoom", items: ["Zoom"] },*/
                ],
                fontSize: {
                    options: ["10px", "12px", "14px", "16px", "18px", "20px", "24px"],
                    supportAllValues: true,
                },
                height: (() => {
                    const tempDiv = document.createElement("div");
                    tempDiv.innerHTML = initialContent;
                    const pageCount = tempDiv.querySelectorAll(".letter-page").length || 1;
                    return pageCount * 1122;
                })(),
                width: 830,
                on: {
                    instanceReady: () => {
                        const toolbar = this.editorInstance.ui.space("top");
                        if (toolbar && toolbar.$ && this.$refs.toolbarContainer) {
                            this.$refs.toolbarContainer.appendChild(toolbar.$);
                        }

                        this.editorInstance.setData(initialContent, {
                            callback: () => {
                                const editable = this.editorInstance.editable();
                                const firstP = editable.findOne(".content p");

                                if (firstP) {
                                    if (firstP.getText().trim() === "Start typing here...") {
                                        firstP.setText("");
                                    }

                                    this.editorInstance.focus();
                                    const range = new CKEDITOR.dom.range(this.editorInstance.document);
                                    range.moveToElementEditEnd(firstP); // ✅ place cursor at the end
                                    this.editorInstance.getSelection().selectRanges([range]);
                                }

                                loader.hide();
                            },
                        });

                        let hasInsertedCursor = false;

                        this.editorInstance.on("focus", () => {
                            if (hasInsertedCursor) return;

                            const editable = this.editorInstance.editable();
                            const firstP = editable.findOne(".content p");

                            if (firstP) {
                                this.editorInstance.focus();
                                const range = new CKEDITOR.dom.range(this.editorInstance.document);
                                range.moveToElementEditEnd(firstP); // ✅ place cursor at the end
                                this.editorInstance.getSelection().selectRanges([range]);
                                hasInsertedCursor = true;
                            }
                        });

                        this.editorInstance.on("contentDom", () => {
                            const editable = this.editorInstance.editable();

                            // Drop support
                            editable.attachListener(editable, "dragover", (evt) => {
                                evt.data.preventDefault();
                            });

                            editable.attachListener(editable, "drop", (evt) => {
                                evt.data.preventDefault();

                                const draggedText = evt.data.$.dataTransfer.getData("text/plain");
                                const clickX = evt.data.$.clientX;
                                const clickY = evt.data.$.clientY;

                                const contentBlock = evt.data
                                    .getTarget()
                                    .getAscendant((el) => el.hasClass && el.hasClass("content"), true);

                                if (!contentBlock) return;

                                const spanList = contentBlock.find("span");
                                const pList = contentBlock.find("p");
                                const allTextElements = [];

                                for (let i = 0; i < spanList.count(); i++) {
                                    allTextElements.push(spanList.getItem(i));
                                }
                                for (let i = 0; i < pList.count(); i++) {
                                    allTextElements.push(pList.getItem(i));
                                }

                                if (allTextElements.length === 0) return;

                                let nearestElement = null;
                                let shortestDistance = Infinity;

                                allTextElements.forEach((el) => {
                                    const rect = el.$.getBoundingClientRect();
                                    const dx = rect.left + rect.width / 2 - clickX;
                                    const dy = rect.top + rect.height / 2 - clickY;
                                    const distance = Math.sqrt(dx * dx + dy * dy);

                                    if (distance < shortestDistance) {
                                        shortestDistance = distance;
                                        nearestElement = el;
                                    }
                                });

                                if (nearestElement) {
                                    const range = this.editorInstance.createRange();
                                    range.moveToElementEditEnd(nearestElement);
                                    range.select();
                                    this.editorInstance.insertText(draggedText);
                                }
                            });

                            // ✅ FIX: Cursor appears on click inside content
                            editable.attachListener(editable, "mouseup", (evt) => {
                                //allow highlighting the text
                                setTimeout(() => {
                                    const selection = this.editorInstance.getSelection();
                                    if (!selection || !selection.isCollapsed) return;

                                    const clickedElement = evt.data.getTarget();
                                    const contentBlock = clickedElement.getAscendant(
                                        (el) => el.hasClass && el.hasClass("content"),
                                        true
                                    );
                                    if (!contentBlock) return;

                                    const isTextElement = clickedElement.getAscendant((el) => {
                                        const name = el.getName && el.getName();
                                        return name === "span" || name === "p";
                                    }, true);
                                    if (isTextElement) return;

                                    const spanList = contentBlock.find("span");
                                    const pList = contentBlock.find("p");
                                    const allTextElements = [];

                                    for (let i = 0; i < spanList.count(); i++) {
                                        allTextElements.push(spanList.getItem(i));
                                    }
                                    for (let i = 0; i < pList.count(); i++) {
                                        allTextElements.push(pList.getItem(i));
                                    }

                                    if (allTextElements.length === 0) return;

                                    const clickX = evt.data.$.clientX;
                                    const clickY = evt.data.$.clientY;

                                    let nearestElement = null;
                                    let shortestDistance = Infinity;

                                    allTextElements.forEach((el) => {
                                        const rect = el.$.getBoundingClientRect();
                                        const dx = rect.left + rect.width / 2 - clickX;
                                        const dy = rect.top + rect.height / 2 - clickY;
                                        const distance = Math.sqrt(dx * dx + dy * dy);

                                        if (distance < shortestDistance) {
                                            shortestDistance = distance;
                                            nearestElement = el;
                                        }
                                    });

                                    if (nearestElement) {
                                        this.editorInstance.focus();
                                        const range = this.editorInstance.createRange();
                                        //cursor to line start
                                        range.moveToElementEditStart(nearestElement);
                                        //cursor to line end
                                        range.moveToElementEditEnd(nearestElement);
                                        this.editorInstance.getSelection().selectRanges([range]);
                                    }
                                }, 10)
                            });

                            editable.attachListener(editable, "keydown", (evt) => {
                                if (evt.data.getKey() === 9) { // 9 is Tab
                                    evt.data.preventDefault(); // Prevent focus shift

                                    const indentString = '\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0'; // 4 non-breaking spaces

                                    this.editorInstance.insertHtml(indentString); // Insert at caret
                                }
                            });
                        });

                        this.editorInstance.on("key", (evt) => {
                            const editable = this.editorInstance.editable();
                            const contentDivs = editable.find(".content");
                            const lastContentDiv = contentDivs.getItem(contentDivs.count() - 1);
                            const maxHeight = 800;

                            const isEnter = evt.data.keyCode === 13;

                            if (isEnter && lastContentDiv.$.scrollHeight > maxHeight) {
                                // Check if the current selection is inside the last <p> of the last .content
                                const selection = this.editorInstance.getSelection();
                                const startElement = selection.getStartElement();
                                const parentParagraph = startElement.getAscendant("p", true);

                                // Get last <p> inside the last page's .content
                                const allPs = lastContentDiv.find("p");
                                const lastP = allPs.getItem(allPs.count() - 1);

                                // ✅ Only insert a new page if we're in the last <p> of the last content block
                                if (parentParagraph && lastP && parentParagraph.$ === lastP.$) {
                                    evt.cancel(); // Prevent normal enter behavior

                                    const currentData = this.editorInstance.getData();
                                    const newData = currentData + NEW_LETTER_PAGE_TEMPLATE;

                                    this.editorInstance.setData(newData, {
                                        callback: () => {
                                            const editable = this.editorInstance.editable();
                                            const letterPages = editable.find(".letter-page");

                                            // 🔧 Dynamically set height: 1122 * number of pages
                                            const newHeight = 1130 * letterPages.count();
                                            this.editorInstance.resize(this.editorInstance.container.$.offsetWidth, newHeight);

                                            if (letterPages.count() > 0) {
                                                const lastPage = letterPages.getItem(letterPages.count() - 1);
                                                const newP = lastPage.findOne(".content p");

                                                if (newP) {
                                                    this.editorInstance.focus();
                                                    const range = new CKEDITOR.dom.range(this.editorInstance.document);
                                                    range.moveToElementEditStart(newP);
                                                    this.editorInstance.getSelection().selectRanges([range]);
                                                }
                                            }
                                            //this.generateEditorPagePreviews(); // 👈 GENERATE PREVIEW
                                            this.$refs.pagesWrapper.style.overflowY = 'scroll'; //set wrapper of preview to scroll
                                        }
                                    });
                                }
                            }
                        });

                        this.editorInstance.on("change", () => {
                            const data = this.editorInstance.getData();
                            this.$emit("update:modelValue", data);

                            this.updateEditorPageCount();

                            // Debounce preview generation
                            if (this.previewUpdateTimeout) {
                                clearTimeout(this.previewUpdateTimeout);
                            }

                            this.previewUpdateTimeout = setTimeout(() => {
                                this.generateEditorPagePreviews();
                            }, 300); // delay 500ms after last change
                        });

                        // Trigger on manual commands like Enter key (new page)
                        //this.editorInstance.on("afterCommandExec", () => {
                            //alert("afterCommandExec")
                            //this.generateEditorPagePreviews();
                        //});
                    },
                },
            });

            // Auto-remove warning messages
            let counter = 0;
            const interval = setInterval(() => {
                const warning = document.querySelector(".cke_notification_warning");
                if (warning) {
                    warning.remove();
                    clearInterval(interval);
                }
                counter++;
                if (counter > 300) clearInterval(interval);
            }, 1);
        },



        initializeEditorOLD() {
            const loader = this.$loading.show({
                container: this.$refs.templateForm
            });

            const initialContent = (this.templateContentEdit && this.templateContentEdit.content) || `
            <div class="letter-page" contenteditable="false" style="background-color: white; margin-bottom: 0; height: 1000px;">
              <img contenteditable="false" src="/letter_header.png" alt="Company Logo" style="width: 100%; height: 122px;margin-top: 40px;" />
              <div
                class="content"
                style=" background-repeat: no-repeat;
                        background-size: contain;
                        background-image: url('/letter_background.png');
                        padding: 0;
                        margin: 0;
                        color: black;
                        height: 700px;
                        width: 100%;"
              >
                  <div  contenteditable="true" style="outline: none; padding: 0 1in!important;text-align: justify!important;" >
                      <p style="line-height: 0.5">
                        <span style="font-size: 12px; font-family: Arial;">Start typing here...</span>
                      </p>
                  </div>
              </div>
            </div>
          `;


            // Add Zoom plugin only if not added yet
            if (!CKEDITOR.plugins.get('zoom')) {
                CKEDITOR.plugins.add('zoom', {
                    init: (editor) => {
                        editor.ui.addRichCombo('Zoom', {
                            label: this.currentZoom + '%',
                            title: 'Zoom',
                            toolbar: 'zoom',
                            panel: {
                                css: [CKEDITOR.skin.getPath('editor')],
                                multiSelect: false,
                            },
                            init: function () {
                                this.startGroup('Zoom Levels');
                                ['50', '75', '100', '125', '150'].forEach(level => {
                                    this.add(level, `${level}%`, `${level}%`);
                                });
                            },
                            onClick: function (value) {
                                this.setValue(value);
                                //this.currentZoom = value;
                                this.setValue(value);
                                const scale = parseInt(value) / 100;
                                const editorContainer = document.querySelector('.editor-container');
                                editorContainer.style.transform = `scale(${scale})`;
                                editorContainer.style.transformOrigin = 'top center';

                                // If you still want to update your Vue data:
                                if (window.vueZoomRef) {
                                    window.vueZoomRef.currentZoom = value;
                                }
                            }
                        });
                    }
                });
            }

            //height and with here is the gray ckeditor iframe #d3d3d3
            this.editorInstance = CKEDITOR.replace(this.$refs.editorElement, {
                contentsCss: ['body { background-color: white!important; box-shadow: none!important; font-family: Arial, sans-serif; font-size: 12px; } .cke_inner {  background-color: #d3d3d3!important; }'],
                removePlugins: 'maximize,resize,notification',
                extraPlugins: 'uploadimage,zoom,font,toolbar',
                allowedContent: true,
                filebrowserUploadUrl: '/upload-image',
                filebrowserUploadMethod: 'form',
                toolbar: [
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Font', 'FontSize'] },
                    { name: 'paragraph', items: ['BulletedList', 'NumberedList', 'Outdent', 'Indent'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image'] },
                    { name: 'undo', items: ['Undo', 'Redo'] },
                    { name: 'zoom', items: ['Zoom'] },
                ],
                fontSize: {
                    options: ['10px', '12px', '14px', '16px', '18px', '20px', '24px'],
                    supportAllValues: true
                },
                height: 1122,
                width: 800,
                on: {
                    instanceReady: () => {
                        const toolbar = this.editorInstance.ui.space('top');
                        if (toolbar && toolbar.$ && this.$refs.toolbarContainer) {
                            this.$refs.toolbarContainer.appendChild(toolbar.$);
                        }


                        this.editorInstance.setData(initialContent, {
                            callback: () => {
                                this.editorInstance.execCommand('font', 'Arial');
                                this.editorInstance.execCommand('fontsize', '12px');

                                const editable = this.editorInstance.editable();
                                const firstSpan = editable.findOne('span');

                                if (firstSpan) {
                                    if (firstSpan.getText().trim() === 'Start typing here...') {
                                        firstSpan.setText('');
                                    }

                                    this.editorInstance.focus();
                                    const range = new CKEDITOR.dom.range(this.editorInstance.document);
                                    range.moveToPosition(firstSpan, CKEDITOR.POSITION_AFTER_START);
                                    const selection = this.editorInstance.getSelection();
                                    selection.selectRanges([range]);
                                }
                            }
                        });

                        let hasInsertedCursor = false; // <- put this at the top of instanceReady()

                        this.editorInstance.on('focus', () => {
                            if (hasInsertedCursor) return;

                            const editable = this.editorInstance.editable();
                            const firstSpan = editable.findOne('span');

                            if (firstSpan) {
                                if (firstSpan.getText().trim() === 'Start typing here...') {
                                    firstSpan.setText('');
                                }

                                const range = this.editorInstance.createRange();
                                range.moveToElementEditStart(firstSpan);
                                this.editorInstance.getSelection().selectRanges([range]);

                                hasInsertedCursor = true;
                            }
                        });
                        this.editorInstance.on('contentDom', () => {
                            const editable = this.editorInstance.editable();

                            editable.attachListener(editable, 'dragover', (evt) => {
                                evt.data.preventDefault(); // Allow drop
                            });

                            editable.attachListener(editable, 'drop', (evt) => {
                                evt.data.preventDefault(); // Prevent default behavior

                                const draggedText = evt.data.$.dataTransfer.getData('text/plain');
                                const clickX = evt.data.$.clientX;
                                const clickY = evt.data.$.clientY;

                                // Find the nearest <span> or <p>
                                const contentBlock = evt.data.getTarget().getAscendant(el => {
                                    return el.hasClass && el.hasClass('content');
                                }, true);

                                if (!contentBlock) return;

                                const spanList = contentBlock.find('span');
                                const pList = contentBlock.find('p');
                                const allTextElements = [];

                                for (let i = 0; i < spanList.count(); i++) {
                                    allTextElements.push(spanList.getItem(i));
                                }
                                for (let i = 0; i < pList.count(); i++) {
                                    allTextElements.push(pList.getItem(i));
                                }

                                if (allTextElements.length === 0) return;

                                let nearestElement = null;
                                let shortestDistance = Infinity;

                                allTextElements.forEach(el => {
                                    const rect = el.$.getBoundingClientRect();
                                    const dx = rect.left + rect.width / 2 - clickX;
                                    const dy = rect.top + rect.height / 2 - clickY;
                                    const distance = Math.sqrt(dx * dx + dy * dy);

                                    if (distance < shortestDistance) {
                                        shortestDistance = distance;
                                        nearestElement = el;
                                    }
                                });

                                if (nearestElement) {
                                    const range = this.editorInstance.createRange();
                                    range.moveToElementEditEnd(nearestElement); // Or EditStart if you prefer
                                    range.select();

                                    this.editorInstance.insertText(draggedText);
                                }
                            });
                        });

                        /*
                        this.editorInstance.on('contentDom', () => {
                            const editable = this.editorInstance.editable();

                            editable.attachListener(editable, 'mouseup', (evt) => {
                                const selection = this.editorInstance.getSelection();

                                // Don't override text selection behavior
                                if (!selection || !selection.isCollapsed) return;

                                const clickedElement = evt.data.getTarget();

                                // Make sure the click happened inside .content
                                const contentBlock = clickedElement.getAscendant(el => {
                                    return el.hasClass && el.hasClass('content');
                                }, true);

                                if (!contentBlock) return;

                                // If click was already on a <span> or <p>, don't interfere
                                const isTextElement = clickedElement.getAscendant(el => {
                                    const name = el.getName && el.getName();
                                    return name === 'span' || name === 'p';
                                }, true);

                                if (isTextElement) return;

                                // Collect all span and p elements
                                const spanList = contentBlock.find('span');
                                const pList = contentBlock.find('p');
                                const allTextElements = [];

                                for (let i = 0; i < spanList.count(); i++) {
                                    allTextElements.push(spanList.getItem(i));
                                }
                                for (let i = 0; i < pList.count(); i++) {
                                    allTextElements.push(pList.getItem(i));
                                }

                                if (allTextElements.length === 0) return;

                                const clickX = evt.data.$.clientX;
                                const clickY = evt.data.$.clientY;

                                let nearestElement = null;
                                let shortestDistance = Infinity;

                                allTextElements.forEach(el => {
                                    const rect = el.$.getBoundingClientRect();
                                    const dx = rect.left + rect.width / 2 - clickX;
                                    const dy = rect.top + rect.height / 2 - clickY;
                                    const distance = Math.sqrt(dx * dx + dy * dy);

                                    if (distance < shortestDistance) {
                                        shortestDistance = distance;
                                        nearestElement = el;
                                    }
                                });

                                if (nearestElement) {
                                    this.editorInstance.focus();
                                    const range = this.editorInstance.createRange();
                                    range.moveToElementEditStart(nearestElement);
                                    this.editorInstance.getSelection().selectRanges([range]);
                                }
                            });
                        });
                        */

                        // Prevent exceeding page height
                        this.editorInstance.on('key', (evt) => {
                            const editable = this.editorInstance.editable();
                            const contentHeight = editable.$.scrollHeight;
                            const maxHeight = 1000;

                            const isEnter = evt.data.keyCode === 13;
                            const isTypingKey = evt.data.keyCode >= 32 && evt.data.keyCode <= 126;

                            if ((isEnter || isTypingKey) && contentHeight > maxHeight) {
                                //alert("end of line: " + contentHeight);
                                //console.log("tried to enter new line in the last line or row of the paper.")
                                if(evt.data.keyCode === 13) {
                                    evt.cancel();
                                }
                            }
                            console.log(contentHeight + ' | ' + maxHeight)
                        });

                        this.editorInstance.on('change', () => {
                            const data = this.editorInstance.getData();
                            this.$emit('update:modelValue', data);
                        });
                    }
                }
            });

            // Remove warning notifications
            this.editorInstance.on('instanceReady', () => {
                const toolbar = this.editorInstance.ui.space('top');
                if (toolbar && toolbar.$ && this.$refs.toolbarContainer) {
                    this.$refs.toolbarContainer.appendChild(toolbar.$);
                }

                let counter = 0;
                const interval = setInterval(() => {
                    const warning = document.querySelector('.cke_notification_warning');
                    if (warning) {
                        warning.remove();
                        clearInterval(interval);
                        loader.hide();
                    }
                    counter++;
                    console.log(counter);
                }, 1);
            });
        },


        insertNewLetterPage() {
            const newPageHtml = `
                <div class="letter-page" style="background-color: white; margin-bottom: 20px;">
                  <div class="letterhead">
                    <div class="logo">
                      <img src="/letter_header.png" alt="Company Logo" style="width: 100%; height: auto; margin-top: 40px;" />
                    </div>
                  </div>
                  <div class="content" contenteditable="true" style="background-image: url('/letter_background.png'); background-size: cover; padding: 0px; color: black; height: 800px; width: 400px">
                    <p style="margin-left: 60px; margin-right: 60px;"></p>
                  </div>
                </div>
              `;

            const currentData = this.editorInstance.getData();
            const newData = currentData + newPageHtml;
            this.editorInstance.setData(newData, {
                callback: () => {
                    const range = this.editorInstance.createRange();
                    const newElement = this.editorInstance.document.$.querySelectorAll('.letter-page:last-child p')[0];
                    if (newElement) {
                        const element = this.editorInstance.document.getById(newElement.id)
                            || this.editorInstance.document.getElementsByTag('p').getItem(-1);
                        range.moveToElementEditStart(element);
                        this.editorInstance.getSelection().selectRanges([range]);
                    }
                }
            });
        },
        submitSaveOrPublish(status) {
            this.showSubmitSaveOrPublishDialog = true
            this.submitSaveOrPublishAction = status
        },
        async submitSaveOrPublishExecute(status) {

            const loader = this.$loading.show({
                container: this.$refs.templateForm
            });

            const screenshotFile = await this.captureEditorContent();
            const store = useAuthStore()

            try {
                const formData = new FormData();
                formData.append('owner_employee_id', this.user.employee.id);
                formData.append('filename', this.filename);
                formData.append('content', this.editorInstance.getData());
                formData.append('type', this.type);
                formData.append('status', status);
                formData.append('screenshot', screenshotFile);
                formData.append('activity', store.user.name + ' ' + status + ' this template.')

                if (this.templateContentEdit) {
                    await templateApi.updateTemplatePatch(this.templateContentEdit.id, formData);
                } else {
                    await templateApi.storeTemplate(formData);
                }

                this.showRequestExemptionSuccessDialog = true;
            } catch (err) {
                this.$toast.error('Something went wrong while submitting the leave request.');
                console.error(err?.response?.data || err);
                this.$emit('close-editor' ,type);
            } finally {
                loader.hide();
            }
        },

        async captureEditorContent() {
            const captureDiv = this.$refs.captureArea;
            const contentHtml = this.editorInstance.getData();

            const styles = `
              <style>
                p {
                  outline: none;
                  padding: 0 0;
                  text-align: justify;
                  font-family: Arial;
                  font-size: 12px;
                }

                .letter-page {
                  background-color: white!important;
                  margin-bottom: 0;
                  height: 1000px;
                  width: 800px;
                }

                .letter-page .content {
                  background-repeat: no-repeat;
                  background-size: contain;
                  background-image: url('/letter_background.png');
                  padding: 0 1in;
                  margin: 0;
                  color: black;
                  height: 700px!important;
                  width: 800px!important;
                }
              </style>
            `;
            captureDiv.innerHTML = styles + contentHtml;

            captureDiv.style.display = 'block';

            try {
                const canvas = await html2canvas(captureDiv, {
                    useCORS: true,
                    scale: 2
                });

                const image = canvas.toDataURL("image/png");
                const imageFile = this.dataURLtoFile(image, "ckeditor-screenshot.png");

                return imageFile;

            } catch (err) {
                console.error("Screenshot failed", err);
            } finally {
                captureDiv.style.display = 'none';
            }
        },

        dataURLtoFile(dataURL, filename) {
            const arr = dataURL.split(','), mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, { type: mime });
        },
    }
};
</script>

<style scoped lang="scss">
@use "@layouts/styles/variables" as variables;

.ck.ck-content {
    background: url('/letter_background.png') no-repeat center center;
    background-size: cover;
}

.__stander_field_edit {
    color: #D0D3D9;
    font-size: 18px;
    display: inline-block;
    margin: 8px 0px;
}

.__stander_field_settings {
    color: #D0D3D9;
    font-size: 18px;
    display: inline-block;
    margin: 8px 0px;
}
.__standard_field_ribbon {
    color: #D0D3D9;
    font-size: 18px;
    display: inline-block;
    margin: 8px 0px;
}

.__six-dot-icon::before {
    content: "\F10D5"; /* Unicode for mdi-dots-grid */
    display: inline-block;
    font-family: 'Material Design Icons';
}

.__draggable_item {
    font-size: small;
    border: variables.$layout_content_boarder;
    display: flex;
    align-items: center; /* Align vertically */
    margin-bottom: 2px;
    border-radius: 3px;
    padding: 4px 4px;
}

.__draggable_items_wrapper {
    height: 90%;

    .__draggable_items_leftside_wrapper {
        //background-color: yellow;
        flex: 0.8;
        padding: 8px 8px;
        border-right: variables.$layout_content_boarder;;
    }
    .__draggable_items_rightside_wrapper {
        //background-color: #94a3b8;
        flex: 6;
        padding: 10px 10px;
    }
}

.__draggable_search_box {
    height: 40px;
    border: variables.$layout_content_boarder;
}

.__editor_draggable_fields_wrapper {
    border: variables.$layout_content_boarder;
    flex: 1.2;
}

.__main_editor_wrapper {
    flex: 6;
}

.editor-wrapper {
    width: 100%;
    height: 95vh;
    background-color: #d3d3d3; /* Gray background */
    padding: 0;
    display: flex;
    justify-content: flex-start;
    flex-direction: column;
    align-items: flex-start;
    border: variables.$layout_content_boarder;

    /*
    width: 100%;
    height: 95vh;
    background-color: #d3d3d3;
    padding: 0;
    display: flex;
    justify-content: center;
    flex-direction: column;
    align-items: center;
    border: variables.$layout_content_boarder;
    */
}

.cke_contents {

    background-color: #d3d3d3!important;
}

.cke_editable {
    //width: 816px; //8.5 inches
    height: 1056px; //11 inches
    background-color: #d3d3d3!important;
    //padding: 40px;
    //box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    overflow: hidden; //Prevent infinite growth
    //box-sizing: border-box;
    //position: relative;
}

.toolbar-container {
    width: 100%; /* Make the toolbar cover the full wrapper width */
    background-color: #fff; /* Match the editor background */
    padding: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
}

.cke_inner {
    background-color: #d3d3d3!important;
}

:deep(.cke_inner) {
    background-color: #d3d3d3 !important;
}

/*------------------------  GRAY EDITOR CONTAINER  --------------------------------*/
.editor-container {
    //background-color: red!important;
    background-color: #d3d3d3!important;
    width: 100%;
    //height: 700px;  /* Short bond paper height */
    box-shadow: none;
    //position: relative; /* Ensure relative positioning for child elements */
    overflow-y: scroll;   /* Prevent overflow from child elements */
    padding-top: 50px;

    display: flex;
    //justify-content: center; /* vertical up and down movement centering */
    flex-direction: column;
    align-items: center;  /* horizontal left and right movement centering */
}


.editor-container textarea {
    justify-content: center;
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    resize: none;
    padding: 0;
    //margin: 40px 0 0 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 16px;
    line-height: 1.5;
    background-color: transparent;
}


.__editor_header_wrapper {
    display: flex;
}

.__editor_back_button {
    border-radius:6px;
    margin: auto 20px;
    color: #858587;
    background-color: #dfe0e3;
    height:40px!important;
}

.__history_preview_button {
    border-radius: 3px!important;
    margin: 12px 0!important;
    color: #858587!important;
    border: 1px solid #dfe0e3!important;
    padding: 3px 10px!important;
    font-size: 10px;
}

.__editor_filename_wrapper {
    display: block;
    height: 50px;
    align-self: center;
    max-width: 400px;
}

.__editor_filename_wrapper small {
    color: #808494;
}

.__editor_filename {
    font-size: 1.2em;
    font-weight: bold;
    color: #3C395C;
    border: 1px solid #d6d3d1;
    padding: 0 5px;
}

/*.letter-page {
    width: 816px;
    height: 950px;
    margin: 20px auto;
    padding: 40px;
    background-color: white;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    box-sizing: border-box;
    overflow: hidden;
    position: relative;
}*/

body {
    background-color: #f5f5f5 !important;
}

.__screenshot_area {
    height: 800px;
    width: 800px!important;
    padding-top: 30px;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    display: none;
}

.cke_bottom cke_reset_all {
    display: none!important;
}

.cke_wysiwyg_frame {
    background-color: red!important;
}

.__stats_strip {
    height: 30px;
    margin: 0 300px;
    padding: 20px 30px;
}

.__status_strip_draft {
    background-color: #F2F2F5;
    color: #808097;
}
.__status_strip_in_progress {
    background-color: #FDE1D0;
    color: #F82D03;
}
.__status_strip_in_completed {
    background-color: #DCFCE7;
    color: #22C55E;
}

.fade-in-preview {
    opacity: 0;
    transition: opacity 0.5s ease-in;
}
.fade-in-preview.visible {
    opacity: 1;
}

.__vesions_card {
    margin: 10px 0;
    padding: 10px 10px 0 10px;
}

@media (min-width: 1440px) {
    .__pages_previews_wrapper {
        min-height: 500px;
    }
    .__pages_previews{
        padding: 5px!important;
    }
}
@media (min-width: 1920px) {
    .__pages_previews_wrapper {
        min-height: 900px;
    }
    .__pages_previews{
        padding: 35px!important;
    }
}
</style>

<style>
    .cke_bottom {
        visibility: hidden!important;
    }
</style>
