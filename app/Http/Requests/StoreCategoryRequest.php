<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox value to boolean
        $this->merge([
            'is_active' => $this->has('is_active') ? true : false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:200|unique:categories,slug',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'description' => 'описание',
            'slug' => 'URL',
            'is_active' => 'статус',
            'meta_title' => 'мета заголовок',
            'meta_description' => 'мета описание',
        ];
    }
}
