<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactSetting;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $contactModel = ContactSetting::first();
        
        $contact = [];
        if ($contactModel) {
            $contact = $contactModel->toArray();
        } else {
            // Fallback default
            $contact = [
                'place_name' => 'Bank Indonesia Jambi',
                'address' => 'Jl. A Yani No.14, Telanaipura, Kec. Telanaipura, Kota Jambi, Jambi 36361',
                'email' => 'genbijambibi@gmail.com',
                'phone' => '089627896750',
                'coordinates_label' => '-1.597995, 103.582875',
                'maps_url' => 'https://maps.app.goo.gl/9Zc1qVZVw4TzY7X67',
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.2241586520556!2d103.58029997496666!3d-1.597994998387063!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e2589000b0e5ad5%3A0x897f9cbb7a67f6a7!2sBank%20Indonesia%20Representative%20Office%20Jambi!5e0!3m2!1sen!2sid!4v1709210000000!5m2!1sen!2sid',
            ];
        }

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json($contact);
        }

        return view('public.contact.index', [
            'contact' => $contact,
            'settings' => [],
            'scripts' => '<script defer src="/assets/js/dist/pages/contact.js"></script>',
        ]);
    }
}
