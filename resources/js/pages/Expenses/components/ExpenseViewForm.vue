<template>
  <VNavigationDrawer
    temporary
    :width="500"
    location="end"
    class="scrollable-content px-3"
    v-model="drawerOpen"
    @after-leave="$emit('close')"
  >
    <AppDrawerHeaderSection
      title="Expense"
      @cancel="$emit('close')"
      class="tw-mb-6"
    />

    <div class="tw-flex tw-flex-col tw-px-6" style="overflow: auto">
      <!-- Display Fields -->
      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Employee Name</label>
        <div class="tw-text-black">
          {{ expense?.employee?.first_name }} {{ expense?.employee?.last_name }}
        </div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Expense Date</label>
        <div class="tw-text-black">{{ formatDate(expense?.expense_date) }}</div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Reason for Expense</label>
        <div class="tw-text-black">{{ expense?.reason }}</div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Cost Code</label>
        <div class="tw-text-black">{{ expense?.cost_code }}</div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Amount</label>
        <div class="tw-text-black">${{ expense?.amount }}</div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Work Related</label>
        <div class="tw-text-black">
          {{ expense?.confirm_expense_is_work_related ? 'Yes' : 'No' }}
        </div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500">
        <label class="tw-mb-2 tw-font-bold">Status</label>
        <div class="tw-text-black">{{ getStatusLabel(expense?.status) }}</div>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-8 tw-text-gray-500" v-if="expense?.receipt_image_path">
        <label class="tw-mb-2 tw-font-bold">Receipt</label>
        <a
          :href="receiptUrl(expense.receipt_image_path)"
          target="_blank"
          rel="noopener noreferrer"
          class="tw-text-blue-600 hover:tw-underline"
        >
          View Receipt
        </a>
      </div>      

      <!-- Approval Section -->
      <div class="tw-border-t tw-pt-6 tw-mt-6 tw-flex tw-flex-col tw-text-gray-700">
        <label class="tw-font-bold tw-mb-2">Select Approval</label>
        <VRadioGroup
          v-model="actionDecision"
          inline
          :disabled="!canTakeActionOnExpense(expense)"
        >
          <VRadio label="Approve" value="approve" />
          <VRadio label="Reject" value="reject" />
        </VRadioGroup>
      </div>

      <div class="tw-flex tw-flex-col tw-mb-6 tw-mt-4 tw-text-gray-700">
        <label class="tw-mb-2 tw-font-bold">Reviewer’s Comment</label>
        <!-- Editable: Only textarea and (optional) required warning -->
        <template v-if="canTakeActionOnExpense(expense)">
          <VTextarea
            v-model="reviewerComment"
            rows="3"
            placeholder="Enter your comments here..."
          />
          <div v-if="!reviewerComment && actionDecision" class="tw-text-red-500 tw-mt-1 tw-text-sm">
            Reviewer comment is required.
          </div>
        </template>

        <!-- Read-only: Only show plain comment -->
        <template v-else>
          <div class="tw-text-black whitespace-pre-wrap">
            {{ reviewerComment || 'No comment provided.' }}
          </div>
        </template>
      </div>

      <!-- Submit Button (only if allowed) -->
      <div class="tw-flex tw-justify-end" v-if="canTakeActionOnExpense(expense)">
        <VBtn
          color="primary"
          :disabled="!actionDecision || !reviewerComment.trim()"
          @click="submitReview"
        >
          Submit
        </VBtn>
      </div>
    </div>
  </VNavigationDrawer>
</template>

<script>
export default {
  props: {
    open: Boolean,
    expense: Object,
    authUser: Object, // Pass this from parent
  },
  data() {
    return {
      drawerOpen: false,
      actionDecision: null,
      reviewerComment: '',
    };
  },
  watch: {
    open: {
      immediate: true,
      handler(val) {
        this.drawerOpen = val;
        if (val && this.expense) {
          if (this.expense.status === 2) {
            this.actionDecision = 'approve';
          } else if (this.expense.status === 4) {
            this.actionDecision = 'reject';
          }
          this.reviewerComment = this.expense.reviewer_comment || '';
        }
        if (!val) this.resetReview();
      },
    },
  },
  methods: {
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleDateString('en-GB');
    },
    receiptUrl(path) {
      return `/storage/${path}`; // Assumes public disk (i.e., storage:link is set up)
    },
    getStatusLabel(status) {
      switch (status) {
        case 1: return 'Pending Approval';
        case 2: return 'Approved';
        case 3: return 'Cancelled';
        case 4: return 'Rejected';
        case 5: return 'Archived';
        default: return 'Unknown';
      }
    },
    submitReview() {
      if (!this.reviewerComment || !this.reviewerComment.trim()) {
        this.$toast?.error?.("Reviewer comment is required."); // If using a toast plugin
        return;
      }

      this.$emit('submit-review', {
        action: this.actionDecision,
        comment: this.reviewerComment,
        expenseId: this.expense?.id,
      });

      this.resetReview();
      this.drawerOpen = false;
    },
    resetReview() {
      this.actionDecision = null;
      this.reviewerComment = '';
    },
    canTakeActionOnExpense(item, action = 'approve') {

      const employee = this.authUser?.employee;
      const roleId   = this.authUser?.role_id;
      const submitted = item?.submitted_by;

      if (!employee || !submitted || !roleId) return false;

      const sameDivision = employee.division_id === submitted.division_id;
      const isSelf = this.authUser.id === submitted.user_id;

      switch (roleId) {
        case 3: // LINE MANAGER
        case 5: // HR
          return sameDivision;

        case 1: // CEO
        case 4: // GENERAL MANAGER
        case 6: // FINANCE
        case 10: // ADMINISTRATOR
          return true;

        case 2: // USER
          return action === 'cancel' && isSelf;

        default:
          return false;
      }
    },
  },
};
</script>
