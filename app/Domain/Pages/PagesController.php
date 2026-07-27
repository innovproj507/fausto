<?php

namespace App\Domain\Pages;

use App\Core\Request;
use App\Core\Response;

class PagesController
{
    /**
     * Sucursales page
     */
    public function sucursales(Request $request): Response
    {
        return Response::view('frontend.pages.sucursales', [
            'title' => 'Nuestras Sucursales'
        ]);
    }

    /**
     * Nosotros page
     */
    public function nosotros(Request $request): Response
    {
        return Response::view('frontend.pages.nosotros', [
            'title' => 'Nosotros'
        ]);
    }

    /**
     * Contacto page
     */
    public function contacto(Request $request): Response
    {
        return Response::view('frontend.pages.contacto', [
            'title' => 'Contáctanos'
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function contactoSubmit(Request $request): Response
    {
        $data = $request->only(['name', 'email', 'phone', 'subject', 'message']);

        // Basic validation
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es requerido';
        if (empty($data['email'])) $errors[] = 'El email es requerido';
        if (empty($data['message'])) $errors[] = 'El mensaje es requerido';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return Response::redirect('/contacto');
        }

        // TODO: Send email or save to database
        
        $_SESSION['success'] = '¡Gracias por contactarnos! Te responderemos pronto.';
        return Response::redirect('/contacto');
    }
}
