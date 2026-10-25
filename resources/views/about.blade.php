@extends('layouts.app')

@section('title', 'Sobre Nosotros — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">

        <div class="section-header">
            <h1 class="section-title">Sobre Nosotros</h1>
            <p class="section-subtitle">Conoce más sobre GSWStore y nuestro compromiso contigo</p>
        </div>

        {{-- Historia --}}
        <div class="about-block">
            <h2 class="about-block-title">Nuestra Historia</h2>
            <p>GSWStore nació con la visión de transformar la experiencia de compra en línea en El Salvador. Somos una tienda en línea comprometida con ofrecer productos de calidad a precios accesibles, con un enfoque en la satisfacción del cliente y la innovación tecnológica.</p>
            <p>Fundada en 2026, nuestra misión es ser la tienda en línea de referencia en El Salvador, ofreciendo una experiencia de compra intuitiva, segura y personalizada gracias a la inteligencia artificial.</p>
        </div>

        {{-- Estadísticas --}}
        <div class="stats-grid" style="margin-bottom: var(--space-2xl);">
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="grid" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">5</span>
                    <span class="stat-label">Categorías</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="box" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">100+</span>
                    <span class="stat-label">Productos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="sparkles" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">RAG</span>
                    <span class="stat-label">Asistente IA</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><x-icon name="truck" :size="22" /></div>
                <div class="stat-info">
                    <span class="stat-value">24/7</span>
                    <span class="stat-label">Disponibilidad</span>
                </div>
            </div>
        </div>

        {{-- Valores --}}
        <div class="about-block">
            <h2 class="about-block-title">Nuestros Valores</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-icon"><x-icon name="users" :size="22" /></div>
                    <h3>Enfoque en el Cliente</h3>
                    <p>Tu satisfacción es nuestra prioridad. Escuchamos y mejoramos constantemente.</p>
                </div>
                <div class="admin-card">
                    <div class="admin-card-icon"><x-icon name="sparkles" :size="22" /></div>
                    <h3>Innovación IA</h3>
                    <p>Usamos inteligencia artificial para ofrecerte una experiencia de compra personalizada.</p>
                </div>
                <div class="admin-card">
                    <div class="admin-card-icon"><x-icon name="shield" :size="22" /></div>
                    <h3>Seguridad</h3>
                    <p>Tus datos están protegidos con los más altos estándares de seguridad.</p>
                </div>
                <div class="admin-card">
                    <div class="admin-card-icon"><x-icon name="truck" :size="22" /></div>
                    <h3>Envíos Rápidos</h3>
                    <p>Entregamos tus pedidos rápidamente a todo El Salvador.</p>
                </div>
            </div>
        </div>

        {{-- Tecnología --}}
        <div class="about-block">
            <h2 class="about-block-title">Tecnología</h2>
            <div class="tech-stack">
                <div class="tech-item">
                    <span class="tech-icon"><x-icon name="box" :size="20" /></span>
                    <div>
                        <strong>Laravel 11</strong>
                        <span>Framework PHP moderno con arquitectura MVC</span>
                    </div>
                </div>
                <div class="tech-item">
                    <span class="tech-icon"><x-icon name="grid" :size="20" /></span>
                    <div>
                        <strong>PostgreSQL + pgvector</strong>
                        <span>Base de datos relacional con soporte para vectores</span>
                    </div>
                </div>
                <div class="tech-item">
                    <span class="tech-icon"><x-icon name="sparkles" :size="20" /></span>
                    <div>
                        <strong>Groq API + LLM</strong>
                        <span>Modelo de lenguaje para el chatbot RAG</span>
                    </div>
                </div>
                <div class="tech-item">
                    <span class="tech-icon"><x-icon name="search" :size="20" /></span>
                    <div>
                        <strong>Sentence-Transformers</strong>
                        <span>Embeddings semánticos para búsqueda por significado</span>
                    </div>
                </div>
                <div class="tech-item">
                    <span class="tech-icon"><x-icon name="shield" :size="20" /></span>
                    <div>
                        <strong>Nginx + PHP-FPM</strong>
                        <span>Servidor web de alto rendimiento en Debian 12</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Equipo --}}
        <div class="about-block">
            <h2 class="about-block-title">Equipo</h2>
            <div class="team-grid">
                <div class="team-member">
                    <span class="team-avatar">AF</span>
                    <strong>Ángel Fernández</strong>
                    <span>Desarrollo Backend</span>
                </div>
                <div class="team-member">
                    <span class="team-avatar">RR</span>
                    <strong>Ricardo Retana</strong>
                    <span>Desarrollo Backend</span>
                </div>
                <div class="team-member">
                    <span class="team-avatar">KU</span>
                    <strong>Kevin Umaña</strong>
                    <span>Desarrollo Frontend</span>
                </div>
                <div class="team-member">
                    <span class="team-avatar">DC</span>
                    <strong>Diego Catalán</strong>
                    <span>Infraestructura y DevOps</span>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="about-block">
            <h2 class="about-block-title">Contacto</h2>
            <div class="auth-card" style="max-width: 480px;">
                <div class="form-group">
                    <span class="about-contact-line"><x-icon name="mail" :size="18" /> info@gswstore.com</span>
                </div>
                <div class="form-group">
                    <span class="about-contact-line"><x-icon name="phone" :size="18" /> +503 2222-1234</span>
                </div>
                <div class="form-group">
                    <span class="about-contact-line"><x-icon name="pin" :size="18" /> San Salvador, El Salvador</span>
                </div>
                <div class="social-links">
                    <a href="#" class="btn btn-outline btn-sm">Facebook</a>
                    <a href="#" class="btn btn-outline btn-sm">Instagram</a>
                    <a href="#" class="btn btn-outline btn-sm">WhatsApp</a>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
