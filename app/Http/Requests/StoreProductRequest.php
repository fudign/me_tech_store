<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        // Convert checkbox value to integer for PostgreSQL compatibility
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);

        // Convert price from KGS to cents if needed (frontend sends in KGS)
        if ($this->has('price') && is_numeric($this->price)) {
            $this->merge([
                'price' => (int) ($this->price * 100),
            ]);
        }

        // Convert old_price from KGS to cents if provided
        if ($this->has('old_price') && is_numeric($this->old_price)) {
            $this->merge([
                'old_price' => (int) ($this->old_price * 100),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:products,slug'],
            'availability_status' => ['required', 'in:in_stock,out_of_stock,coming_soon'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_urls' => ['nullable', 'string'],
            'main_image_index' => ['nullable', 'integer', 'min:0'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.key' => ['required_with:attributes', 'string', 'max:100'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:255'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название товара обязательно для заполнения',
            'name.max' => 'Название не должно превышать 200 символов',
            'price.required' => 'Цена обязательна для заполнения',
            'price.integer' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'slug.unique' => 'Товар с таким slug уже существует',
            'categories.*.exists' => 'Выбранная категория не существует',
            'images.max' => 'Можно загрузить не более 10 изображений',
            'images.*.image' => 'Файл должен быть изображением',
            'images.*.mimes' => 'Допустимые форматы: jpg, jpeg, png, webp',
            'images.*.max' => 'Размер изображения не должен превышать 2MB',
            'attributes.*.key.required_with' => 'Название характеристики обязательно',
            'attributes.*.value.required_with' => 'Значение характеристики обязательно',
            'meta_title.max' => 'Meta title не должен превышать 200 символов',
            'meta_description.max' => 'Meta description не должно превышать 300 символов',
        ];
    }
}
