<template>
    <div ref="templateForm" class="tw-flex">
        <div class="__main_editor_wrapper">
            <div class="editor-wrapper">
                <div ref="toolbarContainer" class="toolbar-container" style="display: none"></div>
                <div class="editor-container">
                    <div style="margin-top: 1800px" contenteditable="false"  ref="editorElement"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue"
import templateApi from "@/api/template/templateApi";
import html2canvas from 'html2canvas';
import { inject, ref } from 'vue'

export default defineComponent({
    name: 'TemplatePendingApproval',
    props: {
        modelValue: {
            type: null,
            required: false
        },
        type: {
            type: String,
            required: true
        },
    },
    data() {
        return {
            editorInstance: null,
            currentZoom: '100',
            user: null,
            filename: this.templateContentEdit?.templateName || 'Untitled Template',
            //showRequestExemptionSuccessDialog: false,
        };
    },
    mounted() {
        this.initializeEditor()
        //this.setUserData()
        window.vueZoomRef = this;

    },
    unmounted() {
        if (this.editorInstance) {
            this.editorInstance.destroy(true)
        }
        console.log("UNMOUNTED TEMPLATE - PENDING APPROVAL");
    },
    beforeUnmount() {
        if (this.editorInstance) {
            this.editorInstance.destroy(true)
        }
    },
    watch: {
        readonly(val) {
            if (this.editorInstance) {
                this.editorInstance.setReadOnly(val);
            }
        }
    },
    methods: {
        waitUntilReady() {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 0);
            });
        },
        getTemplate() {
          return `<div class="letter-page" contenteditable="false" style="background-color: white; margin-bottom: 0; height: 1000px;"><img alt="Company Logo" contenteditable="false" src="/letter_header.png" style="width: 100%; height: auto;margin-top: 40px;" />
<div class="content" style=" background-repeat: no-repeat;
                        background-size: contain;
                        background-image: url('/letter_background.png');
                        padding: 0;
                        margin: 0;
                        color: black;
                        height: 700px;
                        width: 100%;">
<div contenteditable="true" style="outline: none;">
<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Flash undefined</span></p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Address of Flash</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">6-25-2025</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Dear Flash,</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><strong><span style="font-size:18px;">OFFER OF EMPLOYMENT</span></strong></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">It is with pleasure we formally confirm our offer of employment with &nbsp;Katherine West Health Board in the position of &nbsp;Aboriginal Lifestyle Coach reporting to Flash Gordon-GeneralManager, Digital Ninja.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">You will be employed on a full time basis As per the Jirntangku Miyrta Enterprise Agreement, you will be paid &nbsp;$123,123.00 per annum per ordinary hours.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">You are entitled to the following allowances:</span></p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Sample Allowance 7</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Your expected start date will be 6-27-2025. Please confirm acceptance of this offer so that we can issue your Terms of Appointment instrument.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Your standard hours of work will be 37.5 per week, worked between the hours of 8am-4:30pm, Monday to Friday, or as mutually agreed with your manager. You will be expected to participate in the After Hours On-Call Roster as part of your employment agreement. You will be based at Katherine with travel arrangements to the KWHB sites as per the requirements of the role.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">This offer is conditional upon you signing a Terms of Appointment Instrument that sets out the terms and conditions of your employment, either before or on commencement of employment. The terms of Appointment instrument will be issued to you within two business days of confirmation of your acceptance of the offer and start date.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Please see the Jirntangku Miyrta Enterprise Agreement 2021 - 2024 on the Fair Work website for more information about your employment. The Position Description is also attached for your reference.</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Congratulations!</span></p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"> </p>

<p style="margin: 0 75px;"><span style="font-size:12px;">Katherine West Health Board</span></p>

<p style="margin: 0 75px;"><span style="font-size:12px;">hr@kwhb.com.au</span></p>

<p style="margin: 0 75px;"><span style="font-size:12px;">+61 8 8971 9300</span></p>
</div>
</div>
</div>
<div class="letter-page" contenteditable="false" style="background-color: white; margin-bottom: 0; height: 1000px;"><img alt="Company Logo" contenteditable="false" src="/letter_header.png" style="width: 100%; height: auto;margin-top: 40px;" />
<div class="content" style=" background-repeat: no-repeat;
                                         background-size: contain;
                                         background-image: url('/letter_background.png');
                                         padding: 0;
                                         margin: 0;
                                         color: black;
                                         height: 700px;
                                         width: 100%;">
<div contenteditable="true" style="outline: none;">
<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;">Application within 48 hours, your employment maybe terminated or suspended without pay&nbsp;until you do so.&nbsp;</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;">Signed by the Employee:&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;)</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;)&nbsp; &nbsp; &nbsp; .......................................................................</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;"><strong>Date:&nbsp;</strong> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )&nbsp; &nbsp; &nbsp; ........................................................................</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;">Signed by &ltSinon Cooney,&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;"><strong>Chief Executive Officer</strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )&nbsp; &nbsp; &nbsp; ........................................................................</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;">on behalf of Katherine West&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;)</span></p>

<p style="margin: 0 1in;"><span style="font-size:14px;">Health Board</span></p>

<p style="margin: 0 1in;"> </p>

<p style="margin: 0 1in;"><span style="font-size:14px;"><strong>Date:</strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )&nbsp; &nbsp; &nbsp; ........................................................................</span></p>
</div>
</div>
</div>`
        },
        setUserData() {
            this.user = JSON.parse(localStorage.getItem('user'))
            //console.log("dito: ")
            //console.log(this.user)
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
            this.$emit('close-editor');
        },
        initializeEditor() {
            const loader = this.$loading.show({
                container: this.$refs.templateForm
            });

            // ✅ Add this after toolbar is appended or editor is set
            const initialZoom = '125';
            const scale = parseInt(initialZoom) / 100;
            //const editorContainer = document.querySelector('.editor-container');
            const editorContainer = this.$el.querySelector('.editor-container');
            if (editorContainer) {
                editorContainer.style.transform = `scale(${scale})`;
                editorContainer.style.transformOrigin = 'top center';
            }
            this.currentZoom = initialZoom;

            const initialContent = this.modelValue

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
                                //const editorContainer = document.querySelector('.editor-container');
                                const editorContainer = window.vueZoomRef?.$el.querySelector('.editor-container');
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

            this.editorInstance = CKEDITOR.replace(this.$refs.editorElement, {
                contentsCss: ['body { background-color: #D3D3D3!important; box-shadow: none!important; font-family: Arial, sans-serif; font-size: 12px; } .cke_inner {  background-color: #d3d3d3!important; }'],
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
                height: (() => {
                    const tempDiv = document.createElement("div");
                    tempDiv.innerHTML = initialContent;
                    const pageCount = tempDiv.querySelectorAll(".letter-page").length || 1;
                    return pageCount * 1122;
                })(),
                width: 840,
                on: {
                    instanceReady: () => {
                        const toolbar = this.editorInstance.ui.space('top');
                        if (toolbar && toolbar.$ && this.$refs.toolbarContainer) {
                            this.$refs.toolbarContainer.appendChild(toolbar.$);
                        }
                        this.editorInstance.setData(initialContent,{
                            callback: () => {
                                this.editorInstance.execCommand('font', 'Arial');
                                this.editorInstance.execCommand('fontsize', '18px');

                                //prevent editing
                                this.editorInstance.setReadOnly(true)
                                const editable = this.editorInstance.editable();
                                if (editable) {
                                    const innerCE = editable.$.querySelectorAll('[contenteditable="true"]');
                                    innerCE.forEach(el => {
                                        el.removeAttribute('contenteditable');
                                    });
                                }
                            }
                        });

                        // Prevent exceeding page height
                        this.editorInstance.on('key', (evt) => {
                            evt.cancel();
                            ////const editable = this.editorInstance.editable();
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

        async submitSaveOrPublish(status) {
            const loader = this.$loading.show({
                container: this.$refs.templateForm
            });
            const screenshotFile = await this.captureEditorContent();
            //TODO: fix issue with patch
            //alert(this.type || JSON.stringify(this.templateContentEdit))

            try {
                if (!confirm(`Are you sure you want to save as ${status}?`)) {
                    return;
                }

                const formData = new FormData();
                formData.append('owner_employee_id', this.user.employee.id);
                formData.append('filename', this.filename);
                formData.append('content', this.editorInstance.getData());
                formData.append('type', this.type);
                formData.append('status', status);
                formData.append('screenshot', screenshotFile);

                if (this.templateContentEdit) {
                    // only contains ID, filename, content, status
                    await templateApi.updateTemplatePatch(this.templateContentEdit.id, formData);
                } else {
                    await templateApi.storeTemplate(formData);
                }

                this.showRequestExemptionSuccessDialog = true;
            } catch (err) {
                this.$toast.error('Something went wrong while submitting the leave request.');
                console.error(err?.response?.data || err);
                this.$emit('close-editor');
            } finally {
                loader.hide();
            }
        },

        async captureEditorContent() {
            const captureDiv = this.$refs.captureArea;
            const contentHtml = this.editorInstance.getData();

            captureDiv.innerHTML = contentHtml;
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
})
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
    align-items: center;
    border: variables.$layout_content_boarder;
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
    padding: 0px;
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
    //margin-top: 500px!important;
    background-color: #d3d3d3!important;

    width: 100%;
    //height: 700px;  /* Short bond paper height */
    box-shadow: none;
    //position: relative; /* Ensure relative positioning for child elements */
    //overflow-y: scroll;   /* Prevent overflow from child elements */
    padding-top: 50px;

    display: flex;
    justify-content: flex-start; /*center vertical up and down movement centering */
    flex-direction: column;
    ///align-items: center;  /* horizontal left and right movement centering */
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
    margin: 20px 20px;
    color: #858587;
    background-color: #dfe0e3;
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
    display: none;
    height: 1056px;
    width: 816px;
    padding-top: 30px;
}

.cke_bottom cke_reset_all {
    display: none!important;
}

</style>

<style>
iframe {
    background-color: #D3D3D3!important;
}
</style>
