<template>
    <div>
        <!-- Toolbar Container -->
        <div ref="toolbarContainer"></div>

        <!-- CKEditor Textarea -->
        <textarea ref="editorElement"></textarea>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue';

const editorInstance = ref(null);
const toolbarContainer = ref(null);
const editorElement = ref(null);

onMounted(() => {
    // Initialize CKEditor 4 manually
    editorInstance.value = CKEDITOR.replace(editorElement.value, {
        removePlugins: 'maximize,resize',
        toolbarLocation: 'bottom',
        toolbar: [
            { name: 'basicstyles', items: ['Bold', 'Italic'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
            { name: 'links', items: ['Link', 'Unlink'] },
            { name: 'insert', items: ['Image', 'Table'] },
            { name: 'styles', items: ['Format'] },
        ],
    });

    // Move toolbar to the custom container
    editorInstance.value.on('instanceReady', () => {
        const toolbar = editorInstance.value.ui.space('top');
        if (toolbar && toolbar.$ && toolbarContainer.value) {
            toolbarContainer.value.appendChild(toolbar.$);
        }
    });
});

onBeforeUnmount(() => {
    // Destroy the editor instance on unmount
    if (editorInstance.value) {
        editorInstance.value.destroy();
    }
});
</script>
