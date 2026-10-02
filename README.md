# ESE Latam — WordPress Theme

Theme corporativo de ESE Latam, alineado 1:1 al design system del Figma (`ESE-LATAM-WEB`).

## Stack

- **WordPress** (theme clásico PHP 8.1+)
- **Vite 5** como build tool (`base: ''` relativo — requerido para que los chunks dinámicos resuelvan dentro de `/wp-content/themes/`)
- **TypeScript** en modo estricto
- **Tailwind CSS v4** con tokens ESE Latam en `@theme`
- **GSAP** (+ ScrollTrigger) para animaciones de scroll
- **Lenis** para smooth scroll inercial (sincronizado con el ticker de GSAP)
- **Embla Carousel** para sliders
- **Three.js** para 3D (carga perezosa)
- **Plus Jakarta Sans** como tipografía oficial

## Estructura

```
ese-latam/
├── style.css              # Header requerido por WordPress
├── functions.php          # Bootstrap del theme
├── index.php              # Fallback template
├── front-page.php         # Home: hero animado + sección Sectores (slider)
├── page-nosotros.php      # Página Nosotros (Figma 3441-370), ver abajo
├── page-contacto.php      # Página Contacto (Figma 3941-8369), ver abajo
├── page-sectores.php      # Página Soluciones por sector (Figma 3510-6982), ver abajo
├── page-sector.php        # Plantilla "Solución por sector" (Figma 3551-5049), ver abajo
├── page-certificaciones.php # Página Certificaciones (Figma 3785-4089), ver abajo
├── page-impacto.php       # Página Impacto / Residuos inteligentes (Figma 3824-3688), ver abajo
├── archive-caso.php       # Blog de Casos de éxito (/casos-de-exito/, Figma 3891-3677), ver abajo
├── page-distribuidores.php # Encuentra un distribuidor (/distribuidores/, Figma 3952-9273), ver abajo
├── header.php / footer.php
├── inc/
│   ├── setup.php          # Supports, menús (+ fallback del menú), sidebars
│   ├── paginas.php        # Crea las páginas base (/inicio, /nosotros, /contacto, /sectores, /certificaciones, /impacto, /distribuidores, /municipalidades), fija la portada y resuelve sus URLs
│   ├── pcf.php            # Puente PCF↔plantillas: lectores con valor por defecto y precedencia de las secciones compartidas
│   ├── pcf-home.php       # Campos de la portada (hero, marquee, titulares, productos destacados)
│   ├── pcf-globales.php   # Páginas de opciones "ESE Latam" + override por página de las secciones compartidas
│   ├── cpt-sectores.php   # Módulo `sector`: URLs /sectores/{slug}/ y redirección desde la ruta antigua
│   ├── pcf-sectores.php   # Campos de cada sector y de la página "Soluciones por sector"
│   ├── contenido.php      # Listas compartidas: certificaciones y servicios de Residuos inteligentes
│   ├── acf-productos.php  # Campos del CPT producto (ficha, litrajes, colores, características)
│   ├── cpt-casos.php      # CPT `caso` (+ taxonomías sector/ciudad), filtros del archivo y helpers de tarjeta
│   ├── distribuidores.php # Red de distribuidores: data compartida por el globo de la home y la página del buscador
│   ├── contacto.php       # Datos de contacto y envío del formulario
│   └── enqueue.php        # Integración con manifest de Vite
├── assets/
│   ├── icons/             # SVGs exportados del Figma (CTA, chevron, search)
│   └── imgs/              # Logo, nubes, fotos de sectores; hero/ = capas del hero de la home
├── src/
│   ├── css/main.css       # Tailwind v4 + tokens + componentes (hero, nav, slider…)
│   └── ts/
│       ├── main.ts        # Entry point
│       ├── lib/gsap.ts    # Setup central de GSAP
│       └── modules/
│           ├── hero-scroll.ts    # Coreografía del hero (intro + pin)
│           ├── smooth-scroll.ts  # Lenis + integración ScrollTrigger
│           ├── header.ts         # Header fijo: scrolled + menú móvil
│           ├── slider.ts         # Sliders Embla (flechas, dots, contador)
│           ├── scroll-reveals.ts # Reveals genéricos por data-attributes
│           ├── nosotros.ts       # Coreografía de la página Nosotros (lazy)
│           ├── contacto-page.ts  # FAQ + envío del formulario (lazy)
│           ├── tab-panels.ts     # Tabs genéricas [data-tabs] (autoplay, prev/next)
│           ├── count-up.ts       # Contadores [data-count-to]
│           └── three-scene.ts
├── vite.config.ts
├── tsconfig.json
└── package.json
```

## Instalación

```bash
cd wp-content/themes/ese-latam
npm install
```

## Desarrollo

1. Crear archivo marcador `.vite-dev` en la raíz del theme:
   ```bash
   touch .vite-dev
   ```
2. Correr Vite:
   ```bash
   npm run dev
   ```
3. Levantar WordPress. Los assets se sirven desde `http://localhost:5173` con HMR.

## Producción

```bash
rm -f .vite-dev
npm run build
```

Vite genera `dist/.vite/manifest.json` y `enqueue.php` encola los archivos con hash.

> ⚠️ **No agregar `?ver=` a los assets de Vite** (`enqueue.php` pasa `null` como
> versión a propósito). Un query string haría que los chunks dinámicos importen
> el módulo `main` con otra URL y se ejecute dos veces, rompiendo ScrollTrigger.

---

## ✍️ Contenido editable (PCF / campos personalizados)

El sitio usa **Prompt Custom Fields** (plugin `prompt-custom-fields`), que
expone la misma API que ACF PRO (`get_field`, `have_rows`, `the_row`,
`acf_add_local_field_group`). Todos los grupos se registran **por PHP** en
`inc/`, nunca a mano en el admin: así viajan versionados con el theme y no
hay que exportar/importar nada al desplegar.

> Si algún día se activa ACF PRO en vez de PCF, todo sigue funcionando: el
> theme solo usa funciones que existen en ambos.

**Regla de oro: los campos son la única fuente del contenido.** El theme no
trae textos ni imágenes de respaldo: lo que no está cargado, no se pinta.
Cada elemento se envuelve en su propia comprobación y, cuando una sección se
queda sin lo que la justifica, no se imprime su `<section>`:

| Sección | Desaparece cuando… |
| --- | --- |
| Hero | no hay titular, bajada, botón ni tarjeta |
| Sectores (portada) | no hay sectores cargados |
| Marquee | no hay pistas |
| Productos | no hay productos publicados en el CPT |
| Distribuidores | no hay países cargados |
| Residuos inteligentes | no hay servicios cargados |
| Certificaciones | no hay sellos ni encabezado |
| Contactemos | no hay titular, bajada ni botón |
| Soluciones recomendadas | el producto no tiene otros con que compararse |

Lo mismo dentro de cada sección: sin antetítulo no hay `<p class="type-kicker">`,
sin foto no hay `<img>`, sin botón no hay enlace. Un sector sin imagen sale
como tarjeta de solo texto, y un país sin distribuidores muestra la ficha
"próximamente" que se redacta en la misma página de opciones (vacía, el país
aparece sin fichas).

**Qué sigue viviendo en el theme** (y por qué no es contenido):

- El arte decorativo de la portada: isla, nubes, hojas y el skyline en
  parallax. Son piezas de la composición, no información editable.
- Las etiquetas de interfaz: `aria-label` de las flechas, "Litraje" /
  "Material" de la ficha, "países conectados". Pertenecen al componente y se
  traducen por i18n, no por el CMS.
- El copy de cierre del archivo de Casos de éxito (`archive-caso.php`): un
  archivo no es una entidad editable, así que no tiene dónde guardar campos.
  Es el único texto de negocio que queda en código; si hace falta, se
  resuelve con una página de opciones propia.

Los assets del diseño (fotos de sectores, logos de sellos y de distribuidores,
íconos, fondo e isla del hero, fondos de los CTA) están **importados a la
biblioteca de medios**, así que también se cambian desde el admin. Los tres
íconos del selector de residuos se rasterizaron a PNG porque WordPress no
acepta SVG por defecto.

### Dónde se edita cada cosa

| Dónde | Qué contiene | Archivo |
| --- | --- | --- |
| **Páginas → Inicio** | Hero, marquee, titulares de cada sección y "Productos destacados" | `inc/pcf-home.php` |
| **Sectores** (módulo) | Un sector por entrada: resumen, foto, y todo el contenido de su página | `inc/cpt-sectores.php`, `inc/pcf-sectores.php` |
| **Páginas → Soluciones por sector** | Solo el hero y los pasos del proceso de esa pantalla | `inc/pcf-sectores.php` |
| **Páginas → Certificaciones** | Hero, qué sellos muestra, los cuatro argumentos, los paneles de calidad, el ciclo Blue Angel y la guía de validación | `inc/pcf-certificaciones.php` |
| **Páginas → Residuos inteligentes** | Hero, mosaico "decisión humana", franja de frases y encabezados de alianzas y casos | `inc/pcf-impacto.php` |
| **ESE Latam → Cabecera** | El botón del menú principal |
| **ESE Latam → Certificaciones** | Solo el encabezado de la franja | `inc/pcf-globales.php` |
| **Certificaciones** (módulo) | Un sello por entrada: logo, resumen, criterios, PDF | `inc/modulos.php` |
| **Distribuidores** (módulo) | Una empresa por entrada; el país es una taxonomía con sus coordenadas | `inc/modulos.php` |
| **Aliados**, **Preguntas frecuentes** (módulos) | Logos de socios y las FAQ de Contacto | `inc/modulos.php` |
| **ESE Latam → Distribuidores** | Países (repetidor) con sus empresas (repetidor anidado) | `inc/pcf-globales.php` |
| **ESE Latam → Pruebas de rigurosidad** | El bloque "¿Qué hace que sea excelente?", compartido por cada ficha de producto y Certificaciones | `inc/pcf-globales.php` |
| **ESE Latam → Residuos inteligentes** | Encabezado + servicios (repetidor) | `inc/pcf-globales.php` |
| **ESE Latam → Contactemos** | El bloque de cierre por defecto | `inc/pcf-globales.php` |
| **Cualquier página o producto → "Secciones compartidas"** | Override del copy de Contactemos / Certificaciones / Residuos / Pruebas de rigurosidad solo en esa página | `inc/pcf-globales.php` |

La portada es una **página estática** (`inicio`) fijada en Ajustes → Lectura
por `ese_latam_asegurar_portada()` (`inc/paginas.php`). Sin ella, la home no
sería ninguna página y no habría dónde guardar su contenido. El índice del
blog no se pierde: el listado editorial del sitio es el archivo de "Casos de
éxito" (`archive-caso.php`).

### Precedencia de las secciones compartidas

`template-parts/contacto.php`, `certificaciones.php` y `residuos.php` salen en
muchas plantillas y cada una las llamaba con su propio copy por `$args`. Eso
sigue valiendo; los campos se insertan alrededor, resueltos por
`ese_latam_seccion_args()` (`inc/pcf.php`), de mayor a menor prioridad:

1. Campos de override de la página actual (si su interruptor está activo).
2. `$args` de `get_template_part()` — el copy por página que vive en PHP.
3. Campos globales de la página de opciones.
4. Valores por defecto del theme.

Así, mientras nadie toque el interruptor, cada página conserva exactamente el
copy que tenía; al activarlo, lo que se escriba en el editor manda.

### Helpers de lectura (`inc/pcf.php`)

- `ese_latam_home($campo)` / `ese_latam_opcion($campo)` — leen un campo de la
  portada o de las páginas de opciones; vacío devuelve cadena vacía y la
  plantilla decide si imprime o no.
- `ese_latam_img_url($valor)` — resuelve una imagen venga como ID, array de
  adjunto o URL; devuelve cadena vacía si no hay nada.
- `ese_latam_enlace($valor, $label, $href)` — normaliza un campo `link`.
- `ese_latam_titulo($texto, $tag, $clase)` — los titulares a dos colores (ver
  abajo). `ese_latam_titulo_plano($texto)` da la versión sin barras ni saltos,
  para `aria-label` y demás atributos.
- `ese_latam_texto_rico($html)` — copy de WYSIWYG saneado y sin el `<p>`
  envolvente, para imprimirlo dentro del `<p class="…">` que ya trae el marcado.

### Botones (campo `link`)

Todos los botones editables del sitio usan el campo `link`, que guarda URL,
texto y destino en un solo valor: el del hero y el del catálogo en la portada,
el de cierre de Contactemos, el del hero de cada sector, el "Comprar" de la
ficha de producto y el del menú principal.

El campo de enlace de PCF abre el modal nativo de WordPress ("Insertar/editar
enlace", el mismo del editor): URL, texto del botón, casilla de pestaña nueva
y buscador del contenido publicado para autocompletar la URL. Se implementó en
el plugin (`prompt-custom-fields` 1.1.0), no en el theme.

El valor guardado sigue siendo `{url, title, target}`, así que
`ese_latam_enlace()` lo lee igual. Cuando el editor marca la pestaña nueva,
`ese_latam_target_attr()` imprime el `target` y su `rel="noopener"`: lo
aplican el CTA reutilizable (`ese_latam_cta_button()`) y los enlaces de
productos, distribuidores, certificaciones y residuos.

### Módulos de contenido

Cuatro listas que estaban escritas a mano son ahora entradas (`inc/modulos.php`,
campos en `inc/pcf-modulos.php`). Ninguna tiene página propia: son datos que se
muestran dentro de otras pantallas, así que van con `public => false`.

- **Certificaciones**: sustituyen al repetidor global y a la taxonomía
  `producto_certificacion`. Un sello se edita una vez y sale igual en la franja,
  en el hero de producto y en la página de Certificaciones. El interruptor
  "Mostrar en la franja de sellos" decide cuáles van en la versión corta.
- **Distribuidores**: una entrada por empresa. El país es la taxonomía `pais`,
  que guarda la latitud y la longitud que el globo 3D necesita para apuntar.
- **Aliados** y **Preguntas frecuentes**: logos de socios y las FAQ de Contacto.

Los cuatro se ordenan con el campo "Orden" del editor y se leen con
`ese_latam_modulo_entradas($tipo)`.

### Fotos de producto

La ficha tiene una pestaña **3. Fotos** con un repetidor de tres columnas:
color, capacidad y foto. Al elegir un color y una capacidad en el frontend se
busca aquí la combinación exacta; si no existe, se usa la **foto por defecto**
de ese color (pestaña 2), y si tampoco la hay, la imagen destacada.

Los dos desplegables se llenan solos con los colores y los litrajes del propio
producto, vía el filtro `pcf/prepare_field`, que alcanza también a los
subcampos de un repetidor. Por eso hay que **guardar** el producto después de
añadir un color o un litraje para verlos en las listas.

### Relaciones

La ficha de producto apunta a otros módulos en vez de repetir sus datos:

- **Certificaciones**: los sellos del hero. Reemplaza a la taxonomía, que ya no
  se registra.
- **Sectores donde se usa**: para qué sectores está pensado el producto.

Los campos de producto (`inc/pcf-productos.php`, antes `acf-productos.php`)
llevan además validaciones: descripción corta obligatoria y acotada, al menos un
litraje, tope de filas en cada repetidor, y los archivos limitados por tipo
(PDF en la ficha técnica, PNG o WebP en las fotos de color).

### Módulo Sectores

Cada sector es una entrada del CPT `sector` (`inc/cpt-sectores.php`), no una
página. Añadir un sector es publicar una entrada: el slider de la portada, el
submenú del nav, la grilla bento y su propia página aparecen solos.

- **URL**: `/sectores/{slug}/`, colgando de la página madre. Las rutas
  antiguas en la raíz (`/municipalidades/`) redirigen con un 301.
- **Orden**: el campo "Orden" del editor manda en los cuatro sitios. En la
  grilla bento las áreas se reparten `a`…`h` en ese mismo orden, así que la
  composición se recoloca arrastrando en el admin. A partir del noveno, las
  tarjetas caen en filas nuevas por flujo normal.
- **Página del sector**: `single-sector.php`. No tiene nada escrito; si un
  sector no trae desafíos o criterio, esas secciones no se pintan.
- **Reparto de campos**: lo que describe a un sector va en su ficha; lo que es
  de la pantalla "Soluciones por sector" (hero y pasos del proceso) va en esa
  página.

### Titulares a dos colores: `|así|`

Convención única en todo el theme. El tramo que va en el color de marca se
escribe **entre barras verticales**, y cada salto de línea del texto es un
salto de línea del titular:

```
sectores que
|transformamos|
```

```php
<h2 class="type-h2 uppercase">
    <?php echo ese_latam_titulo($copy['titulo'], 'span', 'hl'); ?>
</h2>
```

Un solo campo por titular: el cliente decide qué se resalta y dónde corta la
línea, sin tocar HTML y sin que la plantilla tenga que adivinar dónde va el
`<br>`. La **etiqueta y la clase** del tramo resaltado sí las fija cada sección
(`<strong>`, `<span class="hl">`, `<span class="certificaciones__title-accent">`),
porque eso es diseño y no contenido.

El texto se escapa antes de interpretar las barras, y la sustitución va por
callback, así que un `$` o una barra invertida del cliente no se
interpretan como referencia de grupo. Una barra suelta (sin cierre) se
imprime tal cual.

Aplica a los titulares de todas las secciones y `template-parts`, y a los
`$args` `title` / `heading` que reciben. Dos excepciones, ambas por motivos
técnicos:

- `producto-hero.php` — el titular se parte solo a partir del nombre del
  producto, y sus dos mitades son `display:block` independientes.
- `sectores-proceso.php` — `proceso-steps.ts` reescribe cada mitad al cambiar
  de paso, así que necesitan seguir siendo dos elementos con su data-attribute.

### Listas compartidas

`ese_latam_sectores()` (`inc/template-tags.php`, que lee el módulo `sector`),
`ese_latam_distribuidores()` (`inc/distribuidores.php`),
`ese_latam_certificaciones()` y `ese_latam_servicios_residuos()`
(`inc/contenido.php`) leen su repetidor global y devuelven una lista vacía
mientras nadie cargue nada. Devuelven las imágenes como **URL absoluta** (o
cadena vacía), así que las plantillas ya no anteponen ninguna ruta. Editar la
lista de sectores —en la pestaña "Sectores" de la página Inicio— cambia a la
vez el slider de la portada, el megamenú, la grilla de "Soluciones por
sector" y cada single de sector; editar la de
distribuidores cambia el globo 3D y la página del buscador — incluido el
contador de "países conectados", que sale de `count()`.

### Slider de productos

Se alimenta del CPT `producto` (los 12 más recientes). El campo de relación
**Productos destacados** fija cuáles salen y en qué orden
(`orderby => post__in`); vacío, vuelve al automático. Sin productos
publicados la sección no se pinta. Los campos de la ficha de producto siguen
en `inc/acf-productos.php`.

---

## 🏠 Home — coreografía del hero

`front-page.php` + `src/ts/modules/hero-scroll.ts`.

**Escena en capas** (sin video, por rendimiento): fondo `.hero__bg` → shade →
isla `.hero__island` → tres hojas `.hero__leaf--{tl,bl,br}` → contenido.

- El **fondo** se pinta en el primer frame (nunca `visibility:hidden`)
  y se precarga desde el `<head>` con el mismo srcset que el `<img>`
  (`ese_latam_hero_fondo()` en `inc/template-tags.php`, hook en
  `inc/enqueue.php`). Sale del campo "Imagen de fondo del hero"
  (`hero_video_poster`, conserva el nombre histórico) o, vacío, de
  `assets/imgs/hero/hero-fondo-{960,1600,2560}.webp`.
- La **isla** sale del campo `hero_isla` o, vacío, de
  `assets/imgs/hero/hero-isla-{800,1400}.webp` (recortada al dibujo, sin margen
  transparente). A la derecha en desktop; centrada en mobile con la propiedad
  `translate` para no chocar con los transforms de GSAP.
- Las **hojas** son decorado fijo (`assets/imgs/hero/hoja-*.webp`, ya
  desenfocadas en el archivo). Dos capas cada una: el wrapper recibe entrada y
  parallax, el `<img>` el vaivén continuo.

Para regenerar los WebP desde los PNG originales (ImageMagick):

```bash
magick fondo.png -resize 1600x -strip -quality 72 -define webp:method=6 hero-fondo-1600.webp
magick isla.png -trim +repage -resize 1400x -strip -quality 74 -define webp:method=6 hero-isla-1400.webp
```

**Intro al cargar** (timeline de entrada, no depende del scroll):

1. El fondo asienta desde un ligero zoom; las hojas entran desde sus esquinas;
   el titular aparece **centrado** con reveal de máscara por línea.
2. El titular viaja a su posición de layout (la distancia se mide del DOM, no
   hay valores mágicos) mientras emerge la isla (flotación continua en loop).
3. Entran el menú, el lede, el CTA y la card de stats, escalonados.

**Scroll** (hero pineado durante `PIN_VIEWPORTS` = 3.5 viewports en desktop,
2.4 en móvil):

- Titular, bloque inferior e isla derivan hacia abajo con parallax; el fondo se
  acerca levemente y las hojas se abren hacia sus esquinas.
- **Último viewport del pin**: la sección siguiente — coronada por las nubes
  (`.hero-next__clouds`) — barre el hero y llega al top exactamente al
  liberarse el pin.

Ojo con el LCP: Chrome no toma como candidata una imagen del tamaño del
viewport, así que el fondo no cuenta. En mobile el LCP es la isla — por eso
ahí entra casi de inmediato (`ISLAND_IN`) en vez de esperar al titular.

La sección que sigue al hero lleva la clase **`.hero-next`**: el módulo le
aplica un `margin-top` negativo igual a la altura real del hero (re-medido en
cada refresh) para el solape exacto. Sin JS o con
`prefers-reduced-motion` todo queda estático, visible y en flujo normal.

---

## 👥 Página Nosotros

`page-nosotros.php` — WordPress la aplica sola a la página con slug
`nosotros` (también se puede asignar como plantilla "Nosotros" desde el
editor). Estilos con prefijo `.nos-*` en `main.css`; animaciones en
`src/ts/modules/nosotros.ts`, que `main.ts` importa perezosamente cuando
existe `[data-nosotros]`. Assets propios en `assets/imgs/nosotros/`.

Secciones y sus efectos GSAP:

- **Hero**: titular por líneas (SplitText + máscara), zoom lento de la foto,
  contador del "13 países", halo que sigue al cursor, salida scrubbed.
- **Construimos**: paneles con cortina `[data-nos-panel="left|right|up"]`
  (clip-path + zoom de la foto), isla con entrada + parallax + flotación.
- **Objetivos**: aside sticky con scroll-spy (el chevron viaja al ítem
  activo), paneles con parallax interno y tilt 3D al mouse.
- **Aliados**: anillos que se expanden con el scroll, logos que entran en
  3D en cascada, isla con tilt que sigue al mouse.
- **Método Circulogic**: tabs ESG con autoplay (`data-nos-metodo-autoplay`,
  la barra verde es el temporizador) y crossfade de la foto.
- **HDPE**: escena de pellets en canvas 2D (llueven, se apilan, se
  reciclan; el cursor los aparta) y chips en cascada.
- **Contactemos**: `template-parts/contacto.php` acepta `$args`
  (`heading`, `heading_strong`, `desc`, `bg`, `class`, …) para pisar el copy.

---

## ✉️ Página Contacto

`page-contacto.php` — el theme crea la página con slug `contacto` una sola
vez (`inc/contacto.php`), así que la URL existe sin tocar wp-admin. Estilos
con prefijo `.ctc-*`; animaciones en `src/ts/modules/contacto-page.ts`.

Tres bloques, cada uno un template-part autocontenido:

- **Formulario de asesoría** (`contacto-form.php`): campos del Figma, con
  Sector y País como listas cerradas y Producto de interés poblado desde el
  CPT `producto`. Al costado, la tarjeta de marca y los cuatro datos de
  contacto.
- **Sede central** (`contacto-sede.php`): dirección + mapa de Google
  embebido (`output=embed`, sin API key).
- **Preguntas frecuentes** (`contacto-faq.php`): acordeón de `<details>`
  nativos; el JS solo anima la altura y deja una abierta a la vez.

### Envío del formulario

Sin plugin: `inc/contacto.php` registra `wp_ajax(_nopriv)_ese_latam_contacto`,
valida, sanitiza y despacha con `wp_mail` al `admin_email` (filtrable con
`ese_latam_contacto_destino`), con `Reply-To` de quien escribe. Trae honeypot
y nonce. Con JS el envío es por `fetch`; sin JS el `<form>` postea normal y
vuelve con `?contacto=ok|error`.

> ⚠️ En el JS **no** se puede usar `form.action`: el formulario tiene un
> campo oculto llamado `action` (lo exige admin-ajax) y el acceso por nombre
> a los controles pisa la propiedad nativa. Hay que leer el atributo con
> `form.getAttribute('action')`.

Los datos de contacto (teléfono, correo, dirección, horario) salen de
`ese_latam_contacto_datos()`, filtrable con `ese_latam_contacto_datos`.

---

## 🏭 Página Soluciones por sector

`page-sectores.php` — la página `sectores` la crea `inc/paginas.php` (mismo
mecanismo que Contacto; el menú y el footer apuntan a ella vía
`ese_latam_pagina_url('sectores')`). Reutiliza todo lo que ya existía:

- **Hero claro** (`sectores-hero.php`): breadcrumb `.nos-crumb` de Nosotros +
  titular centrado. Estilos `.sec-hero*`.
- **Grilla bento** (`sectores-grid.php`): los 8 sectores salen de
  `ese_latam_sectores()` (misma lista que el slider de la home y el submenú);
  la página solo aporta bajadas propias, el tamaño de cada tarjeta
  (`grid-template-areas`, ver `.sec-grid__list`) y una foto por tarjeta.
  Cada tarjeta enlaza a la single del sector si existe su página
  (`ese_latam_sector_url()`, hoy solo `/municipalidades/`) y al catálogo si no.
- **"Entendemos tu operación"** (`sectores-proceso.php` +
  `src/ts/modules/proceso-steps.ts`): tres pasos con autoplay y crossfade de
  texto y foto de fondo. Mismo vidrio que `.contacto__card`.
- **Certificaciones**: `template-parts/certificaciones.php` tal cual.
- **Contactemos**: `template-parts/contacto.php` con copy propio y la
  variante `contacto--upper`.

---

## 🧩 Páginas internas: Sector, Certificaciones e Impacto

Las tres comparten esqueleto: van dentro del wrapper `.nosotros[data-nosotros]`
(gutters, hero oscuro y `nosotros.ts` de la página Nosotros) y se arman con
partes reutilizables; lo nuevo del Figma vive en partes propias. Las páginas
`certificaciones`, `impacto` y `municipalidades` las crea `inc/paginas.php`
(esta última con la plantilla `page-sector.php` asignada); menú y footer ya
apuntan a ellas.

Partes compartidas (todas aceptan `$args` para el copy):

- `hero-interno.php`: el `.nos-hero` de Nosotros + kicker + CTA + breadcrumb
  con ruta. Lo anima `initHero` de `nosotros.ts`.
- `casos-reales.php`: 4 tarjetas de proyecto. Datos de ejemplo hasta que
  exista un CPT de casos.
- `aliados.php`, `objetivos-sticky.php`, `metodo-tabs.php`: extraídas de
  `page-nosotros.php` (que ahora las llama) para reutilizarlas con otro copy.
- `residuos.php`: la sección "Ingeniería de alto desempeño" extraída de la home.
- `certificaciones.php`: ahora acepta `title`, `title_accent` y `centered`.

Tabs genéricas: `src/ts/modules/tab-panels.ts` mueve cualquier
`[data-tabs]` con `[data-tab]` × N y `[data-tab-panel]` × N (más
`data-tabs-autoplay="ms"`, `[data-tabs-prev]`/`[data-tabs-next]`). Pone
`.is-active` en tab y panel, `.is-next` en el panel siguiente y anima
`--tab-progress` (0→1) en la tab activa durante el autoplay.

La ficha de producto (`producto-hero.php`) muestra ahora los sellos del
producto (taxonomía `producto_certificacion`) en la fila inferior del hero,
junto a la barra de atributos (`.producto-hero__certs`, Figma 3682-5745): logo
por slug del término desde `assets/imgs/certificaciones/`, iniciales si no hay
archivo, y el set del diseño como fallback cuando el producto no tiene
términos. Cada sello enlaza a la página de Certificaciones.

**Solución por sector** (`page-sector.php`, plantilla para N sectores; el
sector sale del slug de la página y el copy de `$ese_contenido`, hoy solo
Municipalidades): hero → **Desafíos** (`sector-desafios.php`: pills
Dolores/Alivio + bento de 6 tarjetas, `data-tabs`) → **Criterio de
adaptabilidad** (`sector-criterio.php`: cita + contenedor con callouts) →
marquee → **Recomendados** (`producto-recomendados.php`) → Certificaciones
centradas → **Economía circular** (`economia-circular.php`: anillo SVG del
Figma con 4 nodos-tab y leyenda, prev/next) → Casos → Contactemos.

**Certificaciones** (`page-certificaciones.php`): hero → **Certificaciones en
detalle** (`certificaciones-detalle.php`: 9 sellos-tab + ficha; solo Blue
Angel trae copy del Figma) → marquee → **Marcando la diferencia**
(`metodo-tabs.php` con 4 tabs) → **Pruebas** (`producto-pruebas.php`) →
**Calidad superior desde el origen** (`objetivos-sticky.php`) → **Blue Angel**
(`blue-angel.php`, carrusel en arco, ver abajo) → **Valida certificados**
(`valida-certificados.php`: mazo de tarjetas con autoplay, `data-tabs`) →
Casos → Contactemos.

**Impacto** (`page-impacto.php`): hero → **Decisión humana**
(`impacto-stats.php`: mosaico de tiles con contadores `data-count-to`) →
franja de frases (`impacto-ticker.php`, `data-marquee`) → `residuos.php` →
`aliados.php` → Casos → Contactemos.

Assets nuevos en `assets/imgs/{sectores,certificaciones,economia,impacto,casos}/`.

### Estándar Blue Angel: carrusel en arco

`template-parts/blue-angel.php` + `src/ts/modules/blue-angel.ts` (lo carga
`main.ts` cuando existe `[data-bangel]`). Replica el comportamiento de la
sección "Economía circular" del sitio anterior
(contenedoresdebasura-ese.com#economia-circular) con el diseño del Figma 3807-5473:

- Los pasos (`$ese_ba_pasos`, 6 hoy) se reparten sobre un arco concéntrico con
  la banda de progreso SVG; en desktop se ven 5, el del centro grande y activo,
  con su nombre debajo. En mobile (< 48rem) van 3 en fila, sin arco.
- Prev/next rotan el ciclo con wrap; clic o Enter en un paso lo trae al centro;
  deslizar en táctil también rota. Autoplay (`data-bangel-autoplay`, ms) solo
  con la sección en viewport, y se apaga para siempre a la primera interacción.
  Con `prefers-reduced-motion` no hay autoplay.
- La banda de progreso avanza con `stroke-dasharray` y el marcador "»" se
  coloca con `getPointAtLength` sobre el mismo path. Todo el movimiento es CSS
  (`transition` en `transform`/`opacity`); el JS solo escribe transforms.
- Sin JS los pasos quedan en fila, visibles (`.bangel__stage:not(.is-js)`).
- Los renders son los del Figma (`assets/imgs/certificaciones/blue-angel/`);
  el copy de los pasos es propuesto, solo "Fabricación de contenedores nuevos"
  viene del diseño.

---

## 📰 Blog: Casos de éxito

`archive-caso.php` — archivo del CPT `caso` (Figma 3891-3677), responde en
`/casos-de-exito/`. El CPT, sus taxonomías (`caso_sector`, `caso_ciudad`),
los filtros y los helpers de tarjeta viven en `inc/cpt-casos.php`. En la
primera carga siembra los 8 sectores como términos (misma lista de
`ese_latam_sectores()`) y refresca las reglas de rewrite, así la URL
funciona sin pasar por Ajustes → Enlaces permanentes. Estilos `.cx-*`.

- **Hero** (`casos-hero.php`): el `.nos-hero` de las internas con la variante
  `.cx-hero` (720px, contenido centrado). Lo anima `initHero` de `nosotros.ts`,
  por eso la página va dentro del wrapper `.nosotros[data-nosotros]`.
- **Filtros + grilla + paginación** (`casos-archivo.php`): trabaja sobre la
  query principal. Los filtros viajan por query string — `?q=` (buscador),
  `?sector=`, `?ciudad=` — y los aplica `ese_latam_casos_pre_get_posts()`
  (8 por página). Se usa `q` y no `s` a propósito: con `s` WordPress marca la
  petición como búsqueda global y deja de aplicar `archive-caso.php`. La
  paginación es la nativa (`/page/2/`), así que conserva los filtros.
- **Tarjeta** `.caso` (`ese_latam_caso_card()`): un solo marcado para el
  archivo (variante `caso--blog`, con chip de ciudad) y para "Casos reales"
  (`casos-reales.php`), que ahora lee los 4 últimos casos del CPT.
- **Placeholder**: mientras no haya casos publicados (y nadie esté filtrando)
  se muestran las tarjetas de `ese_latam_casos_placeholder()`.
- **Contactemos**: `contacto.php` acepta ahora `question => false` para no
  cerrar el titular con "?" (el "?" de "¿Necesitas asesoría?" va adentro).

Pendiente: la single de un caso (`single-caso.php`) — hoy cae en `index.php`.

---

## 📍 Encuentra un distribuidor

`page-distribuidores.php` — la página `distribuidores` la crea `inc/paginas.php`;
el footer y `ese_latam_distribuidores_url()` apuntan a ella. Estilos `.dst-*`.
Figma 3952-9273: fondo azul radial de punta a punta y cierra directo con el
footer (sin Contactemos).

- **Datos**: `inc/distribuidores.php` es la única fuente, compartida con el
  globo de la home (`front-page.php` ya no trae el arreglo inline). Cada
  distribuidor puede traer `city`, `desc`, `rep`, `phone`, `email`, `address`,
  `web`, `whatsapp` y `logo` (en `assets/imgs/distribuidores/logos/`); lo que
  falte no se pinta. Solo Perú y México tienen datos reales del Figma, el
  resto son placeholders "Datos de contacto próximamente".
- **Filtros**: `?q=` (texto sobre empresa, ciudad, país, representante y
  dirección) y `?pais=`. El servidor filtra sin JS; con JS
  (`src/ts/modules/distribuidores-page.ts`) el filtro es en vivo, actualiza el
  contador y sincroniza la URL con `replaceState`.
- **Mapa**: embed de Google Maps (`output=embed`, sin API key, igual que la
  sede en Contacto) sticky a la derecha. Arranca en el primer distribuidor
  visible; clic o Enter sobre una tarjeta la marca activa (borde celeste) y
  cambia el `src` por su dirección. En mobile el mapa va entre los filtros y
  la lista (`grid-template-areas`).

---

## 🌎 Planeta de Distribuidores

La sección `#distribuidores` de la home es un planeta 3D a sangre
(`src/ts/modules/globe-scene.ts`, Three.js + `topojson-client`) con el intro,
la tarjeta y el enlace a la izquierda y el riel de países a la derecha
(`<select>` nativo por debajo de 1100px).

- **Textura**: `assets/imgs/distribuidores/earth-diffuse.webp` + las fronteras
  de `assets/data/countries-110m.json` (world-atlas 110m) dibujadas encima en
  un canvas; los países conectados van con el borde más marcado. Rugosidad
  (mar brillante / tierra mate) y emisivo (país elegido iluminado) se pintan
  también en canvas.
- **Código ISO**: el contorno de cada país se busca por su ISO 3166 numérico
  (campo "Código ISO numérico" del término de la taxonomía Países; vacío, cae
  al mapa de `ese_latam_pais_iso()` en `inc/distribuidores.php`). Sin ISO el
  país solo tiene su marcador, que igual se puede tocar.
- **Interacción**: arrastre solo horizontal, acotado a Latinoamérica
  (`ROT_MIN`/`ROT_MAX`, se amplía si algún país queda fuera) con rebote e
  inercia; elegir un país gira e inclina hasta él, deja un pulso y un tooltip
  fijo. Hover → tooltip con el nombre; clic → lo elige y deja una onda.
- No renderiza fuera de pantalla ni con la pestaña oculta. La entrada la
  coreografía `distribuidores-intro.ts`.

---

## 🛠️ Barra de administración

El theme ya no la desactiva: quien esté logueado la ve en el frontend, con
los accesos de WordPress y el enlace para editar la página en la que está.

`.site-header` es `position: fixed`, así que el `margin-top` que WordPress
pone en `html` no le afecta y quedaría por debajo de la barra, que va en
`z-index: 99999`. Por eso `body.admin-bar .site-header` lo desplaza siguiendo
los cortes del propio `admin-bar.css`:

| Ancho | Alto de la barra | `top` del header |
| --- | --- | --- |
| > 782px | 32px | 32px |
| 601–782px | 46px | 46px |
| ≤ 600px | 46px, pero se va con el scroll | 0 |

## 🧭 Header: barra de vidrio

`header.php` + bloque "Header / Navbar" de `main.css` + `header.ts`. Una sola
cápsula flotante (logo · menú centrado · lupa + CTA) con desenfoque de fondo.
La transición oscuro→claro es CONTINUA, no un estado binario: `header.ts`
escribe `--hdr-scroll` (0 arriba de todo, 1 tras 200px de scroll, suavizado
con un quickTo de GSAP para que un giro brusco de rueda no salte) y el CSS
interpola de ese número, con `color-mix()`, el fondo, borde, texto, sombra,
el alto (68→60px) y el fundido del logo (dos capas, blanca y a color: un
`filter` interpolado pasaría por un gris sucio). Las páginas de hero claro
(`body.has-light-hero`, ver `ese_latam_body_class_hero_claro()`) fijan
`--hdr-p: 1` y el panel móvil abierto fuerza `0` sobre el navy del panel.
Sin soporte de `color-mix` queda el vidrio oscuro (fallback declarado antes).

- Hover del menú: una pastilla (`.nav-pill__indicator`) se desliza bajo el
  link con hover/foco (`initNavIndicator`, quickTo en x/ancho). Reemplaza al
  "dock magnify" y al reveal del logo tras el pill de la versión anterior.
- CTA "Contacto": píldora con degradado celeste→azul y chip de flecha que se
  vuelve verde al hover.
- Megamenú de Sectores (`.sub-menu.nav-mega`, lo arma `ese_latam_nav_fallback()`
  en inc/setup.php; interacción en nav-submenu.ts, sin cambios): panel de
  vidrio blanco de dos columnas con miniatura, título y bajada por sector, más
  un pie "Ver todos los sectores". OJO: los links del panel comparten selector
  con los de la barra (`.nav-pill__list a`), por eso pisan `height: auto` —
  sin eso el alto fijo de 40px de la barra monta las filas unas sobre otras.
- El buscador global (search-overlay.ts) no cambió.

---

## 🃏 Cards de producto de vidrio

`.product-card--glass` (recomendados de la ficha de producto y slider de
productos de la home): vidrio navy translúcido, categoría como pill, specs en
dos tiles y chip de flecha circular; la activa del coverflow se "enciende"
con borde celeste. Recomendados usa el coverflow inclinado
(`data-carousel-rotate="44" data-carousel-depth="200" data-carousel-modifier="1"`,
ver product-carousel.ts); la home conserva su coverflow plano original con
los valores por defecto. Ambos con las flechas `.embla__arrow--glass`.

El CTA principal (`.hero-cta`, `ese_latam_cta_button()`) lleva el label con
degradado celeste→azul (`--cta-label-gradient`); el hover entra como velo
(`::before`) porque un degradado no se puede transicionar. `.hero-cta--light`
lo anula con `none`.

---

## 📱 Mobile: decisiones de la auditoría (320–768px)

Todo el CSS mobile va encapsulado en media queries (`< 22.5rem`, `< 40rem`,
`< 48rem`, `< 64rem`); desktop no cambia. El bloque "MOBILE — ajustes
transversales" al final de `main.css` concentra lo que no pertenece a una
sección concreta. Verificado con un escáner por CDP (overflow horizontal,
elementos fuera del viewport, textos < 12px, controles < 40px) en 320, 360,
375, 390, 412, 430, 480 y 768px sobre las diez plantillas.

- **Header / menú móvil**: `viewport-fit=cover` + `env(safe-area-inset-*)`
  en la barra y el panel. El panel usa `100dvh` (fallback `100svh`),
  `justify-content: flex-start` y scroll interno: con `center` y el email en
  absoluto, en 320×568 y en horizontal el CTA pisaba el email y los links se
  salían por arriba y abajo.
- **Tipografía**: piso del `type-h2` en 28px y titulares de hero con clamp
  propio bajo 48rem (`.hero__title`, `.nos-hero__title`, `.sec-hero__title`,
  `.dst-hero__title`): palabras como TRANSFORMAR, MUNICIPALIDADES o
  DISTRIBUIDOR se recortaban a 320px. Nada funcional por debajo de 12px.
- **CTA principal** (`.hero-cta`) bajo 22.5rem: label y chip más compactos —
  medían 283px y no cabían en los 272px útiles de 320px (se salía en Contacto).
- **Hero home**: el pin dura 2.4 viewports en móvil (3.5 en desktop,
  `PIN_VIEWPORTS_MOBILE` en hero-scroll.ts); la coreografía se reparte igual.
- **Catálogo**: `.catalogo-grid` con `minmax(0, 1fr)` (las specs `nowrap`
  ensanchaban cada card a 315px y la grilla se salía 270–340px); en móvil las
  specs van en lista etiqueta/valor; en desktop `row-gap: 9rem` porque la foto
  sobresale ~130px por encima de su card y pisaba la fila anterior.
- **Criterio de adaptabilidad**: bajo 48rem los callouts absolutos pasan a
  lista bajo el producto (se salían -55/+86px del viewport).
- **Áreas táctiles** (≥ 40px): links del footer, ítems del aside de Objetivos,
  datos de contacto, pills de Desafíos y tabs de Valida; los inputs de los
  filtros cubren todo el alto de su control; el banner del catálogo cambió su
  píldora con dots por dos flechas cuadradas (`.catalogo-banner__arrows`, el
  mismo `embla__arrow--solid` de `.sectores__controls`), de 64px en desktop y
  48px en móvil.
- **iOS**: inputs a 16px en móvil (filtros del catálogo, orden, newsletter)
  para evitar el zoom al enfocar.
- **Three.js**: DPR 1.5 y esfera de 48 segmentos en `pointer: coarse`; el
  globo no dibuja fuera de viewport ni con la pestaña oculta
  (IntersectionObserver + visibilitychange).
- **Distribuidores**: mapa en flujo entre filtros y lista (nunca sticky en móvil).
- **Blue Angel**: 3 pasos en fila bajo 48rem, sin arco; verificado a 320px.
- **Lenis**: en táctil usa scroll nativo (no sincroniza touch), así que no hay
  lag ni conflicto con inputs ni con el panel del menú; se mantiene.

Pendiente conocido: la escena de pellets HDPE que describe la sección
Nosotros ya no existe en `nosotros.ts` (hoy es una imagen estática), así que
no hay nada que optimizar ahí.

---

## 🎠 Sliders (Embla)

`src/ts/modules/slider.ts` inicializa cualquier bloque con esta estructura:

```html
<div data-embla data-embla-contain="false">   <!-- contain false: cada slide es snap -->
  <div class="embla__viewport" data-embla-viewport>
    <div class="embla__container"> <div class="embla__slide">…</div> … </div>
  </div>
  <button data-embla-prev>…</button>            <!-- opcional, maneja disabled -->
  <button data-embla-next>…</button>
  <div data-embla-dots></div>                   <!-- opcional, dots generados -->
  <span data-embla-current>01</span>            <!-- opcional, contador 01/08 -->
  <span data-embla-total>/08</span>
</div>
```

El slide seleccionado recibe la clase `is-active` (la sección Sectores la usa
para crecer la card y revelar descripción + chip).

---

## 🎬 Animaciones de scroll

- **Lenis** (`smooth-scroll.ts`): scroll inercial global, anclas internas
  suaves. Se desactiva con `prefers-reduced-motion`.
- **Reveals** (`scroll-reveals.ts`):
  ```html
  <h2 data-reveal="up">Título</h2>
  <p data-reveal="fade" data-reveal-delay="0.2">Párrafo</p>
  <div data-reveal-stagger> <div>…</div> <div>…</div> </div>  <!-- hijos en cascada -->
  ```
  Direcciones: `up` | `down` | `left` | `right` | `fade`. Blur-up + `power3.out`.

---

## 🔠 Escala tipográfica (cuerpo de texto)

Los titulares vienen del Figma tal cual; el cuerpo de texto se subió un
escalón en toda la web para que no quede chico frente a ellos:

| Rol | Tamaño | Ejemplos |
|---|---|---|
| Bajada de sección / lede de hero | 18px | `.hero__lede`, `.productos__desc`, `.nos-desc`, `.sec-hero__desc` |
| Texto de tarjeta / panel | 16px | `.sector-card__desc`, `.residuos__panel-desc`, `.desafio__desc`, `.imp-tile__desc` |
| CTAs y links de acción | 14–15px | `.productos__cta`, `.hero-cta__label`, `.link-arrow__text`, `.caso__cta` |
| Metadatos, sub-títulos de card | 13–14px | `.country-panel__text`, `.desafio__sub`, `.certificaciones__name` |
| Kickers, labels, chips | 12px (piso) | `.type-kicker`, `.ctc-field__label`, `.caso__tag`, `.imp-tile__label` |

Nada por debajo de 12px salvo lo puramente decorativo (`.nos-hero__scroll-text`,
`.producto-hero__scroll-text`). Las variantes mobile bajan como mucho un
escalón (nunca a 8–10px como estaba la grilla del catálogo).

### Reduce-motion

`initGsap()` NO congela el timeline global con `timeScale(0)`: eso anulaba
también los `gsap.set()` con los que cada módulo deja visible el estado final,
y los heros quedaban en `visibility:hidden`. Con `prefers-reduced-motion` las
animaciones se vuelven instantáneas (`timeScale(100)`); los bucles y scrubs
ya se saltan módulo por módulo.

---

## 🎨 Design System

### Paleta de uso (consolidada)

Estos son los tokens que se usan a diario. Se aplican como `bg-primary`, `text-secondary`, `border-accent`, etc.

| Token | Hex | Nombre | Uso |
|---|---|---|---|
| `primary` | `#001E61` | Azul Marino | Base de marca, botones primarios, titulares |
| `primary-dark` | `#001545` | Azul Oscuro | Hover del accent, sombras |
| `secondary` | `#0091D1` | Azul Cielo | Acento cromático, hover del primary, links |
| `accent` | `#8EB952` | Verde Lima | Destaques, tags de sostenibilidad |
| `text-dark` | `#242424` | Carbon | Titulares y body enfatizado |
| `text-mid` | `#666666` | Gris Medio | Body normal |
| `text-soft` | `#8A98B6` | Gris Azul | Captions, kickers, metadatos |
| `bg-soft` | `#EBEFF8` | Azul Pálido | Fondos suaves de sección |
| `bg` | `#F9F9F9` | Background | Fondo default de secciones |
| `white` | `#FFFFFF` | Blanco | Cards, superficies |

### Escalas 50..900

Para hovers, elevaciones y variantes: `bg-primary-{50..900}` y `bg-secondary-{50..900}`.

### Sistema y sub-marca

- `bg-alert-surface`, `border-alert-outline`, `text-alert-text` (idem `warning`, `success`)
- `text-circulogic-green`, `bg-circulogic-blue-surface`, `text-circulogic-orange-dark`, etc.

---

### Tipografía — Plus Jakarta Sans

Utilidades directas que aplican tamaño, peso, tracking y color según el Figma:

| Clase | Tamaño | Peso | Uso |
|---|---|---|---|
| `type-display` | 80px | Light 300 | Hero, portada |
| `type-h2` | 64px | Light 300 | Titulares de sección |
| `type-h3` | 40px | SemiBold 600 | Sub-titulares |
| `type-kicker` | 12px | ExtraBold 800 uppercase, tracking `.2em` | Etiqueta encima de un titular |
| `type-nav` | 14px | SemiBold 600 uppercase, tracking `.1em` | Menú, CTAs textuales |
| `type-body-lg` | 16px | Medium 500 | Lead / intro |
| `type-body` | 16px | Regular 400 | Body copy |
| `type-caption` | 14px | Regular 400 | Metadatos, epígrafes |
| `type-tag` | 10px | ExtraBold 800 uppercase, tracking `.15em` | Chips, atributos técnicos |

**Highlight**: en `type-display` y `type-h2`, envuelve el tramo destacado en `<span class="hl">…</span>` para aplicar Bold + color primary. Es el patrón oficial del Figma.

```html
<h1 class="type-display">
  EL CAMBIO <span class="hl">COMIENZA AQUÍ</span>
</h1>
```

---

### Botones

Todos parten de `btn-base` + una variante:

```html
<a class="btn-base btn-primary">Explorar productos</a>
<a class="btn-base btn-secondary">Ver certificaciones</a>
<a class="btn-base btn-accent">Contactar ahora</a>
```

| Variante | Default | Hover |
|---|---|---|
| `btn-primary` | Fondo `primary`, texto blanco | Fondo `secondary` |
| `btn-secondary` | Fondo blanco, borde y texto `primary` | Se rellena de `primary`, texto blanco |
| `btn-accent` | Fondo `secondary`, texto blanco | Fondo `primary-dark` |

Altura 48px, radio 4px, texto 13px SemiBold uppercase tracking `.05em`.

El CTA del hero (`.hero-cta`) es un componente aparte: usa los SVG exportados
del Figma (`assets/icons/btn-primary-*.svg`) para el shape inclinado + chip de
flecha, con variantes hover.

---

## 🌐 Three.js

Cualquier elemento con `data-three-scene` monta una escena Three (carga perezosa):

```html
<div data-three-scene class="aspect-square"></div>
```

La escena base usa Primary de fondo y Secondary como mesh. Fácil de personalizar en `src/ts/modules/three-scene.ts`.
