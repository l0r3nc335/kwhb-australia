<template>
  <div style="flex: 1; overflow: hidden; padding-bottom: 5px; background-color: white; color: black; margin: 60px 60px 0px 60px;">
    <h3 style="text-align: center; color: black;">Preview</h3>
    <VHTML>
      <p style="text-align:center;">&lt;&lt;Title&gt;&gt;</p>
      <p>&nbsp;</p>
      <p>&lt;&lt;Signature Date&gt;&gt; heuibfielboifnienwonfwn juche</p>
    </VHTML>
  </div>

  <div class="footer" style="display:flex; margin: 0px 60px 0px 60px;">
    <div style="background-color:whitesmoke; display:flex; justify-content:center;width:100%">
      <button @click="openRejectDialog" style="background-color:white;padding:5px 12px;border-radius:5px;margin:8px 0px;color: black;border: 1px solid gray ;">Reject</button>
      <button @click="openSignDialog" style="background-color:orange;padding:5px 12px;border-radius:5px;margin:8px 5px;color: white;">Sign</button>
    </div>
  </div>

  <!-- Reject Dialog -->
  <div v-if="isRejectDialogOpen" class="dialog-overlay">
    <div class="dialog-content">
      <h4>You have rejected the document.</h4>
      <button @click="closeRejectDialog" style="background-color:gray; color: white; padding: 8px; border-radius: 5px;">Close</button>
    </div>
  </div>

  <!-- Sign Dialog -->
  <div v-if="isSignDialogOpen" class="dialog-overlay">
    <div class="dialog-content">
      <h4>You have signed the document.</h4>
      <button @click="closeSignDialog" style="background-color:gray; color: white; padding: 8px; border-radius: 5px;">Close</button>
    </div>
  </div>

</template>

<script setup>
import { ref } from 'vue';

// State to control each dialog
const isRejectDialogOpen = ref(false);
const isSignDialogOpen = ref(false);

// Function to open the Reject dialog
const openRejectDialog = () => {
  isRejectDialogOpen.value = true;
};

// Function to close the Reject dialog
const closeRejectDialog = () => {
  isRejectDialogOpen.value = false;
};

// Function to open the Sign dialog
const openSignDialog = () => {
  isSignDialogOpen.value = true;
};

// Function to close the Sign dialog
const closeSignDialog = () => {
  isSignDialogOpen.value = false;
};
</script>

<style>
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.dialog-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

button {
  cursor: pointer;
}
</style>
