@extends('layouts.app')

@section('title', 'Sobre Nosotros — ' . config('app.name'))

@section('content')
<section class="section">
    <div class="container">

        <div class="about-hero">
            <h1>Sobre GSWStore</h1>
            <p class="about-subtitle">Un proyecto académico de Gestión de Servidores Web que demuestra lo que se puede lograr con tecnología moderna.</p>
        </div>

        <div class="about-content">
            <div class="about-card">
                <h2><span class="about-icon"><x-icon name="compass" :size="22" /></span> El Proyecto</h2>
                <p>GSWStore es una plataforma de comercio electrónico desarrollada como proyecto final de la clase de <strong>Gestión de Servidores Web</strong>. Integra inteligencia artificial mediante un chatbot RAG (Retrieval-Augmented Generation) que permite encontrar productos de forma conversacional.</p>
            </div>

            <div class="about-card">
                <h2><span class="about-icon"><x-icon name="cpu" :size="22" /></span> Tecnología</h2>
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

            <div class="about-card">
                <h2><span class="about-icon"><x-icon name="check" :size="22" /></span> Características</h2>
                <ul class="about-features">
                    <li><span class="about-icon"><x-icon name="bag" :size="18" /></span> Carrito de compras con cálculo de IVA</li>
                    <li><span class="about-icon"><x-icon name="chat" :size="18" /></span> Chatbot RAG con IA que responde cualquier pregunta y redirige a productos</li>
                    <li><span class="about-icon"><x-icon name="search" :size="18" /></span> Búsqueda semántica que entiende consultas en lenguaje natural</li>
                    <li><span class="about-icon"><x-icon name="star" :size="18" /></span> Sistema de reseñas y calificaciones</li>
                    <li><span class="about-icon"><x-icon name="user" :size="18" /></span> Autenticación de usuarios con roles (admin/cliente)</li>
                    <li><span class="about-icon"><x-icon name="grid" :size="18" /></span> Panel de administración con CRUD de productos</li>
                    <li><span class="about-icon"><x-icon name="check" :size="18" /></span> Diseño responsive claro y consistente</li>
                </ul>
            </div>

            <div class="about-card">
                <h2><span class="about-icon"><x-icon name="users" :size="22" /></span> Equipo</h2>
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

            <div class="about-card">
                <h2><span class="about-icon"><x-icon name="sparkles" :size="22" /></span> Arquitectura RAG</h2>
                <p>El chatbot utiliza <strong>Retrieval-Augmented Generation</strong>:</p>
                <ol class="rag-steps">
                    <li><strong>Retrieval</strong> — Busca productos relevantes en la base de datos</li>
                    <li><strong>Context</strong> — Inyecta el catálogo como contexto al modelo de lenguaje</li>
                    <li><strong>Generation</strong> — La IA genera una respuesta natural con links a productos</li>
                </ol>
                <p>Esto permite que el asistente maneje cualquier pregunta, respondiendo siempre de forma directa y redirigiendo a productos de la tienda.</p>
            </div>
        </div>
    </div>
</section>
@endsection
