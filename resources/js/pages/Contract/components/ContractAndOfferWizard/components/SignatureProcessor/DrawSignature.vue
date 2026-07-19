<template>
    <div class="tw-relative tw-inline-block">
        <!-- Floating Clear Button -->
        <div class="tw-flex tw-justify-between">
            <button
                class="tw-absolute tw-left-3 tw-top-3 tw-px-3 tw-py-1 tw-bg-whites tw-shadowss-sm"
                style="borders: 1px solid #D6D6D6; border-radius: 5px; z-index: 10"
                @click="clearCanvas"
            >
                <VIcon icon="tabler-trash" />
            </button>

            <button
                class="tw-absolute tw-right-4 tw-top-3 tw-px-3 tw-py-1 tw-bg-orange-600 tw-shadow-sm"
                style=" border-radius: 5px; z-index: 10"
                @click="setInkColor('#EA580C')"
            />
            <button
                class="tw-absolute tw-right-12 tw-top-3 tw-px-3 tw-py-1 tw-bg-blue-600 tw-shadow-sm"
                style=" border-radius: 5px; z-index: 10"
                @click="setInkColor('#2563EB')"
            />
            <button
                class="tw-absolute tw-right-20 tw-top-3 tw-px-3 tw-py-1 tw-bg-black tw-shadow-sm"
                style=" border-radius: 5px; z-index: 10"
                @click="setInkColor('#000000')"
            />

        </div>

        <!-- Canvas -->
        <canvas
            ref="canvas"
            width="570"
            height="250"
            class="tw-border tw-border-gray-400 tw-rounded"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
        ></canvas>
    </div>
</template>


<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'DrawSignature',
    props: {
        modelValue: {
            type: Number, // this is activeTab
            required: true,
        },
    },
    emits: ['update:isCanvasBlank'],
    data() {
        return {
            isDrawing: false,
            ctx: null,
            inkColor: null,
            canvasIsBlank: true,
        };
    },
    mounted() {
        // Only initialize if tab is active (0)
        if (this.modelValue === 0) {
            this.$nextTick(() => {
                this.initializeCanvas();
            });
        }
    },
    watch: {
        modelValue(val) {
            if (val === 0) {
                this.$nextTick(() => {
                    this.initializeCanvas();
                });
            }
        },
    },
    methods: {
        getCanvasImage() {
            const canvas = this.$refs.canvas;
            return canvas.toDataURL('image/png'); // returns base64 string
        },
        getCanvasBlob(callback) {
            const canvas = this.$refs.canvas;
            canvas.toBlob((blob) => {
                callback(blob);
            }, 'image/png');
        },
        isCanvasBlank() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            const pixelBuffer = new Uint32Array(
                ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
            );
            return !pixelBuffer.some(color => color !== 0);
        },
        setInkColor(color){
            this.inkColor = color
            if (this.ctx) {
                this.ctx.strokeStyle = color
            }
        },
        clearCanvas() {
            if (!this.ctx) return;
            const canvas = this.$refs.canvas;
            this.ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.checkCanvasState(); // this was added
        },
        initializeCanvas() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            this.ctx = canvas.getContext('2d');
            this.ctx.lineWidth = 2;
            this.ctx.strokeStyle = this.inkColor; // Tailwind orange
            this.ctx.lineCap = 'round';
        },
        startDrawing(e) {
            if (!this.ctx) return;
            this.isDrawing = true;
            this.ctx.beginPath();
            this.ctx.moveTo(e.offsetX, e.offsetY);
        },
        draw(e) {
            if (!this.ctx || !this.isDrawing) return;
            this.ctx.lineTo(e.offsetX, e.offsetY);
            this.ctx.stroke();

            // check canvas status
            this.checkCanvasState(); // this was added
        },
        checkCanvasState() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            const pixelBuffer = new Uint32Array(ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer);
            const isBlank = !pixelBuffer.some(color => color !== 0);

            if (this.canvasIsBlank !== isBlank) {
                this.canvasIsBlank = isBlank;
                this.$emit('update:isCanvasBlank', isBlank); // emit to parent
            }
        },
        stopDrawing() {
            if (!this.ctx) return;
            this.isDrawing = false;
            this.ctx.closePath();
        },
    },
});
</script>


<style>

</style>
