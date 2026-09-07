<?php

namespace App\Http\Requests;

use App\Models\Aksesoris;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AksesorisTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aksesoris_id' => ['required', 'exists:aksesoris,id'],
            'jenis' => ['required', Rule::in(['pembelian', 'penjualan'])],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'aksesoris_id.required' => 'Aksesoris wajib dipilih.',
            'aksesoris_id.exists' => 'Aksesoris tidak ditemukan.',
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Jenis transaksi tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'harga_modal.required' => 'Harga modal wajib diisi.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->jenis === 'penjualan') {
                if (empty($this->harga_jual)) {
                    $validator->errors()->add('harga_jual', 'Harga jual wajib diisi untuk transaksi penjualan.');
                } elseif ($this->harga_jual < $this->harga_modal) {
                    $validator->errors()->add('harga_jual', 'Harga jual tidak boleh lebih kecil dari harga modal.');
                }
            }

            if ($this->jenis === 'pembelian') {
                if ($this->harga_jual) {
                    $validator->errors()->add('harga_jual', 'Harga jual tidak boleh diisi untuk transaksi pembelian.');
                }
            }

            $aksesoris = Aksesoris::find($this->aksesoris_id);
            if ($aksesoris && $this->jenis === 'penjualan') {
                $stokTersedia = $aksesoris->hitungStokTersedia();
                if ($this->jumlah > $stokTersedia) {
                    $validator->errors()->add('jumlah', "Jumlah melebihi stok tersedia ({$stokTersedia}).");
                }
            }
        });
    }
}
