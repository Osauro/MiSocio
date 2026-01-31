@php
    $role = Auth::user()->roleInCurrentTenant();

    if ($role === 'landlord') {
        redirect()->route('admin.home')->send();
    } else {
        redirect()->route('home')->send();
    }
@endphp
