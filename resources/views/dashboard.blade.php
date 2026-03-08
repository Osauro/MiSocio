@php
    $role = Auth::user()->roleInCurrentTenant();

    if ($role === 'landlord') {
        redirect()->route('admin.dashboard')->send();
    } else {
        redirect()->route('dashboard')->send();
    }
@endphp
