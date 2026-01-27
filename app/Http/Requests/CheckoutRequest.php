<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Guest checkout, no auth required
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'phone:KG'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', 'in:cash,online,installment'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Укажите ваше имя',
            'name.min' => 'Имя должно содержать минимум 2 символа',
            'phone.required' => 'Укажите номер телефона',
            'phone.phone' => 'Неверный формат телефона. Используйте +996 XXX XXX XXX',
            'address.required' => 'Укажите адрес доставки',
            'address.min' => 'Адрес должен содержать минимум 10 символов',
            'payment_method.required' => 'Выберите способ оплаты',
            'payment_method.in' => 'Неверный способ оплаты',
        ];
    }
}
