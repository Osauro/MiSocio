#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import codecs

file_path = r'c:\laragon\www\licos\app\Livewire\Prestamo.php'

# Leer archivo
with codecs.open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Reemplazos
replacements = {
    'ÃƒÆ'Ã‚Â©': 'é',
    'ÃƒÆ'Ã‚Â³': 'ó',
    'ÃƒÆ'Ã‚Â­': 'í',
    'ÃƒÆ'Ã‚Â¡': 'á',
    'ÃƒÆ'Ã‚Âº': 'ú',
    'ÃƒÆ'Ã‚Â±': 'ñ',
    'ÃƒÂ©': 'é',
    'ÃƒÂ³': 'ó',
    'ÃƒÂ­': 'í',
    'ÃƒÂ¡': 'á',
    'ÃƒÂº': 'ú',
    'ÃƒÂ±': 'ñ',
    'ÃƒÃ±': 'Ñ',
    'Ãƒâ€šÃ‚Â¿': '¿',
    'Ãƒâ€šÃ‚Â¡': '¡',
}

for old, new in replacements.items():
    content = content.replace(old, new)

# Guardar
with codecs.open(file_path, 'w', encoding='utf-8-sig') as f:
    content_no_bom = content.lstrip('\ufeff')
    f.write(content_no_bom)

with codecs.open(file_path, 'w', encoding='utf-8') as f:
    content_no_bom = content.lstrip('\ufeff')
    f.write(content_no_bom)

print("Archivo limpiado")
