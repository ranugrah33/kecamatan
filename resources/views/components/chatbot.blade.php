<!-- Floating Chatbot Component -->
<div x-data="chatbot()" class="fixed bottom-6 right-6 z-50 font-sans" style="font-family: 'Inter', sans-serif;">
    <!-- Chat Button -->
    <button @click="toggleChat" x-show="!isOpen" x-transition.scale.origin.bottom.right 
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-110 focus:outline-none">
        <i class="ph ph-robot text-3xl"></i>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-4 scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
         x-transition:leave-end="opacity-0 translate-y-4 scale-95" 
         class="w-[340px] sm:w-[380px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden" 
         style="height: 550px; max-height: 85vh;">
        
        <!-- Header -->
        <div class="bg-blue-600 px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3 text-white">
                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="ph ph-robot text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Asisten Kecamatan</h3>
                    <p class="text-[11px] text-blue-100">Kecamatan Cikampek</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="toggleChat" class="text-blue-100 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 p-4 overflow-y-auto bg-[#f5f7fb] flex flex-col gap-4 scroll-smooth" id="chatbot-messages">
            <!-- Initial Message -->
            <div class="flex items-start gap-2.5">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-200/50">
                    <i class="ph ph-robot text-sm"></i>
                </div>
                <div class="bg-white border border-gray-200/80 px-3.5 py-2.5 rounded-2xl rounded-tl-sm text-sm text-gray-700 shadow-sm leading-relaxed">
                    Halo! Saya Asisten Kecamatan Cikampek.<br>Ada yang ingin Anda tanyakan mengenai pelayanan kecamatan?
                </div>
            </div>

            <!-- Quick Questions -->
            <div class="flex flex-wrap gap-2 ml-10">
                <button @click="sendQuick('Pelayanan apa saja?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Pelayanan apa saja?</button>
                <button @click="sendQuick('Cara pinjam aula?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Cara pinjam aula?</button>
                <button @click="sendQuick('Pengajuan sertifikat?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Pengajuan sertifikat?</button>
                <button @click="sendQuick('Cara buat surat ahli waris?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Ahli Waris?</button>
                <button @click="sendQuick('Cara izin usaha UMKM?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Izin UMKM?</button>
                <button @click="sendQuick('Info bantuan sosial?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Info Bansos?</button>
                <button @click="sendQuick('Cara urus jual beli tanah?')" class="text-xs font-medium bg-white border border-blue-200 text-blue-600 px-3.5 py-1.5 rounded-full hover:bg-blue-50 transition shadow-sm">Jual Beli Tanah?</button>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex items-start gap-2.5" :class="msg.sender === 'user' ? 'flex-row-reverse' : ''">
                    <template x-if="msg.sender === 'bot'">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-200/50">
                            <i class="ph ph-robot text-sm"></i>
                        </div>
                    </template>
                    <div class="px-3.5 py-2.5 rounded-2xl text-sm shadow-sm max-w-[85%] leading-relaxed" 
                         :class="msg.sender === 'user' ? 'bg-blue-600 text-white rounded-tr-sm' : (msg.isError ? 'bg-red-50 border border-red-200 text-red-700 rounded-tl-sm' : 'bg-white border border-gray-200/80 text-gray-700 rounded-tl-sm')"
                         x-html="formatMessage(msg.text)">
                    </div>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex items-start gap-2.5">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-200/50">
                    <i class="ph ph-robot text-sm"></i>
                </div>
                <div class="bg-white border border-gray-200/80 px-4 py-3.5 rounded-2xl rounded-tl-sm shadow-sm flex items-center gap-1">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            </div>

        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100 shrink-0">
            <form @submit.prevent="sendMessage" data-no-hud class="flex items-center gap-2">
                <input type="text" x-model="userInput" :disabled="isLoading" 
                       placeholder="Ketik pertanyaan Anda..." 
                       class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:bg-white disabled:opacity-50 transition">
                <button type="submit" :disabled="isLoading || userInput.trim() === ''" 
                        class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center disabled:opacity-50 hover:bg-blue-700 transition shadow-sm">
                    <i class="ph ph-paper-plane-right text-lg"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    #chatbot-messages::-webkit-scrollbar { width: 4px; }
    #chatbot-messages::-webkit-scrollbar-track { background: transparent; }
    #chatbot-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    #chatbot-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatbot', () => ({
            isOpen: false,
            isLoading: false,
            userInput: '',
            messages: [],
            
            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    setTimeout(() => this.scrollToBottom(), 100);
                }
            },
            
            sendQuick(text) {
                this.userInput = text;
                this.sendMessage();
            },
            
            async sendMessage() {
                if (this.userInput.trim() === '') return;
                
                const message = this.userInput.trim();
                this.messages.push({ sender: 'user', text: message });
                this.userInput = '';
                this.isLoading = true;
                this.scrollToBottom();
                
                try {
                    const response = await fetch('{{ route('chatbot.chat') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: message })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.messages.push({ sender: 'bot', text: data.reply });
                    } else {
                        this.messages.push({ sender: 'bot', text: data.message || 'Maaf, terjadi kesalahan pada server.', isError: true });
                    }
                } catch (error) {
                    this.messages.push({ sender: 'bot', text: 'Maaf, sistem sedang offline atau terjadi gangguan jaringan.', isError: true });
                } finally {
                    this.isLoading = false;
                    this.scrollToBottom();
                }
            },
            
            scrollToBottom() {
                setTimeout(() => {
                    const container = document.getElementById('chatbot-messages');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 50);
            },
            
            formatMessage(text) {
                let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
                html = html.replace(/\n/g, '<br>');
                return html;
            }
        }));
    });
</script>
