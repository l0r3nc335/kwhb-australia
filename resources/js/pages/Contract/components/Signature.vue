<template> 
  <div class="signature-container">
    <div class="signature-tabs">
      <button
        v-for="(option, index) in signatureOptions"
        :key="index"
        :class="{ active: activeOption === option }"
        @click="changeTab(option)"
      >
        {{ option }}
      </button>
    </div>

    <!-- Draw Mode -->
    <div v-if="activeOption === 'Draw'" class="signature-draw" style="background-color: #e0e0e0; padding-top: 20px;">
      <canvas
        ref="canvas"
        width="500"
        height="300"
        class="signature-canvas"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseout="stopDrawing"
      ></canvas>
      <div class="controls">
        <button @click="clearCanvas" style="margin: 5px 5px; color: black; background-color: gray; border: 1px solid black; border-radius: 5px; padding: 5px;">Clear</button>
        <button @click="saveSignature" style="margin: 5px 5px; color: black; background-color: gray; border: 1px solid black; border-radius: 5px; padding: 5px;">Save</button>
      </div>
    </div>

    <!-- Type Mode -->
    <div v-if="activeOption === 'Type'" class="signature-type" style="background-color: #e0e0e0; padding: 20px; color: black;">
      <div class="font-options" style="display: flex; justify-content: space-between;">
        <div>
          <label style="margin-right: 10px;">Font: </label>
          <select v-model="selectedFont" style="border: 1px solid gray;">
            <option v-for="font in availableFonts" :key="font" :value="font">
              {{ font }}
            </option>
          </select>
        </div>
        <div>
          <label style="margin-right: 10px;">Color</label>
          <input type="color" v-model="selectedColor" style="height: 20px; border: 1px solid gray;" 
        />
        </div>
      </div>
      <input
        v-model="typedSignature"
        placeholder="Type your signature here"
        class="signature-input"
      />
      <div class="preview">
        <span
          :style="{ fontFamily: selectedFont, color: selectedColor }"
        >
          {{ typedSignature }}
        </span>
      </div>
      <button @click="saveTypedSignature">Save</button>
    </div>

    <!-- Upload Mode -->
    <div v-if="activeOption === 'Upload'" class="signature-upload" style="background-color: #e0e0e0; padding: 20px; color: black;">
      <input type="file" @change="handleFileUpload" accept="image/*" />
      <div v-if="uploadedImage">
        <img :src="uploadedImage" alt="Uploaded Signature" class="uploaded-preview" />
      </div>
      <button v-if="uploadedImage" @click="saveUploadedSignature">Save</button>
    </div>

    <div style="padding-top: 20px; ">
      <p style="text-align: justify;">I understand that Katherine  West Health Board Abroginal Corporation  uses my name, email address  and limited information will be used to complete the siganture process and enhance the user experience. To learn more about how Katherine West Health Board Abroginal Corporation uses information, please sea our Privacy Policy. By signing this document with an electronic signature, I agree that such signature will be as valid as handwritten signatures and considered originals to the extent allowed applicable by law.</p>

      <div style="display: flex; justify-content: space-between; margin-top: 10px;">
        <button class="btn" style="background-color: gray; padding: 5px 20px;  border-radius: 5px;">Cancel</button>
        <button @click="saveSignature" style="background-color: orange; padding: 5px 20px; color: #e0e0e0; border-radius: 5px;">Accept and Sign</button>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import contractApi from "@/api/contract/contractApi";


export default {
  setup() {
    const signatureOptions = ["Draw", "Type", "Upload"];
    const activeOption = ref("Draw");

    // Canvas Drawing Setup
    const canvas = ref(null);
    let ctx = null;
    const isDrawing = ref(false);
    const lastX = ref(0);
    const lastY = ref(0);

    const initializeCanvas = () => {
      if (canvas.value) {
        ctx = canvas.value.getContext("2d");
        ctx.lineWidth = 2;
        ctx.lineJoin = "round";
        ctx.lineCap = "round";
        ctx.strokeStyle = "#000000";
        clearCanvas(); 
      }
    };

    const startDrawing = (e) => {
      isDrawing.value = true;
      lastX.value = e.offsetX;
      lastY.value = e.offsetY;
    };

    const draw = (e) => {
      if (!isDrawing.value || !ctx) return;

      ctx.beginPath();
      ctx.moveTo(lastX.value, lastY.value);
      ctx.lineTo(e.offsetX, e.offsetY);
      ctx.stroke();

      lastX.value = e.offsetX;
      lastY.value = e.offsetY;
    };

    const stopDrawing = () => {
      isDrawing.value = false;
    };

    const clearCanvas = () => {
      if (ctx && canvas.value) {
        ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
      }
    };

    const typedSignature = ref("");
    const selectedFont = ref("Arial");
    const selectedColor = ref("#000000");
    const availableFonts = ["Arial", "Cursive", "Georgia", "Times New Roman", "Verdana"];

    const uploadedImage = ref(null);

    const handleFileUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          uploadedImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    };

    const saveSignature = async () => {
      let signatureData;

      if (activeOption.value === "Draw" && canvas.value) {
        signatureData = canvas.value.toDataURL("image/png");
      } else if (activeOption.value === "Type") {
        const canvasTemp = document.createElement("canvas");
        canvasTemp.width = 500;
        canvasTemp.height = 300;

        const tempCtx = canvasTemp.getContext("2d");
        tempCtx.font = `30px ${selectedFont.value}`;
        tempCtx.fillStyle = selectedColor.value;
        tempCtx.fillText(typedSignature.value, 50, 150);

        signatureData = canvasTemp.toDataURL("image/png");
      } else if (activeOption.value === "Upload" && uploadedImage.value) {
        signatureData = uploadedImage.value;
      } else {
        alert("No signature to save.");
        return;
      }

      try {
        const response = await contractApi.saveSignature({ signature: signatureData });
        alert(`Signature saved successfully: ${response.filename}`);
      } catch (error) {
        console.error("Error saving signature:", error);
        alert("Error saving signature.");
      }
    };

    const changeTab = (option) => {
      activeOption.value = option;

      if (option === "Draw") {
        setTimeout(() => {
          initializeCanvas(); 
        }, 0);
      }
    };

    onMounted(() => {
      initializeCanvas();
    });

    return {
      signatureOptions,
      activeOption,
      changeTab,
      canvas,
      startDrawing,
      draw,
      stopDrawing,
      clearCanvas,
      saveSignature,
      typedSignature,
      selectedFont,
      selectedColor,
      availableFonts,
      uploadedImage,
      handleFileUpload,
    };
  },
};
</script>


<style scoped>
.signature-container {
  width: 600px;
  margin: auto;
  text-align: center;
}

.signature-tabs {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.signature-tabs button {
  margin: 0 10px;
  padding: 4px 20px;
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  color: #000;
  cursor: pointer;
  border-radius: 3px;
}

.signature-tabs button.active {
  background-color:orange;
  color: white;
  border-color: #4CAF50;
}

.signature-canvas {
  border: 1px solid #000;
  display: block;
  margin: 0 auto;
}

.signature-input {
  padding: 10px;
  font-size: 16px;
  margin: 10px 0;
  width: 90%;
  text-align: center;
}

.font-options {
  margin-bottom: 10px;
}

.preview {
  margin: 20px 0;
  font-size: 24px;
}

.uploaded-preview {
  max-width: 100%;
  margin: 20px 0;
}
</style>
