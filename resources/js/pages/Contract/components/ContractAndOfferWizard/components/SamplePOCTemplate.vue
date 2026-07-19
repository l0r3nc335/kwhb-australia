<template>
    <div class="layout">
        <!-- LEFT: Rendered template -->
        <div class="document" v-html="compiledTemplate"></div>

        <!-- RIGHT: Drawer-style input form -->
        <div class="drawer">
            <div v-for="(value, key) in form" :key="key" class="input-group">
                <label :for="key">{{ key }}</label>
                <input :id="key" v-model="form[key]" />
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue"


export default defineComponent({
    name: 'SammplePOCTemplate',
    data() {
        return {
            rawTemplate: `
                <p style="margin: 0 75px;"><span style="font-size:12px; background-color: greenyellow;">&lt;&lt;ApplicantName&gt;&gt;</span></p>
                <p style="margin: 0 75px;"><span style="font-size:12px; background-color: greenyellow;">&lt;&lt;ApplicantAddress&gt;&gt;</span></p>
              `,
            form: {
                ApplicantName: 'John Doe',
                ApplicantAddress: '123 Main Street'
            }
        }
    },
    computed: {
        compiledTemplate() {
            let html = this.rawTemplate;
            for (const key in this.form) {
                const safeValue = this.escapeHtml(this.form[key]);
                const pattern = new RegExp(`&lt;&lt;${key}&gt;&gt;`, 'g');
                html = html.replace(pattern, `<span class="highlight">${safeValue}</span>`);
            }
            return html;
        }
    },
    methods: {
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
})
</script>

<style scoped>
.layout {
    display: flex;
    gap: 32px;
}

.document {
    width: 60%;
    border: 1px solid #ccc;
    padding: 20px;
}

.drawer {
    width: 35%;
    border-left: 2px solid #ddd;
    padding: 20px;
    background: #f9f9f9;
}

.input-group {
    margin-bottom: 16px;
}

.highlight {
    background-color: #c8f7c5;
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: bold;
}
</style>
