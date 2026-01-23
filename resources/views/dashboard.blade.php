@php
    $role = Auth::user()->roleInCurrentTenant();
    
    if ($role === 'landlord') {
        redirect()->route('landlord.home')->send();
    } else {
        redirect()->route('tenant.home')->send();
    }
@endphp
