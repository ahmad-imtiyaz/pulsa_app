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
            'harga_jual' => ['required_if:jenis,penjualan', 'nullable', 'numeric', 'min:0'],
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
            'harga_jual.required_if' => 'Harga jual wajib diisi untuk transaksi penjualan.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
        ];
    }
}
