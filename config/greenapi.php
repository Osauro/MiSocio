<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Green API (WhatsApp) Credentials
    |--------------------------------------------------------------------------
    | Used for sending WhatsApp notifications via the Green API service.
    | https://green-api.com
    */

    'instance_id'    => env('GREENAPI_INSTANCE_ID', ''),
    'api_token'      => env('GREENAPI_API_TOKEN', ''),

    // Phone that receives landlord-level alerts (new tenants, verifications, etc.)
    'landlord_phone' => env('GREENAPI_LANDLORD_PHONE', ''),

    'base_url'       => 'https://api.green-api.com',
];
