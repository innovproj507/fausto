<?php

namespace App\Domain\Seo;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;
use App\Domain\Pages\PagesController;

class SitemapController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $urls = [
            ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => url('/products'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => url('/sucursales'), 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => url('/nosotros'), 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => url('/contacto'), 'changefreq' => 'monthly', 'priority' => '0.4'],
        ];

        foreach (array_keys(PagesController::GUIDES) as $slug) {
            $urls[] = [
                'loc' => url('/guias/' . $slug),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        foreach ($this->db->fetchAll('SELECT slug, updated_at FROM categories WHERE status = "active"') as $category) {
            $urls[] = [
                'loc' => url('/category/' . $category['slug']),
                'lastmod' => $this->toDate($category['updated_at']),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        foreach ($this->db->fetchAll('SELECT slug, updated_at FROM products WHERE status = "active"') as $product) {
            $urls[] = [
                'loc' => url('/products/' . $product['slug']),
                'lastmod' => $this->toDate($product['updated_at']),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return new Response($this->buildXml($urls), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function toDate(?string $timestamp): ?string
    {
        return $timestamp ? date('Y-m-d', strtotime($timestamp)) : null;
    }

    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>' . "\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
