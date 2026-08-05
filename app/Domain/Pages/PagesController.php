<?php

namespace App\Domain\Pages;

use App\Core\Request;
use App\Core\Response;

class PagesController
{
    /**
     * Guías técnicas de respuesta directa (formato AEO): cada entrada es una
     * pregunta real de cliente con una vista propia. Se agregan aquí a
     * medida que se publica cada pieza del calendario de contenido.
     */
    public const GUIDES = [
        'anclaje-para-pared-de-concreto' => [
            'title' => 'Qué Anclaje Usar en Pared de Concreto - Fausto Salazar, S.A.',
            'view' => 'frontend.pages.guias.anclaje-para-pared-de-concreto',
        ],
    ];

    /**
     * Guide page (AEO direct-answer format)
     */
    public function guia(Request $request, string $slug): Response
    {
        $guide = self::GUIDES[$slug] ?? null;

        if (!$guide) {
            return Response::view('frontend.404', [], 404);
        }

        return Response::view($guide['view'], [
            'title' => $guide['title'],
        ]);
    }

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
