<?php $__env->startSection('title', 'Contacto | MD Technology Cell'); ?>
<?php $__env->startSection('body_class', 'modulo-contacto'); ?>

<?php $__env->startSection('content'); ?>
    <section class="contacto-banner">
        <div class="container contacto-banner-grid">
            <div>
                <span class="contacto-etiqueta"><i class="bi bi-chat-dots"></i> Estamos para ayudarte</span>
                <h1>Hablemos de tu <span>próximo equipo</span></h1>
                <p>Escríbenos tu consulta o visítanos en nuestra tienda de Bagua. Te ayudamos a elegir el celular o accesorio ideal, con productos originales y atención cercana.</p>
                <div class="contacto-botones"><a class="btn btn-primary" href="#formulario"><i class="bi bi-send"></i> Enviar consulta</a><a class="btn btn-ghost" href="#mapa"><i class="bi bi-geo-alt"></i> Cómo llegar</a></div>
                <ul class="contacto-ventajas"><li><i class="bi bi-patch-check-fill"></i> Productos originales</li><li><i class="bi bi-person-heart"></i> Asesoría personalizada</li><li><i class="bi bi-shop"></i> Atención presencial</li></ul>
            </div>
            <div class="contacto-foto"><img src="<?php echo e(asset('assets/cell-legacy/img/publico/nosotros/local.jpg')); ?>" alt="Fachada de la tienda MD Technology Digital Cell"></div>
        </div>
    </section>

    <section class="container contacto-datos">
        <article><i class="bi bi-geo-alt"></i><div><h2>Dirección</h2><p>Jr. Amazonas 563, Bagua</p></div></article>
        <article><i class="bi bi-signpost-2"></i><div><h2>Referencia</h2><p>Cerca de Plásticos Jireh</p></div></article>
        <article><i class="bi bi-calendar-week"></i><div><h2>Horario</h2><p>Lunes a sábado, horario comercial</p></div></article>
        <article><i class="bi bi-envelope-paper"></i><div><h2>Consultas web</h2><p>Déjanos tu mensaje y te respondemos</p></div></article>
    </section>

    <section class="container contacto-seccion" id="formulario">
        <div class="contacto-formulario">
            <div class="contacto-formulario-titulo"><i class="bi bi-chat-square-text"></i><div><h2>Envíanos tu consulta</h2><p>Cuéntanos qué producto buscas o en qué te podemos ayudar.</p></div></div>
            <form method="post" action="<?php echo e(route('pages.contact.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="contacto-dos-campos">
                    <div class="form-group">
                        <label for="contact-name">Nombre</label>
                        <div class="contacto-campo">
                            <i class="bi bi-person"></i>
                            <input
                                id="contact-name"
                                name="nombre"
                                class="input"
                                value="<?php echo e(old('nombre', auth()->user()?->nombre)); ?>"
                                required
                                maxlength="120"
                                placeholder="Tu nombre completo"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact-email">Correo o teléfono</label>
                        <div class="contacto-campo">
                            <i class="bi bi-envelope"></i>
                            <input
                                id="contact-email"
                                name="contacto"
                                class="input"
                                value="<?php echo e(old('contacto', auth()->user()?->correo)); ?>"
                                required
                                maxlength="160"
                                placeholder="ejemplo@correo.com o 9XX XXX XXX"
                            >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact-message">Mensaje</label>
                    <div class="contacto-campo">
                        <i class="bi bi-chat-left-text"></i>
                        <textarea
                            id="contact-message"
                            name="mensaje"
                            rows="5"
                            required
                            maxlength="2000"
                            placeholder="Ej.: ¿Tienen disponible el Samsung Galaxy A55 en color azul?"
                        ><?php echo e(old('mensaje')); ?></textarea>
                    </div>
                </div>

                <div class="contacto-enviar">
                    <small>
                        <i class="bi bi-shield-lock"></i>
                        Tus datos solo se usan para responder tu consulta.
                    </small>
                    <button type="submit" class="btn btn-primary">
                        Enviar consulta
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="contacto-mapa" id="mapa">
            <div class="contacto-mapa-dibujo"><div class="contacto-pin"><i class="bi bi-geo-alt-fill"></i><span>MD Technology Cell</span></div></div>
            <div class="contacto-mapa-pie"><div><b>MD Technology Digital Cell</b><small>Jr. Amazonas 563, Bagua</small></div><a class="btn btn-primary" href="https://www.google.com/maps/search/?api=1&amp;query=Bagua%2C%20Amazonas" target="_blank" rel="noopener noreferrer"><i class="bi bi-sign-turn-right"></i> Ver en Google Maps</a></div>
        </div>
    </section>

    <section class="container contacto-tienda"><span class="contacto-etiqueta"><i class="bi bi-camera"></i> Nuestra tienda</span><h2>Ven y prueba los equipos en persona</h2><p>Exhibimos celulares, audífonos y accesorios de las marcas que más te gustan.</p><div class="contacto-galeria"><?php for ($i = 1; $i <= 4; $i++) { ?><div><img src="<?php echo e(asset('assets/cell-legacy/img/publico/inicio/banners/exhibicion'.$i.'.jpg')); ?>" alt="Exhibición de la tienda <?php echo e($i); ?>"></div><?php } ?></div></section>

    <section class="container contacto-final"><div><h2>¿Ya sabes qué equipo quieres?</h2><p>Revisa precios, características y stock disponible en nuestro catálogo antes de visitarnos.</p><a href="<?php echo e(route('catalog.index')); ?>">Ver catálogo <i class="bi bi-arrow-right"></i></a></div><img src="<?php echo e(asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png')); ?>" alt="Celulares y accesorios"></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>