<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DompetPulsaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dompetId = $this->route('dompet_pulsa')?->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', Rule::unique('dompet_pulsa', 'kode')->ignore($dompetId)],
            'saldo_awal' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama dompet wajib diisi.',
            'kode.required' => 'Kode dompet wajib diisi.',
            'kode.unique' => 'Kode dompet sudah digunakan.',
            'saldo_awal.required' => 'Saldo awal wajib diisi.',
            'saldo_awal.numeric' => 'Saldo awal harus berupa angka.',
            'saldo_awal.min' => 'Saldo awal tidak boleh negatif.',
        ];
    }
}
