<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'harga_modal' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_modal'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'harga_modal.required' => 'Harga modal wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $voucher = $this->route('voucher');

            if ($voucher) {
                $stokTersedia = $voucher->hitungStokTersedia();

                // Saat EDIT transaksi (bukan create), jumlah transaksi yang sedang
                // diedit sudah ikut mengurangi stok tersedia versi lama. Tambahkan
                // kembali dulu supaya validasi membandingkan terhadap stok yang
                // benar (seolah transaksi lama belum ada), bukan stok yang sudah
                // "kepotong" oleh dirinya sendiri.
                $transaksi = $this->route('transaksi');
                if ($transaksi) {
                    $stokTersedia += $transaksi->jumlah;
                }

                if ($this->jumlah > $stokTersedia) {
                    $validator->errors()->add('jumlah', "Jumlah melebihi stok tersedia ({$stokTersedia}).");
                }
            }
        });
    }
}
