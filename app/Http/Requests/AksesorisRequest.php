<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AksesorisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aksesorisId = $this->route('aksesoris')?->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('aksesoris', 'sku')->ignore($aksesorisId)],
            'kategori' => ['nullable', 'string', 'max:100'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_modal'],
            'stok' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama aksesoris wajib diisi.',
            'sku.required' => 'SKU wajib diisi.',
            'sku.unique' => 'SKU sudah digunakan.',
            'harga_modal.required' => 'Harga modal wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
            'stok.required' => 'Stok wajib diisi.',
        ];
    }
}
