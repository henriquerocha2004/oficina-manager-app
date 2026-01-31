<?php

namespace App\Http\Requests;

use App\Dto\SearchDto;
use Illuminate\Foundation\Http\FormRequest;

class FindClientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'sort_direction' => 'sometimes|in:asc,desc',
            'sort_field' => 'sometimes|string|in:name,document_number,created_at',
            'filters' => 'sometimes|array',
        ];
    }

    public function toDto(): SearchDto
    {
        $validated = $this->validated();

        return new SearchDto(
            search: $validated['search'] ?? null,
            per_page: $validated['per_page'] ?? 15,
            sort_by: $validated['sort_field'] ?? 'created_at',
            sort_direction: $validated['sort_direction'] ?? 'desc',
            filters: $validated['filters'] ?? [],
        );
    }
}
