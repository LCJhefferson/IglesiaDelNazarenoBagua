<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artículos de Fe - Iglesia del Nazareno</title>
    
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS GLOBAL Y DE NAVEGACIÓN -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/globalPublico.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/nav.css">
    
    <!-- CSS ESPECÍFICOS -->
    <link rel="stylesheet" href="<?= URL ?>public/web/css/articulos-fe.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/cards_conocenos.css">
    <link rel="stylesheet" href="<?= URL ?>public/web/css/footer.css">
</head>
<body class="pagina-conocenos">

    <?php include 'componentes/nav.php'; ?>

    <main class="fe-main-wrapper">
        
        <!-- HERO PRINCIPAL -->
        <header class="fe-hero">
            <h1>Artículos de <em>Fe</em></h1>
            <p class="fe-hero-lead">
                Con el fin de preservar nuestra herencia recibida de Dios y la fe entregada a los santos, adoptamos y establecemos los siguientes 16 Artículos de Fe de la Iglesia del Nazareno.
            </p>

            <!-- Banner para Descargar PDF Integrado en Hero -->
            <div class="fe-download-banner">
                <div class="banner-info">
                    <!-- BADGE PERSONALIZADO DE PDF -->
                    <div class="custom-pdf-badge">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>PDF</span>
                    </div>
                    <div>
                        <strong>Documento Oficial Completo</strong>
                        <span>Obtén la versión descargable en PDF de los Artículos de Fe y el Manual de la Iglesia.</span>
                    </div>
                </div>
                <div class="banner-actions">
                    <a href="#" class="btn-primary"><i class="fa-solid fa-download"></i> Descargar (Español)</a>
                    <a href="#" class="btn-secondary-link">Traducciones <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </header>

        <!-- FILTROS POR CATEGORÍA (Navegación Rápida) -->
        <nav class="fe-filter-nav">
            <button class="filter-btn active" data-filter="all">Todos (16)</button>
            <button class="filter-btn" data-filter="trinidad">La Divinidad</button>
            <button class="filter-btn" data-filter="salvacion">La Salvación</button>
            <button class="filter-btn" data-filter="iglesia">La Iglesia</button>
            <button class="filter-btn" data-filter="futuro">Eventos Futuros</button>
        </nav>

        <!-- GRID DE ARTÍCULOS DE FE -->
        <section class="fe-grid-container">

            <!-- 1. El Dios Trino -->
            <article class="fe-card" data-category="trinidad">
                <div class="fe-card-number">01</div>
                <div class="fe-card-body">
                    <h2>El Dios Trino</h2>
                    <p>Creemos en un solo Dios eternamente existente e infinito, Creador y Sustentador soberano del universo; que solo Él es Dios, santo en su naturaleza, atributos y propósito. El Dios que es amor santo y luz es trino en su ser esencial, y se revela como Padre, Hijo y Espíritu Santo.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Génesis 1; Levítico 19:2; Deuteronomio 6:4-5; Juan 14:6-27; 2 Corintios 13:14)</span>
                </div>
            </article>

            <!-- 2. Jesucristo -->
            <article class="fe-card" data-category="trinidad">
                <div class="fe-card-number">02</div>
                <div class="fe-card-body">
                    <h2>Jesucristo</h2>
                    <p>Creemos en Jesucristo, la Segunda Persona de la Divina Trinidad; que Él es eternamente uno con el Padre; que se encarnó por obra del Espíritu Santo y nació de la Virgen María, de manera que dos naturalezas enteras y perfectas fueron unidas en una sola Persona, verdadero Dios y verdadero Hombre.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Mateo 1:20-25; Juan 1:1-18; Filipenses 2:5-11; Hebreos 1:1-5)</span>
                </div>
            </article>

            <!-- 3. El Espíritu Santo -->
            <article class="fe-card" data-category="trinidad">
                <div class="fe-card-number">03</div>
                <div class="fe-card-body">
                    <h2>El Espíritu Santo</h2>
                    <p>Creemos en el Espíritu Santo, la tercera Persona de la Divina Trinidad, que está siempre presente y eficazmente activo en la Iglesia de Cristo, convenciendo al mundo de pecado, regenerando a los que se arrepienten y santificando a los creyentes.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Juan 14:15-18; Hechos 2:33; Romanos 8:1-27)</span>
                </div>
            </article>

            <!-- 4. Las Santas Escrituras -->
            <article class="fe-card" data-category="trinidad">
                <div class="fe-card-number">04</div>
                <div class="fe-card-body">
                    <h2>Las Santas Escrituras</h2>
                    <p>Creemos en la inspiración inerrante de las Santas Escrituras, compuestas por los 66 libros del Antiguo y Nuevo Testamento, dadas por inspiración divina, las cuales revelan inefablemente la voluntad de Dios respecto a nuestra salvación.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(2 Timoteo 3:15-17; 2 Pedro 1:20-21; Salmos 119:105)</span>
                </div>
            </article>

            <!-- 5. El Pecado, Original y Personal -->
            <article class="fe-card" data-category="salvacion">
                <div class="fe-card-number">05</div>
                <div class="fe-card-body">
                    <h2>El Pecado, Original y Personal</h2>
                    <p>Creemos que el pecado original o depravación es aquella corrupción de la naturaleza de todos los descendientes de Adán. El pecado personal es una violación voluntaria de la ley conocida de Dios por parte de una persona con capacidad moral.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Génesis 3; Romanos 3:23; 5:12; 1 Juan 1:8-10)</span>
                </div>
            </article>

            <!-- 6. La Expiación -->
            <article class="fe-card" data-category="salvacion">
                <div class="fe-card-number">06</div>
                <div class="fe-card-body">
                    <h2>La Expiación</h2>
                    <p>Creemos que Jesucristo, por medio de sus sufrimientos, por el derramamiento de su preciosa sangre y por su muerte en la cruz, hizo una expiación plena por todo el pecado humano, y que esta expiación es la única base de la salvación.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Isaías 53:5-6; Juan 3:16; Romanos 5:8-11; 1 Juan 2:2)</span>
                </div>
            </article>

            <!-- 7. La Gracia Preveniente -->
            <article class="fe-card" data-category="salvacion">
                <div class="fe-card-number">07</div>
                <div class="fe-card-body">
                    <h2>La Gracia Preveniente</h2>
                    <p>Creemos que la creación humana cayó por el pecado, quedando incapacitada para volverse a Dios por sus propias fuerzas. Sin embargo, la gracia de Dios por medio de Jesucristo es libremente otorgada a todas las personas para que respondan al llamado del Evangelio.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Efesios 2:8-9; Tito 2:11; Juan 1:9)</span>
                </div>
            </article>

            <!-- 8. El Arrepentimiento -->
            <article class="fe-card" data-category="salvacion">
                <div class="fe-card-number">08</div>
                <div class="fe-card-body">
                    <h2>El Arrepentimiento</h2>
                    <p>Creemos que el arrepentimiento es un cambio sincero y piadoso de la mente respecto al pecado, con un reconocimiento del mismo y la decisión voluntaria de abandonarlo, siendo un requisito indispensable para recibir el perdón.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(2 Crónicas 7:14; Marcos 1:15; Hechos 17:30)</span>
                </div>
            </article>

            <!-- 9. Justificación, Regeneración y Adopción -->
            <article class="fe-card" data-category="salvacion">
                <div class="fe-card-number">09</div>
                <div class="fe-card-body">
                    <h2>Justificación, Regeneración y Adopción</h2>
                    <p>Creemos que la justificación es aquel acto judicial de Dios por el cual Él perdona nuestros pecados. La regeneración es el nuevo nacimiento espiritual, y la adopción es el acto por el cual somos constituidos en hijos de Dios.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Romanos 5:1; 2 Corintios 5:17; Gálatas 4:4-7; Juan 1:12-13)</span>
                </div>
            </article>

            <!-- 10. La Entera Santificación -->
            <article class="fe-card highlight-card" data-category="salvacion">
                <div class="fe-card-number">10</div>
                <div class="fe-card-body">
                    <span class="card-badge">Doctrina Distintiva</span>
                    <h2>La Entera Santificación</h2>
                    <p>Creemos que la entera santificación es aquel acto del Señor por el cual los creyentes son hechos libres del pecado original y llevados a un estado de entera devoción a Dios y de santa obediencia del amor perfeccionado. Es efectuada por la llenura del Espíritu Santo.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(1 Tesalonicenses 5:23; Romanos 12:1-2; Hebreos 10:14)</span>
                </div>
            </article>

            <!-- 11. La Iglesia -->
            <article class="fe-card" data-category="iglesia">
                <div class="fe-card-number">11</div>
                <div class="fe-card-body">
                    <h2>La Iglesia</h2>
                    <p>Creemos en la Iglesia, la comunidad que confiesa a Jesucristo como Señor; el cuerpo de Cristo llamado a ser uno mediante la gracia de Dios. Su misión principal es la evangelización del mundo y el discipulado de las naciones.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Mateo 16:18; Efesios 1:22-23; 5:25-27; 1 Pedro 2:9)</span>
                </div>
            </article>

            <!-- 12. El Bautismo -->
            <article class="fe-card" data-category="iglesia">
                <div class="fe-card-number">12</div>
                <div class="fe-card-body">
                    <h2>El Bautismo</h2>
                    <p>Creemos que el bautismo con agua es un sacramento que significa la aceptación de los beneficios de la expiación de Jesucristo. Es una declaración pública de la fe del creyente y su identificación con la muerte y resurrección de Cristo.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Mateo 28:19; Hechos 2:38; Romanos 6:3-4)</span>
                </div>
            </article>

            <!-- 13. La Santa Cena -->
            <article class="fe-card" data-category="iglesia">
                <div class="fe-card-number">13</div>
                <div class="fe-card-body">
                    <h2>La Santa Cena</h2>
                    <p>Creemos que la Cena del Señor es un sacramento instituido por nuestro Señor Jesucristo, en el cual se conmemora su muerte y sufrimiento. Es un medio de gracia donde Cristo está espiritualmente presente entre su pueblo.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Lucas 22:19-20; 1 Corintios 11:23-26)</span>
                </div>
            </article>

            <!-- 14. La Sanidad Divina -->
            <article class="fe-card" data-category="iglesia">
                <div class="fe-card-number">14</div>
                <div class="fe-card-body">
                    <h2>La Sanidad Divina</h2>
                    <p>Creemos en la doctrina bíblica de la sanidad divina y animamos a nuestro pueblo a ofrecer la oración de fe por la sanidad de los enfermos. También reconocemos que Dios providencialmente imparte sabiduría médica.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Santiago 5:14-16; Mateo 8:16-17; Salmos 103:2-3)</span>
                </div>
            </article>

            <!-- 15. La Segunda Venida de Cristo -->
            <article class="fe-card" data-category="futuro">
                <div class="fe-card-number">15</div>
                <div class="fe-card-body">
                    <h2>La Segunda Venida de Cristo</h2>
                    <p>Creemos que el Señor Jesucristo vendrá otra vez; que los creyentes que estén vivos al tiempo de su venida serán arrebatados para recibir al Señor en el aire, y que Él reinará triunfalmente.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Juan 14:1-3; 1 Tesalonicenses 4:13-18; Apocalipsis 1:7)</span>
                </div>
            </article>

            <!-- 16. Resurrección, Juicio y Destino -->
            <article class="fe-card" data-category="futuro">
                <div class="fe-card-number">16</div>
                <div class="fe-card-body">
                    <h2>Resurrección, Juicio y Destino</h2>
                    <p>Creemos en la resurrección de los muertos, tanto de los justos como de los injustos, y en el juicio final donde cada persona recibirá la recompensa o el castigo conforme a sus hechos en esta vida terrenal.</p>
                    <button class="btn-toggle-read" type="button">Leer más <i class="fa-solid fa-chevron-down"></i></button>
                    <span class="citas-biblicas">(Mateo 25:31-46; Apocalipsis 20:11-15; 1 Corintios 15:51-58)</span>
                </div>
            </article>

        </section>

    </main>

    <!-- Sección de accesos directos reutilizables -->
    <?php include __DIR__ . '/cards_conocenos.php'; ?>
    <?php include __DIR__ . '/componentes/footer.php'; ?>

    <!-- Scripts de interactividad -->
    <script>
        // 1. Filtrado por categorías
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.getAttribute('data-filter');
                document.querySelectorAll('.fe-card').forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // 2. Expandir / Contraer párrafos
        document.querySelectorAll('.btn-toggle-read').forEach(button => {
            button.addEventListener('click', () => {
                const body = button.closest('.fe-card-body');
                body.classList.toggle('expanded');
                
                if (body.classList.contains('expanded')) {
                    button.innerHTML = 'Leer menos <i class="fa-solid fa-chevron-up"></i>';
                } else {
                    button.innerHTML = 'Leer más <i class="fa-solid fa-chevron-down"></i>';
                }
            });
        });
    </script>
</body>
</html>