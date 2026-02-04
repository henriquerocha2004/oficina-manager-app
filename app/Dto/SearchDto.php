<?php

namespace App\Dto;

class SearchDto
{
    public function __construct(
        public ?string $search = null,
        public int $per_page = 15,
        public string $sort_by = 'created_at',
        public string $sort_direction = 'desc',
        public array $filters = []
    ) {
    }
}
