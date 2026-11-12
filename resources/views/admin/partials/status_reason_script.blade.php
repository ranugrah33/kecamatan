@push('scripts')
<script>
function sipafStatusReasonForm(initialStatus, requiredStatuses, rootEl) {
    const textareaEl = rootEl ? rootEl.querySelector('textarea') : null;
    return {
        status: initialStatus,
        reason: textareaEl ? textareaEl.value : '',
        clientError: '',
        isSubmitting: false,
        get requiresReason() {
            return requiredStatuses.includes(this.status);
        },
        get isRevision() {
            return this.status === 'Perlu Perbaikan';
        },
        get isRejected() {
            return this.status === 'Ditolak';
        },
        onStatusChange() {
            if (!this.requiresReason) {
                this.clientError = '';
            }
        },
        onReasonInput() {
            if (this.reason.trim() !== '') {
                this.clientError = '';
            }
        },
        handleSubmit(event) {
            if (this.requiresReason && this.reason.trim() === '') {
                event.preventDefault();
                event.stopPropagation();
                this.clientError = this.isRevision
                    ? 'Alasan atau catatan perbaikan wajib diisi sebelum menyimpan perubahan.'
                    : 'Alasan penolakan wajib diisi sebelum menyimpan perubahan.';
                this.$nextTick(() => {
                    if (this.$refs.reasonTextarea) {
                        this.$refs.reasonTextarea.focus();
                    }
                });
                return false;
            }

            if (this.isSubmitting) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }

            this.clientError = '';
            this.isSubmitting = true;
            return true;
        }
    };
}
</script>
@endpush

