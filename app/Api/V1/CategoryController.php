<?php

namespace App\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database\Connection;

class CategoryController extends ApiController
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function index(Request $request): Response
    {
        $categories = $this->db->fetchAll(
            'SELECT * FROM categories WHERE status = "active" ORDER BY sort_order ASC, name ASC'
        );

        return Response::json(['data' => $categories]);
    }
}
