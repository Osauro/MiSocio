# Script de limpieza de archivos innecesarios del proyecto LICOS
# Ejecutar desde la raíz del proyecto

# Crear backup antes de eliminar
Write-Host "=== Limpieza de Archivos Innecesarios ===" -ForegroundColor Cyan
Write-Host "Se recomienda hacer un backup antes de continuar." -ForegroundColor Yellow
$confirm = Read-Host "¿Continuar con la limpieza? (s/n)"

if ($confirm -ne 's') {
    Write-Host "Operación cancelada." -ForegroundColor Red
    exit
}

# Carpetas de imágenes innecesarias en public/assets/images
$imageFoldersToDelete = @(
    "public\assets\images\alert",
    "public\assets\images\apexchart",
    "public\assets\images\avatars",
    "public\assets\images\avtar",
    "public\assets\images\banner",
    "public\assets\images\big-lightgallry",
    "public\assets\images\blog",
    "public\assets\images\customizer",
    "public\assets\images\dashboard-1",
    "public\assets\images\dashboard-2",
    "public\assets\images\dashboard-3",
    "public\assets\images\ecommerce",
    "public\assets\images\email",
    "public\assets\images\email-template",
    "public\assets\images\faq",
    "public\assets\images\file-manager",
    "public\assets\images\forms",
    "public\assets\images\gif",
    "public\assets\images\job-search",
    "public\assets\images\knowledgebase",
    "public\assets\images\landing",
    "public\assets\images\lightgallry",
    "public\assets\images\login",
    "public\assets\images\masonry",
    "public\assets\images\other-images",
    "public\assets\images\product",
    "public\assets\images\product-1",
    "public\assets\images\scrollbar",
    "public\assets\images\slider",
    "public\assets\images\social-app",
    "public\assets\images\switch",
    "public\assets\images\user",
    "public\assets\images\users",
    "public\assets\images\widget"
)

# Imágenes individuales innecesarias
$imageFilesToDelete = @(
    "public\assets\images\saaspos.png",
    "public\assets\images\logo_spos.png",
    "public\assets\images\logo_saas1.jpg",
    "public\assets\images\logo_saas1_nobg.png",
    "public\assets\images\widget-bg.png",
    "public\assets\images\details_close.png",
    "public\assets\images\details_open.png",
    "public\assets\images\js-grid.png",
    "public\assets\images\sort_asc.png",
    "public\assets\images\sort_desc.png",
    "public\assets\images\sort_both.png",
    "public\assets\images\sort_asc_disabled.png",
    "public\assets\images\sort_desc_disabled.png"
)

# Vistas de paginación innecesarias (mantener solo bootstrap-5)
$paginationViewsToDelete = @(
    "resources\views\vendor\pagination\bootstrap.blade.php",
    "resources\views\vendor\pagination\bootstrap-4.blade.php",
    "resources\views\vendor\pagination\default.blade.php",
    "resources\views\vendor\pagination\semantic-ui.blade.php",
    "resources\views\vendor\pagination\simple-bootstrap-4.blade.php",
    "resources\views\vendor\pagination\simple-bootstrap-5.blade.php",
    "resources\views\vendor\pagination\simple-default.blade.php",
    "resources\views\vendor\pagination\simple-tailwind.blade.php",
    "resources\views\vendor\pagination\tailwind.blade.php"
)

$deletedCount = 0
$errorCount = 0

# Eliminar carpetas de imágenes
Write-Host "`nEliminando carpetas de imágenes innecesarias..." -ForegroundColor Yellow
foreach ($folder in $imageFoldersToDelete) {
    if (Test-Path $folder) {
        try {
            Remove-Item -Path $folder -Recurse -Force
            Write-Host "✓ Eliminado: $folder" -ForegroundColor Green
            $deletedCount++
        } catch {
            Write-Host "✗ Error al eliminar: $folder" -ForegroundColor Red
            $errorCount++
        }
    } else {
        Write-Host "- No existe: $folder" -ForegroundColor Gray
    }
}

# Eliminar imágenes individuales
Write-Host "`nEliminando imágenes individuales innecesarias..." -ForegroundColor Yellow
foreach ($file in $imageFilesToDelete) {
    if (Test-Path $file) {
        try {
            Remove-Item -Path $file -Force
            Write-Host "✓ Eliminado: $file" -ForegroundColor Green
            $deletedCount++
        } catch {
            Write-Host "✗ Error al eliminar: $file" -ForegroundColor Red
            $errorCount++
        }
    } else {
        Write-Host "- No existe: $file" -ForegroundColor Gray
    }
}

# Eliminar vistas de paginación innecesarias
Write-Host "`nEliminando vistas de paginación innecesarias..." -ForegroundColor Yellow
foreach ($file in $paginationViewsToDelete) {
    if (Test-Path $file) {
        try {
            Remove-Item -Path $file -Force
            Write-Host "✓ Eliminado: $file" -ForegroundColor Green
            $deletedCount++
        } catch {
            Write-Host "✗ Error al eliminar: $file" -ForegroundColor Red
            $errorCount++
        }
    } else {
        Write-Host "- No existe: $file" -ForegroundColor Gray
    }
}

Write-Host "`n=== Resumen ===" -ForegroundColor Cyan
Write-Host "Elementos eliminados: $deletedCount" -ForegroundColor Green
Write-Host "Errores: $errorCount" -ForegroundColor $(if ($errorCount -eq 0) { "Green" } else { "Red" })
Write-Host "`nLimpieza completada." -ForegroundColor Cyan
