{{-- ═══════════════════════════════════════════════════════
     CHATBOT RAG — GSWStore Assistant
     ═══════════════════════════════════════════════════════ --}}
<div class="chatbot" id="chatbot">

    <button class="chatbot-fab" id="chatbot-fab" onclick="chatbot.toggle()" aria-label="Abrir asistente">
        <svg class="fab-icon-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="26" height="26">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
        <svg class="fab-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="26" height="26" style="display:none">
            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
        </svg>
    </button>

    <div class="chat-window" id="chat-window" role="dialog" aria-label="Asistente de GSWStore">
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/>
                    </svg>
                    <span class="chat-avatar-status"></span>
                </div>
                <div class="chat-header-text">
                    <h4>GSWStore Assistant</h4>
                    <span class="chat-status">En línea</span>
                </div>
            </div>
            <button class="chat-header-close" onclick="chatbot.toggle()" aria-label="Cerrar chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <div class="chat-body" id="chat-body"></div>

        <div class="chat-products" id="chat-products" style="display:none">
            <div class="chat-products-head">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.27 6.96 8.73 5.05 8.73-5.05"/><path d="M12 22.08V12"/>
                    </svg>
                    Productos encontrados
                </span>
                <button onclick="chatbot.closeProducts()" aria-label="Ocultar productos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
            <div class="chat-products-body" id="chat-products-body"></div>
        </div>

        <div class="chat-footer">
            <form class="chat-form" id="chat-form" onsubmit="chatbot.send(event)">
                <input type="text" class="chat-input" id="chat-input"
                       placeholder="Escribe tu pregunta..." maxlength="500" autocomplete="off"
                       aria-label="Escribe tu pregunta">
                <button type="submit" class="chat-send" id="chat-send" aria-label="Enviar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="19" height="19">
                        <path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const chatbot = {
    isOpen: false,
    isSending: false,
    historyLoaded: false,

    icons: {
        activity: '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        tag: '<path d="M12 2H2v10l9.3 9.3a1 1 0 0 0 1.4 0l8.6-8.6a1 1 0 0 0 0-1.4Z"/><circle cx="7" cy="7" r="1"/>',
        compass: '<circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-2.12 6.36-6.36 2.12 2.12-6.36 6.36-2.12z"/>',
        cpu: '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"/>',
        star: '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
        home: '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        bag: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        mountain: '<path d="m8 3 4 8 5-5 5 15H2L8 3z"/>',
        box: '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.27 6.96 8.73 5.05 8.73-5.05"/><path d="M12 22.08V12"/>'
    },

    svgIcon(name, size) {
        size = size || 15;
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="' + size + '" height="' + size + '" aria-hidden="true">' + (this.icons[name] || this.icons.box) + '</svg>';
    },

    categoryIcon(cat) {
        if (!cat) return 'box';
        var n = cat.name || cat;
        if (n.indexOf('Deporte') !== -1) return 'activity';
        if (n.indexOf('Tecnolog') !== -1) return 'cpu';
        if (n.indexOf('Hogar') !== -1) return 'home';
        if (n.indexOf('Moda') !== -1) return 'bag';
        if (n.indexOf('Outdoor') !== -1) return 'mountain';
        return 'box';
    },

    toggle() {
        this.isOpen = !this.isOpen;
        const win = document.getElementById('chat-window');
        const fab = document.getElementById('chatbot-fab');
        const iconOpen = fab.querySelector('.fab-icon-open');
        const iconClose = fab.querySelector('.fab-icon-close');

        if (this.isOpen) {
            win.classList.add('open');
            iconOpen.style.display = 'none';
            iconClose.style.display = 'block';
            if (!this.historyLoaded) {
                this.loadHistory();
            }
            document.getElementById('chat-input').focus();
        } else {
            win.classList.remove('open');
            iconOpen.style.display = 'block';
            iconClose.style.display = 'none';
        }
    },

    /* ── Historial en sessionStorage (se borra al cerrar pestaña) ── */
    getLocalHistory() {
        try {
            return JSON.parse(sessionStorage.getItem('gsw_chat_history') || '[]');
        } catch (e) { return []; }
    },

    saveLocalHistory(history) {
        sessionStorage.setItem('gsw_chat_history', JSON.stringify(history));
    },

    async loadHistory() {
        const local = this.getLocalHistory();

        if (local.length > 0) {
            const body = document.getElementById('chat-body');
            body.innerHTML = '';
            local.forEach(msg => {
                this.appendMessage(msg.role, msg.content, true, msg.time, false);
            });
            this.historyLoaded = true;
            body.scrollTop = body.scrollHeight;
        } else {
            try {
                const response = await fetch('{{ route("chat.history") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.success && data.history.length > 0) {
                    const body = document.getElementById('chat-body');
                    body.innerHTML = '';
                    const localData = [];
                    data.history.forEach(msg => {
                        this.appendMessage(msg.role, msg.content, true, msg.time, false);
                        localData.push({ role: msg.role, content: msg.content, time: msg.time });
                    });
                    this.saveLocalHistory(localData);
                    this.historyLoaded = true;
                    body.scrollTop = body.scrollHeight;
                } else {
                    this.showWelcome();
                    this.historyLoaded = true;
                }
            } catch (e) {
                this.showWelcome();
                this.historyLoaded = true;
            }
        }
    },

    showWelcome() {
        const body = document.getElementById('chat-body');
        body.innerHTML = '';
        this.appendMessage('bot',
            'Hola, soy el asistente de **GSWStore**. Puedo ayudarte a encontrar productos, comparar precios o darte recomendaciones. ¿En qué te ayudo?',
            true, 'ahora', false
        );
        const qa = document.createElement('div');
        qa.className = 'quick-actions';
        qa.id = 'quick-actions';
        qa.innerHTML = '<button class="qa-btn" onclick="chatbot.quick(\'¿Qué productos de deporte tienen?\')">' + this.svgIcon('activity') + 'Deportes</button>'
            + '<button class="qa-btn" onclick="chatbot.quick(\'Muéstrame los más baratos\')">' + this.svgIcon('tag') + 'Baratos</button>'
            + '<button class="qa-btn" onclick="chatbot.quick(\'¿Qué me recomiendan para hacer ejercicio?\')">' + this.svgIcon('compass') + 'Recomendar</button>'
            + '<button class="qa-btn" onclick="chatbot.quick(\'¿Qué tienen de tecnología?\')">' + this.svgIcon('cpu') + 'Tecnología</button>'
            + '<button class="qa-btn" onclick="chatbot.quick(\'Cuéntame sobre los productos destacados\')">' + this.svgIcon('star') + 'Destacados</button>';
        body.appendChild(qa);
    },

    quick(message) {
        document.getElementById('chat-input').value = message;
        document.getElementById('chat-form').dispatchEvent(new Event('submit'));
    },

    async send(e) {
        e.preventDefault();
        if (this.isSending) return;

        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (!message) return;

        this.isSending = true;
        document.getElementById('chat-send').disabled = true;

        const now = new Date();
        const userTime = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
        this.appendMessage('user', message, false, userTime, true);

        const local = this.getLocalHistory();
        local.push({ role: 'user', content: message, time: userTime });
        this.saveLocalHistory(local);

        input.value = '';

        const qa = document.getElementById('quick-actions');
        if (qa) qa.style.display = 'none';

        const typing = this.showTyping();

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json();
            typing.remove();

            if (data.success) {
                const respTime = new Date();
                const time = respTime.getHours().toString().padStart(2,'0') + ':' + respTime.getMinutes().toString().padStart(2,'0');
                this.appendMessage('bot', data.response, true, time, true);

                const local2 = this.getLocalHistory();
                local2.push({ role: 'bot', content: data.response, time: time });
                this.saveLocalHistory(local2);

                if (data.products && data.products.length > 0) {
                    this.showProducts(data.products);
                }
            } else {
                this.appendMessage('bot', 'Lo siento, ocurrió un error. Intenta de nuevo.', false, null, true);
            }
        } catch (error) {
            typing.remove();
            this.appendMessage('bot', 'No se pudo conectar. Verifica tu conexión e intenta de nuevo.', false, null, true);
        }

        this.isSending = false;
        document.getElementById('chat-send').disabled = false;
        input.focus();
    },

    appendMessage(role, content, isHtml, time, scroll) {
        if (role === 'assistant') role = 'bot'; // Normalizar rol del historial del servidor
        const body = document.getElementById('chat-body');
        const div = document.createElement('div');
        div.className = 'msg msg--' + role;
        if (!time) {
            const now = new Date();
            time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
        }
        const formattedContent = isHtml ? this.formatMarkdown(content) : this.escapeHtml(content);
        div.innerHTML = '<div class="msg-content">'
            + '<div class="msg-bubble">' + formattedContent + '</div>'
            + '<span class="msg-time">' + time + '</span>'
            + '</div>';
        body.appendChild(div);
        if (scroll !== false) body.scrollTop = body.scrollHeight;
    },

    showTyping() {
        const body = document.getElementById('chat-body');
        const div = document.createElement('div');
        div.className = 'msg msg--bot';
        div.id = 'typing-indicator';
        div.innerHTML = '<div class="msg-content">'
            + '<div class="typing-indicator">'
            + '<div class="typing-dot"></div>'
            + '<div class="typing-dot"></div>'
            + '<div class="typing-dot"></div>'
            + '</div></div>';
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        return div;
    },

    showProducts(products) {
        const panel = document.getElementById('chat-products');
        const list = document.getElementById('chat-products-body');
        const self = this;
        list.innerHTML = products.slice(0, 5).map(function(p) {
            return '<a class="cp-item" href="/productos/' + p.slug + '">'
                + '<div class="cp-emoji">' + self.svgIcon(self.categoryIcon(p.category), 20) + '</div>'
                + '<div class="cp-info">'
                + '<div class="cp-name">' + self.escapeHtml(p.name) + '</div>'
                + '<div class="cp-price">$' + parseFloat(p.price).toFixed(2) + '</div>'
                + '</div>'
                + '<button class="cp-btn" onclick="event.preventDefault(); event.stopPropagation(); chatbot.addToCart(' + p.id + ')">Agregar</button>'
                + '</a>';
        }).join('');
        panel.style.display = 'flex';
    },

    closeProducts() {
        document.getElementById('chat-products').style.display = 'none';
    },

    async addToCart(productId) {
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 }),
            });
            const data = await response.json();
            if (data.success) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) cartCount.textContent = data.cart_count;
                this.appendMessage('bot', 'Producto agregado al carrito. Puedes continuar comprando o ir al carrito para finalizar la compra.', false, null, true);
            } else {
                this.appendMessage('bot', 'No se pudo agregar el producto. Intenta desde la página del producto.', false, null, true);
            }
        } catch (e) {
            this.appendMessage('bot', 'Hubo un error al agregar al carrito. Intenta de nuevo.', false, null, true);
        }
    },

    formatMarkdown(text) {
        return text
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="chat-product-link">$1</a>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    },

    escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

/* ── Borrar historial al CERRAR la pestaña (no al recargar) ── */
window.addEventListener('pagehide', function() {
    sessionStorage.removeItem('gsw_chat_history');
    try {
        navigator.sendBeacon('{{ route("chat.clear") }}');
    } catch (e) {}
});

/* ── Enter para enviar ── */
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('chat-input');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chat-form').dispatchEvent(new Event('submit'));
            }
        });
    }
});
</script>
@endpush
