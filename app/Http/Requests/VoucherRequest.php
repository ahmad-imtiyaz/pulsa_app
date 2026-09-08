<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher')?->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', Rule::unique('vouchers', 'kode')->ignore($voucherId)],
            'jenis' => ['required', 'string', 'max:100'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_modal'],
            'stok' => ['required', 'integer', 'min:0'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'habis'])],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama voucher wajib diisi.',
            'kode.required' => 'Kode voucher wajib diisi.',
            'kode.unique' => 'Kode voucher sudah digunakan.',
            'jenis.required' => 'Jenis voucher wajib diisi.',
            'harga_modal.required' => 'Harga modal wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
            'stok.required' => 'Stok wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
