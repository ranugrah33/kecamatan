<!-- Floating Chatbot Component -->
<div x-data="chatbot()"
     data-sipaf-chatbot
     data-chat-url="{{ route('chatbot.chat') }}"
     data-csrf-token="{{ csrf_token() }}"
     class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[1050] font-sans"
     style="font-family: 'Inter', sans-serif;">
    <!-- Chat Button -->
    <button @click="toggleChat" x-show="!isOpen" x-transition.scale.origin.bottom.right
            type="button"
            aria-label="Buka Asisten Digital Kecamatan"
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-110 focus:outline-none border-0 cursor-pointer">
        <i class="ph ph-robot text-3xl"></i>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="w-[calc(100vw-2rem)] max-w-[380px] sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden"
         style="height: 560px; max-height: 85vh;">

        <!-- Header -->
        <div class="bg-blue-600 px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3 text-white min-w-0">
                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <i class="ph ph-robot text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-semibold text-sm truncate">Asisten Digital Kecamatan</h3>
                    <p class="text-[11px] text-blue-100 truncate">Kecamatan Cikampek</p>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button @click="requestResetChat"
                        type="button"
                        :disabled="messages.length === 0 && !isLoading"
                        title="Reset percakapan"
                        aria-label="Reset percakapan"
                        class="text-blue-100 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed p-1.5 rounded-lg hover:bg-white/10 transition border-0 bg-transparent cursor-pointer">
                    <i class="ph ph-arrow-counter-clockwise text-base"></i>
                </button>
                <button @click="toggleChat"
                        type="button"
                        aria-label="Tutup jendela chat"
                        class="text-blue-100 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition border-0 bg-transparent cursor-pointer">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Local Reset Confirmation Bar -->
        <div x-show="showResetConfirm" x-cloak
             class="bg-blue-50 border-b border-blue-200 px-3.5 py-2.5 text-xs text-slate-700 flex flex-col gap-2 shrink-0">
            <p class="leading-snug">
                Bersihkan percakapan di layar ini? Data akun dan pengajuan SIPAF Anda tidak akan terhapus.
            </p>
            <div class="flex items-center justify-end gap-2">
                <button @click="cancelResetChat"
                        type="button"
                        class="px-2.5 py-1 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium cursor-pointer transition">
                    Batal
                </button>
                <button @click="confirmResetChat"
                        type="button"
                        class="px-2.5 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium border-0 cursor-pointer transition">
                    Ya, Reset
                </button>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 p-4 overflow-y-auto bg-[#f5f7fb] flex flex-col gap-3.5 scroll-smooth" id="chatbot-messages" aria-live="polite">
            <!-- Initial Message -->
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-200/50">
                    <i class="ph ph-robot text-sm"></i>
                </div>
                <div class="bg-white border border-gray-200/80 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm text-gray-700 shadow-sm leading-relaxed min-w-0 break-words">
                    Halo! Saya Asisten Digital Kecamatan Cikampek.<br>Ada yang ingin Anda tanyakan mengenai pelayanan atau wilayah kecamatan?
                </div>
            </div>

            <!-- Quick Questions -->
            <div class="flex flex-wrap gap-2 ml-10">
                <button @click="sendQuick('Pelayanan apa saja?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Pelayanan apa saja?</button>
                <button @click="sendQuick('Berapa luas wilayah dan desa di Cikampek?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Luas Desa?</button>
                <button @click="sendQuick('Cara pinjam aula?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Cara pinjam aula?</button>
                <button @click="sendQuick('Pengajuan sertifikat?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Pengajuan sertifikat?</button>
                <button @click="sendQuick('Cara buat surat ahli waris?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Ahli Waris?</button>
                <button @click="sendQuick('Cara izin usaha UMKM?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Izin UMKM?</button>
                <button @click="sendQuick('Info bantuan sosial?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Info Bansos?</button>
                <button @click="sendQuick('Cara urus jual beli tanah?')" :disabled="isLoading" type="button" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm cursor-pointer">Jual Beli Tanah?</button>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex items-start gap-2.5" :class="msg.sender === 'user' ? 'flex-row-reverse' : ''">
                    <template x-if="msg.sender === 'bot'">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 shadow-sm border"
                             :class="msg.isError ? 'bg-amber-100 text-amber-700 border-amber-200/80' : 'bg-blue-100 text-blue-600 border-blue-200/50'">
                            <i class="ph text-sm" :class="msg.isError ? 'ph-warning-circle' : 'ph-robot'"></i>
                        </div>
                    </template>

                    <div class="flex flex-col max-w-[85%] min-w-0" :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
                        <div class="px-3.5 py-2.5 rounded-2xl text-sm shadow-sm w-full min-w-0 break-words leading-relaxed"
                             :class="msg.sender === 'user'
                                 ? (msg.status === 'failed' ? 'bg-blue-600/90 text-white rounded-tr-sm ring-1 ring-amber-300' : 'bg-blue-600 text-white rounded-tr-sm')
                                 : (msg.isError ? 'bg-amber-50 border border-amber-200 text-amber-950 rounded-tl-sm chatbot-markdown' : 'bg-white border border-gray-200/80 text-gray-800 rounded-tl-sm chatbot-markdown')"
                             x-html="msg.sender === 'user' ? formatUserMessage(msg.text) : formatMessage(msg.text)">
                        </div>

                        <template x-if="msg.sender === 'user' && msg.status === 'failed'">
                            <span class="mt-1 text-[11px] text-amber-700 font-medium">Pesan belum terkirim</span>
                        </template>

                        <!-- Friendly Retry / Action Row on Error -->
                        <template x-if="msg.isError">
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <button x-show="msg.canRetry && msg.retryMessage"
                                        @click="retryFailedMessage(msg.id)"
                                        :disabled="isLoading"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-xs font-medium bg-white border border-amber-300 text-amber-900 hover:bg-amber-100/70 disabled:opacity-50 px-2.5 py-1 rounded-lg shadow-sm cursor-pointer transition">
                                    <i class="ph ph-arrows-clockwise text-xs"></i>
                                    Coba Lagi
                                </button>
                                <button x-show="msg.retryMessage"
                                        @click="restoreFailedToInput(msg.retryMessage)"
                                        :disabled="isLoading"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-xs font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 disabled:opacity-50 px-2.5 py-1 rounded-lg shadow-sm cursor-pointer transition">
                                    <i class="ph ph-pencil-simple text-xs"></i>
                                    Edit Pesan
                                </button>
                                <button x-show="msg.showReloadHint"
                                        @click="reloadPage()"
                                        type="button"
                                        class="inline-flex items-center gap-1 text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 border-0 px-2.5 py-1 rounded-lg shadow-sm cursor-pointer transition">
                                    Muat Ulang Halaman
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex items-start gap-2.5" role="status" aria-live="polite">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-200/50">
                    <i class="ph ph-robot text-sm"></i>
                </div>
                <div class="bg-white border border-gray-200/80 px-3.5 py-2.5 rounded-2xl rounded-tl-sm shadow-sm flex items-center gap-2.5">
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                    <span class="text-xs text-gray-600 font-medium">Asisten sedang menyiapkan jawaban...</span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100 shrink-0">
            <form @submit.prevent="sendMessage" data-no-hud class="flex items-center gap-2">
                <input type="text" x-model="userInput" :disabled="isLoading"
                       id="chatbot-input"
                       maxlength="500"
                       autocomplete="off"
                       placeholder="Ketik pertanyaan Anda..."
                       class="chatbot-input flex-1 min-w-0 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:bg-white disabled:opacity-50 transition">
                <button type="submit" :disabled="isLoading || userInput.trim() === ''"
                        aria-label="Kirim pertanyaan"
                        class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700 transition shadow-sm border-0 cursor-pointer">
                    <i class="ph text-lg" :class="isLoading ? 'ph-circle-notch animate-spin' : 'ph-paper-plane-right'"></i>
                </button>
            </form>
            <div x-show="userInput.length > 400" x-cloak class="mt-1 text-right text-[11px] text-gray-500">
                <span x-text="userInput.length"></span>/500 karakter
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    #chatbot-messages::-webkit-scrollbar { width: 4px; }
    #chatbot-messages::-webkit-scrollbar-track { background: transparent; }
    #chatbot-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    #chatbot-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Gaya teks saat mengetik chat: warna hitam pekat / hijau gelap agar jelas terlihat */
    #chatbot-input, .chatbot-input {
        color: #064e3b !important;
        caret-color: #059669 !important;
        background-color: #f8fafc !important;
        font-weight: 500 !important;
    }
    #chatbot-input:focus, .chatbot-input:focus {
        color: #022c22 !important;
        background-color: #ffffff !important;
    }
    #chatbot-input::placeholder, .chatbot-input::placeholder {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    /* Scoped Markdown readability styles (responsive tables, lists, headings) */
    .chatbot-markdown > :first-child { margin-top: 0 !important; }
    .chatbot-markdown > :last-child { margin-bottom: 0 !important; }
    .chatbot-markdown p { margin: 0.35rem 0; }
    .chatbot-markdown h1,
    .chatbot-markdown h2,
    .chatbot-markdown h3,
    .chatbot-markdown h4 {
        font-weight: 700;
        color: #0f172a;
        margin: 0.6rem 0 0.3rem;
        line-height: 1.35;
    }
    .chatbot-markdown h1 { font-size: 0.95rem; }
    .chatbot-markdown h2 { font-size: 0.91rem; }
    .chatbot-markdown h3,
    .chatbot-markdown h4 { font-size: 0.875rem; }
    .chatbot-markdown ul,
    .chatbot-markdown ol {
        margin: 0.35rem 0;
        padding-left: 1.25rem;
    }
    .chatbot-markdown ul { list-style-type: disc; }
    .chatbot-markdown ol { list-style-type: decimal; }
    .chatbot-markdown li { margin: 0.18rem 0; }
    .chatbot-markdown hr {
        border: 0;
        border-top: 1px solid #e2e8f0;
        margin: 0.55rem 0;
    }
    .chatbot-markdown blockquote {
        border-left: 3px solid #60a5fa;
        padding-left: 0.6rem;
        margin: 0.4rem 0;
        color: #475569;
        font-style: italic;
    }
    .chatbot-markdown .chatbot-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        margin: 0.45rem 0;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background-color: #ffffff;
        -webkit-overflow-scrolling: touch;
    }
    .chatbot-markdown table {
        width: 100%;
        min-width: 240px;
        border-collapse: collapse;
        font-size: 0.75rem;
        line-height: 1.4;
    }
    .chatbot-markdown th,
    .chatbot-markdown td {
        padding: 0.38rem 0.55rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .chatbot-markdown th {
        background-color: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        white-space: nowrap;
    }
    .chatbot-markdown tr:last-child td {
        border-bottom: 0;
    }
</style>

<script>
    window.createSipafChatbot = function (options = {}) {
        return {
            isOpen: false,
            isLoading: false,
            showResetConfirm: false,
            userInput: '',
            messages: [],
            lastFailedMessage: '',
            chatUrl: options.chatUrl || '',
            csrfToken: options.csrfToken || '',
            requestTimeoutMs: typeof options.requestTimeoutMs === 'number' ? options.requestTimeoutMs : 35000,
            activeRequestId: 0,
            activeAbortController: null,
            nextMessageSeq: 1,

            init() {
                const root = this.$el || (typeof document !== 'undefined' ? document.querySelector('[data-sipaf-chatbot]') : null);
                if (root && root.dataset) {
                    if (!this.chatUrl && root.dataset.chatUrl) {
                        this.chatUrl = root.dataset.chatUrl;
                    }
                    if (!this.csrfToken && root.dataset.csrfToken) {
                        this.csrfToken = root.dataset.csrfToken;
                    }
                }
            },

            generateMessageId(prefix = 'msg') {
                return `${prefix}-${Date.now()}-${this.nextMessageSeq++}`;
            },

            getChatUrl() {
                if (this.chatUrl) {
                    return this.chatUrl;
                }
                const root = typeof document !== 'undefined' ? document.querySelector('[data-sipaf-chatbot]') : null;
                return (root && root.dataset && root.dataset.chatUrl) ? root.dataset.chatUrl : '/chatbot/chat';
            },

            getCsrfToken() {
                if (typeof document !== 'undefined') {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) {
                        const content = meta.getAttribute('content');
                        if (content) {
                            return content;
                        }
                    }
                    const root = document.querySelector('[data-sipaf-chatbot]');
                    if (root && root.dataset && root.dataset.csrfToken) {
                        return root.dataset.csrfToken;
                    }
                }
                return this.csrfToken || '';
            },

            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    setTimeout(() => this.scrollToBottom(), 100);
                }
            },

            sendQuick(text) {
                if (this.isLoading) {
                    return;
                }
                this.userInput = String(text ?? '');
                return this.sendMessage();
            },

            requestResetChat() {
                if (this.messages.length === 0 && !this.isLoading) {
                    return;
                }
                this.showResetConfirm = true;
            },

            cancelResetChat() {
                this.showResetConfirm = false;
            },

            confirmResetChat() {
                this.activeRequestId++;
                if (this.activeAbortController && typeof this.activeAbortController.abort === 'function') {
                    try {
                        this.activeAbortController.abort();
                    } catch (_) {
                        // Ignore abort errors on cleanup
                    }
                }
                this.activeAbortController = null;
                this.isLoading = false;
                this.messages = [];
                this.lastFailedMessage = '';
                this.showResetConfirm = false;
                this.scrollToBottom();
            },

            reloadPage() {
                if (typeof window !== 'undefined' && window.location && typeof window.location.reload === 'function') {
                    window.location.reload();
                }
            },

            restoreFailedToInput(text) {
                if (this.isLoading) {
                    return;
                }
                this.userInput = String(text ?? '').slice(0, 500);
                if (typeof document !== 'undefined') {
                    const input = document.getElementById('chatbot-input');
                    if (input && typeof input.focus === 'function') {
                        setTimeout(() => input.focus(), 20);
                    }
                }
            },

            buildHistoryBeforeMessage(excludeMessageId = null) {
                const eligible = [];
                for (const item of this.messages) {
                    if (excludeMessageId && item.id === excludeMessageId) {
                        break;
                    }
                    if (!item || item.isError || item.status === 'failed') {
                        continue;
                    }
                    const cleanText = String(item.text ?? '').trim();
                    if (!cleanText) {
                        continue;
                    }
                    eligible.push({
                        role: item.sender === 'user' ? 'user' : 'model',
                        text: cleanText.slice(0, 2500),
                        isError: false,
                    });
                }

                return eligible.slice(-8);
            },

            async sendMessage() {
                if (this.isLoading) {
                    return;
                }

                const message = String(this.userInput ?? '').trim();
                if (message === '') {
                    return;
                }

                this.showResetConfirm = false;
                const userMsgId = this.generateMessageId('user');

                this.messages.push({
                    id: userMsgId,
                    sender: 'user',
                    text: message,
                    status: 'sending',
                    isError: false,
                });
                this.userInput = '';

                const hasPriorError = this.messages.some((m) => m && (m.isError || m.status === 'failed'));
                const historySource = hasPriorError
                    ? this.messages.slice(0, -1).filter((m) => m && !m.isError && m.status !== 'failed')
                    : this.messages.slice(0, -1).slice(-8);
                const history = historySource
                    .filter((m) => m && String(m.text ?? '').trim() !== '')
                    .map((m) => ({
                        role: m.sender === 'user' ? 'user' : 'model',
                        text: String(m.text ?? '').slice(0, 2500),
                        isError: false,
                    }))
                    .slice(-8);

                await this.dispatchChatRequest({
                    message,
                    history,
                    userMsgId,
                });
            },

            async retryFailedMessage(errorMsgId) {
                if (this.isLoading) {
                    return;
                }

                const errIdx = this.messages.findIndex((m) => m && m.id === errorMsgId);
                if (errIdx === -1) {
                    return;
                }

                const errMsg = this.messages[errIdx];
                const retryText = String(errMsg.retryMessage || this.lastFailedMessage || '').trim();
                if (retryText === '') {
                    return;
                }

                let targetUserMsgId = errMsg.targetUserMsgId || null;
                if (targetUserMsgId) {
                    const userTarget = this.messages.find((m) => m && m.id === targetUserMsgId);
                    if (userTarget) {
                        userTarget.status = 'sending';
                    } else {
                        targetUserMsgId = null;
                    }
                }

                if (!targetUserMsgId) {
                    targetUserMsgId = this.generateMessageId('user');
                    this.messages.splice(errIdx, 0, {
                        id: targetUserMsgId,
                        sender: 'user',
                        text: retryText,
                        status: 'sending',
                        isError: false,
                    });
                }

                const refreshedErrIdx = this.messages.findIndex((m) => m && m.id === errorMsgId);
                if (refreshedErrIdx !== -1) {
                    this.messages.splice(refreshedErrIdx, 1);
                }

                const history = this.buildHistoryBeforeMessage(targetUserMsgId);

                await this.dispatchChatRequest({
                    message: retryText,
                    history,
                    userMsgId: targetUserMsgId,
                });
            },

            async dispatchChatRequest({ message, history, userMsgId }) {
                if (this.isLoading) {
                    return;
                }

                this.isLoading = true;
                this.scrollToBottom();

                const reqId = ++this.activeRequestId;
                const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
                this.activeAbortController = controller;

                let timedOut = false;
                let timeoutHandle = null;

                if (controller && this.requestTimeoutMs > 0) {
                    timeoutHandle = setTimeout(() => {
                        timedOut = true;
                        try {
                            controller.abort();
                        } catch (_) {
                            // Ignore abort error
                        }
                    }, this.requestTimeoutMs);
                }

                try {
                    const fetchOptions = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                        body: JSON.stringify({
                            message,
                            history,
                        }),
                    };

                    if (controller) {
                        fetchOptions.signal = controller.signal;
                    }

                    const response = await fetch(this.getChatUrl(), fetchOptions);

                    if (reqId !== this.activeRequestId) {
                        return;
                    }

                    const outcome = await this.parseHttpOutcome(response);

                    if (reqId !== this.activeRequestId) {
                        return;
                    }

                    if (outcome.ok) {
                        this.markUserMessageStatus(userMsgId, 'sent');
                        this.lastFailedMessage = '';
                        this.messages.push({
                            id: this.generateMessageId('bot'),
                            sender: 'bot',
                            text: outcome.reply,
                            isError: false,
                        });
                    } else {
                        this.pushFriendlyError({
                            userMsgId,
                            retryMessage: message,
                            text: outcome.message,
                            errorCode: outcome.errorCode,
                            showReloadHint: Boolean(outcome.showReloadHint),
                        });
                    }
                } catch (error) {
                    if (reqId !== this.activeRequestId) {
                        return;
                    }

                    const mapped = this.resolveFetchException(error, timedOut);
                    this.pushFriendlyError({
                        userMsgId,
                        retryMessage: message,
                        text: mapped.message,
                        errorCode: mapped.errorCode,
                        showReloadHint: false,
                    });
                } finally {
                    if (timeoutHandle !== null) {
                        clearTimeout(timeoutHandle);
                    }
                    if (reqId === this.activeRequestId) {
                        this.isLoading = false;
                        this.activeAbortController = null;
                        this.scrollToBottom();
                    }
                }
            },

            markUserMessageStatus(userMsgId, status) {
                const target = this.messages.find((m) => m && m.id === userMsgId);
                if (target) {
                    target.status = status;
                }
            },

            pushFriendlyError({ userMsgId, retryMessage, text, errorCode, showReloadHint = false }) {
                this.markUserMessageStatus(userMsgId, 'failed');
                this.lastFailedMessage = retryMessage;
                this.messages.push({
                    id: this.generateMessageId('err'),
                    sender: 'bot',
                    text,
                    isError: true,
                    errorCode,
                    canRetry: true,
                    retryMessage,
                    targetUserMsgId: userMsgId,
                    showReloadHint,
                });
            },

            sanitizeUserFacingError(rawText, fallback) {
                const candidate = String(rawText ?? '').replace(/[\x00-\x1F\x7F]+/g, ' ').trim();
                if (candidate === '' || candidate.length > 320) {
                    return fallback;
                }

                const sensitivePatterns = /SQLSTATE|Exception|Stack trace|\.php\b|vendor\/|app\/Services|[A-Z]:\\\\|\/var\/www|\/home\/|AIza[0-9A-Za-z\-_]+|gsk_[0-9A-Za-z\-_]+|tvly-[0-9A-Za-z\-_]+|Bearer\s+[A-Za-z0-9\-_.]+|\b(gemini|groq|tavily)\b/i;
                if (sensitivePatterns.test(candidate)) {
                    return fallback;
                }

                return candidate;
            },

            async parseHttpOutcome(response) {
                const status = Number(response && response.status ? response.status : 0);

                let rawBody = '';
                try {
                    rawBody = typeof response.text === 'function' ? await response.text() : '';
                } catch (_) {
                    rawBody = '';
                }

                let data = null;
                if (rawBody !== '') {
                    try {
                        data = JSON.parse(rawBody);
                    } catch (_) {
                        data = null;
                    }
                } else if (typeof response.json === 'function') {
                    try {
                        data = await response.json();
                    } catch (_) {
                        data = null;
                    }
                }

                if (status === 419) {
                    return {
                        ok: false,
                        errorCode: 'http_419',
                        showReloadHint: true,
                        message: 'Sesi halaman Anda telah kedaluwarsa. Silakan muat ulang halaman ini, lalu kirim kembali pertanyaan Anda.',
                    };
                }

                if (status === 422) {
                    const validationMsg = this.extractSafeValidationMessage(data);
                    return {
                        ok: false,
                        errorCode: 'http_422',
                        message: validationMsg,
                    };
                }

                if (status === 429) {
                    return {
                        ok: false,
                        errorCode: 'http_429',
                        message: 'Permintaan Anda sedang dibatasi sementara karena terlalu banyak percakapan dalam waktu singkat. Mohon tunggu sebentar lalu tekan Coba Lagi.',
                    };
                }

                if (status >= 500 || status === 404) {
                    return {
                        ok: false,
                        errorCode: `http_${status}`,
                        message: 'Maaf, server Asisten Digital sedang mengalami gangguan sementara. Silakan tekan Coba Lagi beberapa saat lagi.',
                    };
                }

                if (response && response.ok === false) {
                    return {
                        ok: false,
                        errorCode: `http_${status}`,
                        message: 'Maaf, permintaan belum dapat diproses saat ini. Silakan tekan Coba Lagi.',
                    };
                }

                if (!data || typeof data !== 'object' || Array.isArray(data) || typeof data.success !== 'boolean') {
                    return {
                        ok: false,
                        errorCode: 'invalid_json',
                        message: 'Maaf, respons dari server tidak dapat dibaca dengan lengkap. Silakan tekan Coba Lagi.',
                    };
                }

                if (data.success === true) {
                    if (typeof data.reply !== 'string' || data.reply.trim() === '') {
                        return {
                            ok: false,
                            errorCode: 'invalid_json',
                            message: 'Maaf, jawaban dari asisten diterima dalam kondisi tidak lengkap. Silakan tekan Coba Lagi.',
                        };
                    }

                    return {
                        ok: true,
                        reply: data.reply,
                    };
                }

                return {
                    ok: false,
                    errorCode: 'ai_unavailable',
                    message: this.sanitizeUserFacingError(
                        data.message,
                        'Maaf, Asisten Digital Kecamatan Cikampek sedang mengalami kendala teknis. Silakan coba beberapa saat lagi.'
                    ),
                };
            },

            extractSafeValidationMessage(data) {
                const defaultValidation = 'Pertanyaan tidak dapat diproses. Pastikan pesan terisi dan maksimal 500 karakter.';
                if (!data || typeof data !== 'object') {
                    return defaultValidation;
                }

                if (data.errors && typeof data.errors === 'object') {
                    for (const key of Object.keys(data.errors)) {
                        const fieldErrors = data.errors[key];
                        if (Array.isArray(fieldErrors) && typeof fieldErrors[0] === 'string') {
                            const safeField = this.sanitizeUserFacingError(fieldErrors[0], '');
                            if (safeField !== '' && !/The .+ field/i.test(safeField)) {
                                return safeField;
                            }
                        }
                    }
                }

                if (typeof data.message === 'string') {
                    const lower = data.message.toLowerCase();
                    if (lower.includes('history') || lower.includes('role')) {
                        return 'Riwayat percakapan tidak valid. Silakan tekan Reset percakapan di kanan atas lalu coba kembali.';
                    }
                    if (lower.includes('validation') || lower.includes('csrf') || lower.includes('given data was invalid')) {
                        return defaultValidation;
                    }
                    return this.sanitizeUserFacingError(data.message, defaultValidation);
                }

                return defaultValidation;
            },

            resolveFetchException(error, timedOut) {
                if (timedOut || (error && error.name === 'AbortError')) {
                    return {
                        errorCode: 'timeout',
                        message: 'Asisten membutuhkan waktu terlalu lama untuk merespons. Silakan tekan tombol Coba Lagi.',
                    };
                }

                return {
                    errorCode: 'network_error',
                    message: 'Koneksi internet Anda tampak terputus atau tidak stabil. Periksa jaringan lalu tekan tombol Coba Lagi.',
                };
            },

            scrollToBottom() {
                setTimeout(() => {
                    const container = typeof document !== 'undefined' ? document.getElementById('chatbot-messages') : null;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 50);
            },

            escapeHtml(text) {
                return String(text ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            },

            formatUserMessage(text) {
                return this.escapeHtml(text).replace(/\r\n|\r|\n/g, '<br>');
            },

            isSafeMarkdownUrl(escapedUrl) {
                if (typeof escapedUrl !== 'string' || escapedUrl.trim() === '') {
                    return false;
                }
                if (/&(quot|lt|gt|#0?39);/i.test(escapedUrl)) {
                    return false;
                }
                const decoded = escapedUrl
                    .replace(/&amp;/g, '&')
                    .trim();

                if (/[\s<>"'`\\]/.test(decoded) || /[\x00-\x1F\x7F]/.test(decoded)) {
                    return false;
                }

                if (!/^https?:\/\//i.test(decoded)) {
                    return false;
                }

                try {
                    const parsed = new URL(decoded);
                    return (parsed.protocol === 'http:' || parsed.protocol === 'https:') && Boolean(parsed.hostname);
                } catch (_) {
                    return false;
                }
            },

            formatMessage(text) {
                let html = this.escapeHtml(text);
                html = html.replace(/\u0000/g, '').replace(/\r\n|\r/g, '\n');

                // Extract fenced code blocks before line-by-line block parsing
                const codeBlocks = [];
                html = html.replace(/```[^\n]*\n([\s\S]*?)```/g, (_, codeBody) => {
                    const token = `\u0000CODEBLOCK${codeBlocks.length}\u0000`;
                    codeBlocks.push(`<pre class="bg-slate-900 text-slate-100 rounded-lg p-2.5 text-xs overflow-x-auto my-2"><code>${codeBody.replace(/\n$/, '')}</code></pre>`);
                    return token;
                });

                const lines = html.split('\n');
                const output = [];
                let paragraphBuffer = [];
                let i = 0;

                const flushParagraph = () => {
                    if (paragraphBuffer.length > 0) {
                        const joined = paragraphBuffer.map((ln) => this.formatInlineMarkdown(ln)).join('<br>');
                        output.push(`<p>${joined}</p>`);
                        paragraphBuffer = [];
                    }
                };

                while (i < lines.length) {
                    const line = lines[i];
                    const trimmed = line.trim();

                    if (trimmed === '') {
                        flushParagraph();
                        i++;
                        continue;
                    }

                    const codeMatch = trimmed.match(/^\u0000CODEBLOCK(\d+)\u0000$/);
                    if (codeMatch) {
                        flushParagraph();
                        output.push(codeBlocks[Number(codeMatch[1])] ?? '');
                        i++;
                        continue;
                    }

                    // Horizontal rule
                    if (/^(?:-{3,}|\*{3,}|_{3,})$/.test(trimmed)) {
                        flushParagraph();
                        output.push('<hr>');
                        i++;
                        continue;
                    }

                    // Headings (# to ####)
                    const headingMatch = trimmed.match(/^(#{1,4})\s+(.+)$/);
                    if (headingMatch) {
                        flushParagraph();
                        const level = headingMatch[1].length;
                        output.push(`<h${level}>${this.formatInlineMarkdown(headingMatch[2])}</h${level}>`);
                        i++;
                        continue;
                    }

                    // Markdown Table (header row + separator row + body rows)
                    if (trimmed.includes('|') && (i + 1) < lines.length && this.isTableSeparatorLine(lines[i + 1])) {
                        flushParagraph();
                        const headers = this.parseTableCells(lines[i]);
                        const alignments = this.parseTableAlignments(lines[i + 1]);
                        i += 2;

                        const bodyRows = [];
                        while (i < lines.length) {
                            const rowTrimmed = lines[i].trim();
                            if (rowTrimmed === '' || !rowTrimmed.includes('|') || /^(#{1,4}\s+|&gt;\s?|(?:[-*]|\u2022)\s+|\d+\.\s+)/.test(rowTrimmed)) {
                                break;
                            }
                            bodyRows.push(this.parseTableCells(lines[i]));
                            i++;
                        }

                        const thHtml = headers.map((cell, idx) => {
                            const alignClass = alignments[idx] || 'text-left';
                            return `<th class="${alignClass}">${this.formatInlineMarkdown(cell)}</th>`;
                        }).join('');

                        const trHtml = bodyRows.map((cells) => {
                            const tds = headers.map((_, idx) => {
                                const alignClass = alignments[idx] || 'text-left';
                                const val = idx < cells.length ? cells[idx] : '';
                                return `<td class="${alignClass}">${this.formatInlineMarkdown(val)}</td>`;
                            }).join('');
                            return `<tr>${tds}</tr>`;
                        }).join('');

                        output.push(`<div class="chatbot-table-wrap"><table><thead><tr>${thHtml}</tr></thead><tbody>${trHtml}</tbody></table></div>`);
                        continue;
                    }

                    // Unordered / Bullet list (- or *)
                    if (/^(?:[-*]|\u2022)\s+(.+)$/.test(trimmed)) {
                        flushParagraph();
                        const items = [];
                        while (i < lines.length) {
                            const itemMatch = lines[i].trim().match(/^(?:[-*]|\u2022)\s+(.+)$/);
                            if (!itemMatch) {
                                break;
                            }
                            items.push(`<li>${this.formatInlineMarkdown(itemMatch[1])}</li>`);
                            i++;
                        }
                        output.push(`<ul>${items.join('')}</ul>`);
                        continue;
                    }

                    // Ordered / Numbered list (1. ...)
                    if (/^\d+\.\s+(.+)$/.test(trimmed)) {
                        flushParagraph();
                        const items = [];
                        while (i < lines.length) {
                            const itemMatch = lines[i].trim().match(/^\d+\.\s+(.+)$/);
                            if (!itemMatch) {
                                break;
                            }
                            items.push(`<li>${this.formatInlineMarkdown(itemMatch[1])}</li>`);
                            i++;
                        }
                        output.push(`<ol>${items.join('')}</ol>`);
                        continue;
                    }

                    // Blockquote (> after escapeHtml becomes &gt;)
                    if (/^&gt;\s?(.*)$/.test(trimmed)) {
                        flushParagraph();
                        const quotes = [];
                        while (i < lines.length) {
                            const qMatch = lines[i].trim().match(/^&gt;\s?(.*)$/);
                            if (!qMatch) {
                                break;
                            }
                            quotes.push(this.formatInlineMarkdown(qMatch[1]));
                            i++;
                        }
                        output.push(`<blockquote>${quotes.join('<br>')}</blockquote>`);
                        continue;
                    }

                    paragraphBuffer.push(line);
                    i++;
                }

                flushParagraph();

                return output.join('');
            },

            formatInlineMarkdown(escapedLine) {
                const placeholders = [];
                const hold = (snippet) => {
                    const token = `\u0000PH${placeholders.length}\u0000`;
                    placeholders.push(snippet);
                    return token;
                };

                let out = String(escapedLine ?? '');

                // 1. Protect inline code spans before links/emphasis
                out = out.replace(/`([^`\n]+?)`/g, (_, code) => {
                    return hold(`<code class="px-1 py-0.5 bg-gray-100 text-slate-800 rounded text-[12px] font-mono">${code}</code>`);
                });

                // 2. Convert validated http/https Markdown links into safe anchors and protect URLs via placeholders
                out = out.replace(/\[([^\[\]\n]+?)\]\((https?:\/\/[^\s<>"')\\]+)\)/g, (_, label, url) => {
                    if (!this.isSafeMarkdownUrl(url)) {
                        return this.applyEmphasis(label);
                    }
                    const cleanLabel = this.applyEmphasis(label);
                    return hold(`<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-800 break-words font-medium">${cleanLabel}</a>`);
                });

                // 3. Neutralize any remaining non-http Markdown links (e.g. javascript:, data:, vbscript:)
                out = out.replace(/\[([^\[\]\n]+?)\]\(([^()\n]+?)\)/g, (_, label) => {
                    return this.applyEmphasis(label);
                });

                // 4. Bold and italic emphasis
                out = this.applyEmphasis(out);

                // 5. Restore protected code/link tokens
                out = out.replace(/\u0000PH(\d+)\u0000/g, (_, idx) => placeholders[Number(idx)] ?? '');

                return out;
            },

            applyEmphasis(escapedSegment) {
                return String(escapedSegment ?? '')
                    .replace(/\*\*([^\n*](?:[^\n]*?[^\n*])?)\*\*/g, '<strong>$1</strong>')
                    .replace(/(^|[^*])\*([^\s*](?:[^\n*]*?[^\s*])?)\*(?!\*)/g, '$1<em>$2</em>');
            },

            parseTableCells(line) {
                let raw = String(line ?? '').trim();
                if (raw.startsWith('|')) {
                    raw = raw.slice(1);
                }
                if (raw.endsWith('|')) {
                    raw = raw.slice(0, -1);
                }
                return raw.split('|').map((cell) => cell.trim());
            },

            isTableSeparatorLine(line) {
                const trimmed = String(line ?? '').trim();
                if (!trimmed.includes('|') || !trimmed.includes('-')) {
                    return false;
                }
                return /^\|?(?:\s*:?-{2,}:?\s*\|)+\s*(?::?-{2,}:?\s*)?\|?$/.test(trimmed);
            },

            parseTableAlignments(separatorLine) {
                return this.parseTableCells(separatorLine).map((cell) => {
                    const startsColon = cell.startsWith(':');
                    const endsColon = cell.endsWith(':');
                    if (startsColon && endsColon) return 'text-center';
                    if (endsColon) return 'text-right';
                    return 'text-left';
                });
            },
        };
    };

    if (typeof document !== 'undefined') {
        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined') {
                Alpine.data('chatbot', () => window.createSipafChatbot());
            }
        });
    }
</script>
