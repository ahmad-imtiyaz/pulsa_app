<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DompetPulsaTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(['topup', 'penjualan'])],
            'tanggal' => ['required', 'date'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Jenis transaksi tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->jenis === 'penjualan') {
                if (empty($this->harga_jual)) {
                    $validator->errors()->add('harga_jual', 'Harga jual wajib diisi untuk transaksi penjualan.');
                } elseif ($this->harga_jual < $this->nominal) {
                    $validator->errors()->add('harga_jual', 'Harga jual tidak boleh lebih kecil dari harga modal.');
                }
            }

            if ($this->jenis === 'topup') {
                if ($this->harga_jual) {
                    $validator->errors()->add('harga_jual', 'Harga jual tidak boleh diisi untuk transaksi topup.');
                }
            }
        });
    }
}
