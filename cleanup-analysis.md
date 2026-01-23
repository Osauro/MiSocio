# Análisis de Archivos del Proyecto LICOS

## Archivos que SÍ se están usando:

### Imágenes usadas:
- `assets/images/saas_pos_blanco_editado.png` (logo blanco en sidebar)
- `assets/images/logo_saas_pos.png` (logo para móviles en header)
- `assets/images/profile.png` (imagen de perfil de usuario)
- `assets/images/favicon.png` (favicon del sitio)
- `assets/svg/iconly-sprite.svg` (iconos SVG)

### CSS usados:
- `assets/css/vendors/flag-icon.css`
- `assets/css/iconly-icon.css`
- `assets/css/bulk-style.css`
- `assets/css/themify.css`
- `assets/css/fontawesome-min.css`
- `assets/css/vendors/weather-icons/weather-icons.min.css`
- `assets/css/vendors/scrollbar.css`
- `assets/css/vendors/slick.css`
- `assets/css/vendors/slick-theme.css`
- `assets/css/style.css`
- `assets/css/color-1.css`
- `assets/css/custom.css`

### JS usados:
- `assets/js/vendors/jquery/jquery.min.js`
- `assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js`
- `assets/js/vendors/bootstrap/dist/js/popper.min.js`
- `assets/js/vendors/font-awesome/fontawesome-min.js`
- `assets/js/vendors/feather-icon/feather.min.js`
- `assets/js/vendors/feather-icon/custom-script.js`
- `assets/js/sidebar.js`
- `assets/js/height-equal.js`
- `assets/js/config.js`
- `assets/js/chart/apex-chart/apex-chart.js`
- `assets/js/chart/apex-chart/stock-prices.js`
- `assets/js/scrollbar/simplebar.js`
- `assets/js/scrollbar/custom.js`
- `assets/js/slick/slick.min.js`
- `assets/js/slick/slick.js`
- `assets/js/theme-customizer/customizer.js`
- `assets/js/animation/tilt/tilt.jquery.js`
- `assets/js/animation/tilt/tilt-custom.js`
- `assets/js/script.js`
- `assets/js/custom.js`

### Vistas Livewire activas:
- `categorias.blade.php`
- `clientes.blade.php`
- `home-landlord.blade.php`
- `home-tenant.blade.php`
- `productos.blade.php`
- `usuarios.blade.php`

### Layouts usados:
- `layouts/tenant/theme.blade.php`
- `layouts/tenant/header.blade.php`
- `layouts/tenant/sidebar.blade.php`
- `layouts/landlord/theme.blade.php`
- `layouts/landlord/header.blade.php`
- `layouts/landlord/sidebar.blade.php`
- `layouts/app.blade.php` (perfil)
- `layouts/guest.blade.php` (login/registro)
- `layouts/navigation.blade.php`

### Vistas de autenticación usadas:
- `auth/login.blade.php`
- `auth/register.blade.php` (probablemente)
- `auth/*` (resto de archivos de autenticación)

### Otros archivos usados:
- `welcome.blade.php` (página de inicio)
- `dashboard.blade.php` (si se usa)
- `profile/edit.blade.php` y partials

## Archivos INNECESARIOS para eliminar:

### Imágenes innecesarias en public/assets/images:
- Todas las subcarpetas que no se usan: alert/, apexchart/, avatars/, avtar/, banner/, big-lightgallry/, blog/, customizer/, dashboard-1/, dashboard-2/, dashboard-3/, ecommerce/, email/, email-template/, faq/, file-manager/, forms/, gif/, job-search/, knowledgebase/, landing/, lightgallry/, login/, logo/ (revisar), masonry/, other-images/, product/, product-1/, scrollbar/, slider/, social-app/, switch/, user/, users/, widget/
- Imágenes no usadas: saaspos.png, logo_spos.png, logo_saas1.jpg, logo_saas1_nobg.png, widget-bg.png
- Imágenes de DataTables: details_close.png, details_open.png, js-grid.png, sort_*.png

### Vistas innecesarias en resources/views:
- `vendor/pagination/*` (todas las vistas de paginación de vendor que no usamos)
- Cualquier vista de ejemplo que no se use

### Controladores innecesarios:
- Verificar controllers que no se usan en routes

## Recomendaciones:

1. **Eliminar carpetas de imágenes completas** que son de demos del template
2. **Mantener solo las vistas de paginación que usamos** (bootstrap probablemente)
3. **Revisar y eliminar CSS/JS** de funcionalidades no usadas (weather-icons, algunos vendors)
4. **Limpiar la carpeta public/assets/images** de todas las subcarpetas de demo
5. **Revisar si usamos los charts** de ApexCharts, si no, eliminar
6. **Revisar theme-customizer** si lo usamos
7. **Revisar animaciones (tilt)** si las usamos
