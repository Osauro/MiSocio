<?php
    $role = Auth::user()->roleInCurrentTenant();

    if ($role === 'landlord') {
        redirect()->route('admin.home')->send();
    } else {
        redirect()->route('home')->send();
    }
?>
<?php /**PATH C:\laragon\www\licos\resources\views/dashboard.blade.php ENDPATH**/ ?>