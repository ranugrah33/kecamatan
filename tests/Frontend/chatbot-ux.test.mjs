import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const BLADE_PATH = path.resolve(__dirname, '../../resources/views/components/chatbot.blade.php');

function extractScriptFromBlade() {
    const html = fs.readFileSync(BLADE_PATH, 'utf8');
    const matches = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)];
    assert.ok(matches.length >= 1, 'chatbot.blade.php harus memiliki tag <script>.');
    return matches[matches.length - 1][1];
}

function createChatbotHarness({ fetchImpl, requestTimeoutMs = 35000, csrfToken = 'test-csrf-token' } = {}) {
    let alpineFactory = null;

    const elementsById = {
        'chatbot-messages': { scrollTop: 0, scrollHeight: 420 },
        'chatbot-input': { focused: false, focus() { this.focused = true; } },
    };

    const documentMock = {
        listeners: {},
        querySelector(selector) {
            if (selector === '[data-sipaf-chatbot]') {
                return { dataset: { chatUrl: '/chatbot/chat', csrfToken } };
            }
            if (selector === 'meta[name="csrf-token"]') {
                return { getAttribute: () => csrfToken };
            }
            return null;
        },
        getElementById(id) {
            return elementsById[id] || null;
        },
        addEventListener(event, cb) {
            this.listeners[event] = cb;
        },
    };

    const windowMock = {
        location: { reloaded: false, reload() { this.reloaded = true; } },
    };

    const context = {
        window: windowMock,
        document: documentMock,
        Alpine: {
            data(name, factory) {
                if (name === 'chatbot') {
                    alpineFactory = factory;
                }
            },
        },
        fetch: fetchImpl || (async () => ({
            ok: true,
            status: 200,
            text: async () => JSON.stringify({ success: true, reply: 'Halo warga!' }),
        })),
        AbortController,
        URL,
        setTimeout,
        clearTimeout,
        console,
    };

    vm.createContext(context);
    const scriptCode = extractScriptFromBlade();
    vm.runInContext(scriptCode, context);

    assert.equal(typeof context.window.createSipafChatbot, 'function', 'window.createSipafChatbot harus teregistrasi.');

    if (typeof documentMock.listeners['alpine:init'] === 'function') {
        documentMock.listeners['alpine:init']();
        assert.equal(typeof alpineFactory, 'function', 'Alpine.data("chatbot") harus terdaftar.');
    }

    const bot = context.window.createSipafChatbot({
        chatUrl: '/chatbot/chat',
        csrfToken,
        requestTimeoutMs,
    });
    bot.init();

    return { bot, elementsById, windowMock, scriptCode };
}

function jsonResponse(status, bodyObj, ok = status >= 200 && status < 300) {
    const serialized = typeof bodyObj === 'string' ? bodyObj : JSON.stringify(bodyObj);
    return {
        ok,
        status,
        text: async () => serialized,
        json: async () => JSON.parse(serialized),
    };
}

test('1. Handles HTTP 419 (expired CSRF/session) cleanly in Indonesian', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(419, { message: 'CSRF token mismatch.' }, false),
    });

    bot.userInput = 'Halo';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages.length, 2);
    assert.equal(bot.messages[0].status, 'failed');
    assert.equal(bot.messages[1].isError, true);
    assert.equal(bot.messages[1].errorCode, 'http_419');
    assert.equal(bot.messages[1].showReloadHint, true);
    assert.match(bot.messages[1].text, /kedaluwarsa|muat ulang/i);
});

test('2. Handles HTTP 422 (validation error) without exposing technical stack details', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(422, {
            message: 'The given data was invalid.',
            errors: { message: ['Pertanyaan maksimal 500 karakter.'] },
        }, false),
    });

    bot.userInput = 'Pertanyaan uji';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages[1].isError, true);
    assert.equal(bot.messages[1].errorCode, 'http_422');
    assert.match(bot.messages[1].text, /maksimal 500 karakter/i);
    assert.doesNotMatch(bot.messages[1].text, /Exception|SQLSTATE|vendor/i);
});

test('3. Handles HTTP 429 (rate limit) with calm wait guidance', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(429, { message: 'Too Many Attempts.' }, false),
    });

    bot.userInput = 'Cara pinjam aula?';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages[1].errorCode, 'http_429');
    assert.match(bot.messages[1].text, /terlalu banyak|tunggu sebentar/i);
});

test('4. Handles HTTP 500 without ever displaying stack trace, SQL, API key, or file paths', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(500, {
            message: 'SQLSTATE[42S02]: Table missing in C:\\xampp\\htdocs\\app\\Services\\AI\\GeminiService.php with key AIzaSySecret123456',
            trace: ['#0 vendor/laravel/framework'],
        }, false),
    });

    bot.userInput = 'Pelayanan apa saja?';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    const errMsg = bot.messages[1];
    assert.equal(errMsg.isError, true);
    assert.equal(errMsg.errorCode, 'http_500');
    assert.match(errMsg.text, /gangguan sementara|Coba Lagi/i);
    assert.doesNotMatch(errMsg.text, /SQLSTATE|AIzaSy|GeminiService|xampp|\.php|trace/i);
});

test('5. Handles network error (fetch rejection) gracefully', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => {
            throw new TypeError('Failed to fetch');
        },
    });

    bot.userInput = 'Info jam operasional?';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages[1].errorCode, 'network_error');
    assert.match(bot.messages[1].text, /koneksi internet|terputus|Coba Lagi/i);
});

test('6. Handles client-side request timeout via AbortController and stops loading cleanly', async () => {
    const { bot } = createChatbotHarness({
        requestTimeoutMs: 25,
        fetchImpl: (_url, opts) => new Promise((_resolve, reject) => {
            opts.signal.addEventListener('abort', () => {
                const err = new Error('The operation was aborted.');
                err.name = 'AbortError';
                reject(err);
            });
        }),
    });

    bot.userInput = 'Siapa Camat Cikampek?';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages[1].errorCode, 'timeout');
    assert.match(bot.messages[1].text, /terlalu lama|Coba Lagi/i);
});

test('7. Handles invalid or incomplete JSON payloads safely without crashing', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => ({
            ok: true,
            status: 200,
            text: async () => '<html><body>Unexpected Bad Gateway</body></html>',
        }),
    });

    bot.userInput = 'Pertanyaan 1';
    await bot.sendMessage();
    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages[1].errorCode, 'invalid_json');
    assert.match(bot.messages[1].text, /tidak dapat dibaca|Coba Lagi/i);

    // Also verify incomplete success payload missing reply string
    const { bot: bot2 } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(200, { success: true, reply: '   ' }, true),
    });
    bot2.userInput = 'Pertanyaan 2';
    await bot2.sendMessage();
    assert.equal(bot2.messages[1].errorCode, 'invalid_json');
});

test('8. Prevents double submit via Enter + click or rapid quick-question clicks', async () => {
    let fetchCalls = 0;
    let releaseFetch;
    const { bot } = createChatbotHarness({
        fetchImpl: () => {
            fetchCalls++;
            return new Promise((resolve) => {
                releaseFetch = () => resolve(jsonResponse(200, { success: true, reply: 'Jawaban tunggal.' }));
            });
        },
    });

    bot.userInput = 'Cara pinjam aula?';
    const p1 = bot.sendMessage();
    const p2 = bot.sendMessage(); // double click / Enter right after
    const p3 = bot.sendQuick('Luas Desa?'); // quick pill clicked while loading

    assert.equal(bot.isLoading, true);
    assert.equal(fetchCalls, 1, 'Hanya boleh 1 request fetch berjalan saat isLoading = true.');

    releaseFetch();
    await Promise.all([p1, p2, p3]);

    assert.equal(bot.isLoading, false);
    assert.equal(fetchCalls, 1);
    assert.equal(bot.messages.length, 2);
});

test('9. Loading indicator resets cleanly to false after HTTP 500, timeout, network, or JSON errors', async () => {
    const errorFactories = [
        () => jsonResponse(500, 'Server Error', false),
        () => { throw new TypeError('Failed to fetch'); },
        () => ({ ok: true, status: 200, text: async () => 'not-json' }),
    ];

    for (const makeFetch of errorFactories) {
        const { bot } = createChatbotHarness({ fetchImpl: makeFetch });
        bot.userInput = 'Cek layanan';
        await bot.sendMessage();
        assert.equal(bot.isLoading, false, 'isLoading tidak boleh menggantung setelah error.');
    }
});

test('10. Failed user message stays preserved visibly in conversation with failed indicator', async () => {
    const { bot } = createChatbotHarness({
        fetchImpl: async () => jsonResponse(503, 'Service Unavailable', false),
    });

    bot.userInput = 'Persyaratan KTP elektronik?';
    await bot.sendMessage();

    assert.equal(bot.messages.length, 2);
    assert.equal(bot.messages[0].sender, 'user');
    assert.equal(bot.messages[0].text, 'Persyaratan KTP elektronik?');
    assert.equal(bot.messages[0].status, 'failed');
    assert.equal(bot.lastFailedMessage, 'Persyaratan KTP elektronik?');
    assert.equal(bot.messages[1].canRetry, true);
    assert.equal(bot.messages[1].retryMessage, 'Persyaratan KTP elektronik?');
});

test('11. Retry resends ONLY the single failed message without duplicating bubbles or looping infinitely', async () => {
    let callNum = 0;
    const capturedBodies = [];

    const { bot } = createChatbotHarness({
        fetchImpl: async (_url, opts) => {
            callNum++;
            capturedBodies.push(JSON.parse(opts.body));
            if (callNum === 1) {
                return jsonResponse(200, { success: true, reply: 'Aula berada di kantor kecamatan.' });
            }
            if (callNum === 2) {
                return jsonResponse(500, { message: 'Temporary error' }, false);
            }
            return jsonResponse(200, { success: true, reply: 'Syaratnya surat permohonan dan KTP.' });
        },
    });

    // Turn 1 succeeds
    bot.userInput = 'Dimana lokasi aula?';
    await bot.sendMessage();

    // Turn 2 fails
    bot.userInput = 'Apa syarat peminjaman?';
    await bot.sendMessage();

    assert.equal(bot.messages.length, 4);
    const errId = bot.messages[3].id;
    assert.equal(bot.messages[3].isError, true);

    // Retry ONLY failed turn 2
    await bot.retryFailedMessage(errId);

    assert.equal(callNum, 3, 'Retry hanya kirim ulang manual 1x tanpa retry loop otomatis.');
    assert.equal(capturedBodies[2].message, 'Apa syarat peminjaman?');
    assert.equal(capturedBodies[2].history.length, 2, 'History retry hanya memuat giliran sukses sebelum pesan gagal.');
    assert.equal(bot.messages.length, 4, 'User bubble tidak diduplikasi dan kartu error lama diganti jawaban sukses.');
    assert.equal(bot.messages[2].status, 'sent');
    assert.equal(bot.messages[3].isError, false);
    assert.equal(bot.messages[3].text, 'Syaratnya surat permohonan dan KTP.');
});

test('12. Reset clears local chat history safely and blocks late stale responses', async () => {
    let resolveSlowFetch;
    const { bot } = createChatbotHarness({
        fetchImpl: () => new Promise((resolve) => {
            resolveSlowFetch = () => resolve(jsonResponse(200, { success: true, reply: 'Respons lama yang terlambat.' }));
        }),
    });

    bot.messages.push({ id: 'u1', sender: 'user', text: 'Halo', status: 'sent', isError: false });
    bot.userInput = 'Pertanyaan lambat';
    const slowPromise = bot.sendMessage();
    assert.equal(bot.isLoading, true);

    // User opens reset confirmation bar and confirms
    bot.requestResetChat();
    assert.equal(bot.showResetConfirm, true);

    bot.confirmResetChat();
    assert.equal(bot.showResetConfirm, false);
    assert.equal(bot.isLoading, false);
    assert.equal(bot.messages.length, 0);

    // Late response from pre-reset request resolves later -> MUST NOT pollute empty chat
    resolveSlowFetch();
    await slowPromise;

    assert.equal(bot.messages.length, 0, 'Respons request lama tidak boleh menimpa chat yang baru di-reset.');
    assert.equal(bot.isLoading, false);
});

test('13. Markdown renders headings, bold, italic, numbered & bullet lists, responsive table wrapper, hr, and safe links', () => {
    const { bot } = createChatbotHarness();

    const md = [
        '## Panduan Layanan SIPAF',
        '',
        'Berikut **ringkasan penting** dan *catatan* untuk warga:',
        '',
        '- Bawa KTP asli',
        '- Siapkan *surat pengantar*',
        '',
        '1. Buka menu layanan',
        '2. Isi formulir online',
        '',
        '| Layanan | Estimasi |',
        '| :--- | ---: |',
        '| Pinjam Aula | 1 Hari |',
        '| Inventaris | 2 Hari |',
        '',
        '---',
        '',
        'Sumber: [Pemkab Karawang](https://karawangkab.go.id/layanan_publik?page=1)',
    ].join('\n');

    const rendered = bot.formatMessage(md);

    assert.match(rendered, /<h2>Panduan Layanan SIPAF<\/h2>/);
    assert.match(rendered, /<strong>ringkasan penting<\/strong>/);
    assert.match(rendered, /<em>catatan<\/em>/);
    assert.match(rendered, /<ul><li>Bawa KTP asli<\/li><li>Siapkan <em>surat pengantar<\/em><\/li><\/ul>/);
    assert.match(rendered, /<ol><li>Buka menu layanan<\/li><li>Isi formulir online<\/li><\/ol>/);
    assert.match(rendered, /<div class="chatbot-table-wrap"><table><thead><tr><th class="text-left">Layanan<\/th><th class="text-right">Estimasi<\/th><\/tr><\/thead>/);
    assert.match(rendered, /<td class="text-left">Pinjam Aula<\/td>/);
    assert.match(rendered, /<td class="text-right">1 Hari<\/td>/);
    assert.match(rendered, /<hr>/);
    assert.match(rendered, /<a href="https:\/\/karawangkab\.go\.id\/layanan_publik\?page=1" target="_blank" rel="noopener noreferrer"/);
    assert.doesNotMatch(rendered, /href="[^"]*<(em|strong)>/);
});

test('14. XSS attacks and placeholder smuggling are neutralized cleanly', () => {
    const { bot } = createChatbotHarness();

    const payloads = [
        '<script>alert("xss")</script>',
        '<img src=x onerror=alert(1)>',
        '<svg onload=confirm(1)>',
        '**tebal <script>bad()</script>**',
        '\u0000PH0\u0000\u0000CODEBLOCK0\u0000',
    ];

    for (const payload of payloads) {
        const rendered = bot.formatMessage(payload);
        assert.doesNotMatch(rendered, /<(script|img|svg|iframe|object|embed)\b/i, `XSS payload tidak boleh menghasilkan tag HTML berbahaya: ${payload}`);
        assert.doesNotMatch(rendered, /<[^>]+\s(onerror|onload|onclick)\s*=/i, `XSS payload tidak boleh menghasilkan atribut event HTML aktif: ${payload}`);
    }

    assert.match(bot.formatMessage('<script>alert(1)</script>'), /&lt;script&gt;/);
    assert.match(bot.formatMessage('<img src=x onerror=alert(1)>'), /&lt;img src=x onerror=alert\(1\)&gt;/);
});

test('15. Dangerous link URLs (javascript:, data:, vbscript:, attribute breakouts) are blocked strictly', () => {
    const { bot } = createChatbotHarness();

    const dangerousMarkdowns = [
        '[Klik](javascript:alert(1))',
        '[Klik](JAVASCRIPT:alert(document.cookie))',
        '[Klik](data:text/html,<script>alert(1)</script>)',
        '[Klik](vbscript:msgbox(1))',
        '[Klik](https://example.com"onclick=alert(1))',
        '![img](javascript:alert(1))',
    ];

    for (const sample of dangerousMarkdowns) {
        const rendered = bot.formatMessage(sample);
        assert.doesNotMatch(rendered, /<a\b/i, `URL berbahaya tidak boleh dirender menjadi tag <a>: ${sample}`);
        assert.doesNotMatch(rendered, /<img\b/i);
        assert.doesNotMatch(rendered, /onclick=/i);
    }
});

test('16. Normal conversation updates history cleanly and keeps chatbot responsive', async () => {
    const capturedRequests = [];
    const { bot } = createChatbotHarness({
        fetchImpl: async (url, opts) => {
            capturedRequests.push({ url, headers: opts.headers, body: JSON.parse(opts.body) });
            return jsonResponse(200, { success: true, reply: 'Jam layanan Senin–Jumat pukul **08.00–15.00 WIB**.' });
        },
    });

    bot.userInput = 'Jam pelayanan kecamatan?';
    await bot.sendMessage();

    assert.equal(bot.isLoading, false);
    assert.equal(capturedRequests.length, 1);
    assert.equal(capturedRequests[0].headers['Accept'], 'application/json');
    assert.equal(capturedRequests[0].headers['X-CSRF-TOKEN'], 'test-csrf-token');
    assert.equal(bot.messages.length, 2);
    assert.equal(bot.messages[0].status, 'sent');
    assert.equal(bot.messages[1].isError, false);
    assert.match(bot.formatMessage(bot.messages[1].text), /<strong>08\.00–15\.00 WIB<\/strong>/);

    // Turn 2 sends Turn 1 in history
    bot.userInput = 'Hari Sabtu apakah buka?';
    await bot.sendMessage();
    assert.equal(capturedRequests[1].body.history.length, 2);
    assert.equal(capturedRequests[1].body.history[0].role, 'user');
    assert.equal(capturedRequests[1].body.history[1].role, 'model');
});
