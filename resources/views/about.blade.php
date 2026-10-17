@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Sobre Nosotros</h1>
            <p class="section-subtitle">Conoce más sobre GSWStore y nuestro compromiso contigo</p>
        </div>

        <div class="about-grid">
            <div class="about-text">
                <h2>Nuestra Historia</h2>
                <p>GSWStore nació con la visión de transformar la experiencia de compra en línea en Costa Rica. Somos una tienda en línea comprometida con ofrecer productos de calidad a precios accesibles, con un enfoque en la satisfacción del cliente y la innovación tecnológica.</p>
                <p>Fundada en 2026, nuestra misión es ser la tienda en línea de referencia en Costa Rica, ofreciendo una experiencia de compra intuitiva, segura y personalizada gracias a la inteligencia artificial.</p>
            </div>
            <div class="about-stats">
                <div class="stat-card">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Categorías</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Productos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-label">Asistente IA</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🚚</div>
                    <div class="stat-label">Envíos Rápidos</div>
                </div>
            </div>
        </div>

        <div class="about-values">
            <h2>Nuestros Valores</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">🎯</div>
                    <h3>Enfoque en el Cliente</h3>
                    <p>Tu satisfacción es nuestra prioridad. Escuchamos y mejoramos constantemente.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🤖</div>
                    <h3>Innovación IA</h3>
                    <p>Usamos inteligencia artificial para ofrecerte una experiencia de compra personalizada.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🔒</div>
                    <h3>Seguridad</h3>
                    <p>Tus datos están protegidos con los más altos estándares de seguridad.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">🚚</div>
                    <h3>Envíos Rápidos</h3>
                    <p>Entregamos tus pedidos rápidamente a toda Costa Rica.</p>
                </div>
            </div>
        </div>

        <div class="about-contact">
            <div class="contact-card">
                <h2>Contáctanos</h2>
                <p><x-icon name="mail" :size="20" /> info@gswstore.com</p>
                <p><x-icon name="phone" :size="20" /></span> +506 2222-1234</p>
                <p><x-icon name="map-pin" :size="20" /> San José, Costa Rica</p>
                <div class="social-links">
                    <a href="#" class="social-link">Facebook</a>
                    <a href="#" class="social-link">Instagram</a>
                    <a href="#" class="social-link">WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
