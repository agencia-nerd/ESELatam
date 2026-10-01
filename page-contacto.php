<?php
/**
 * Template Name: Contacto
 *
 * Página "Contacto" (Figma node 3941-8369). WordPress la aplica sola a la
 * página con slug `contacto` (page-{slug}.php) — que el theme crea una vez
 * desde inc/contacto.php — y, por el header de arriba, también se puede
 * asignar a mano desde el editor a cualquier otra página.
 *
 * Composición, en orden del Figma:
 *   1. Titular + formulario de asesoría + información de contacto
 *   2. Sede central (dirección + mapa embebido)
 *
 * Las preguntas frecuentes ya no van acá: tienen su propia página
 * (page-preguntas-frecuentes.php).
 *
 * Cada bloque es un template-part autocontenido, igual que en el resto del
 * theme: resuelve sus propios datos y se puede reordenar o quitar sin
 * arrastrar variables de otro archivo. A diferencia de las demás páginas,
 * esta NO cierra con template-parts/contacto.php (el CTA de "Contactemos"):
 * sería mandar al usuario a contactar desde la propia página de contacto.
 *
 * @package EseLatam
 */
declare(strict_types=1);

get_header();
?>

<div class="contacto-page" data-contacto-page>
    <?php
    get_template_part('template-parts/contacto-form');
    get_template_part('template-parts/contacto-sede');
    ?>
</div>

<?php
get_footer();
