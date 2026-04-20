<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidenceRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasRole('provider') || auth()->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rental_period' => 'required|in:monthly,yearly',
            // The form uses price_per_month; we map it to price in prepareForValidation
            'price_per_month' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'required|array|min:1',
            'facilities.*' => 'string|max:255',
            'images' => 'required|array|min:1|max:10',
            // Match frontend hint (5MB)
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'discount_type' => 'nullable|in:percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'name.required' => 'Nama residence wajib diisi',
            'name.max' => 'Nama residence maksimal 255 karakter',
            'description.required' => 'Deskripsi wajib diisi',
            'address.required' => 'Alamat wajib diisi',
            'latitude.numeric' => 'Latitude harus berupa angka',
            'latitude.between' => 'Latitude harus antara -90 dan 90',
            'longitude.numeric' => 'Longitude harus berupa angka',
            'longitude.between' => 'Longitude harus antara -180 dan 180',
            'rental_period.required' => 'Periode sewa wajib dipilih',
            'rental_period.in' => 'Periode sewa harus monthly atau yearly',
            'price_per_month.required' => 'Harga wajib diisi',
            'price_per_month.numeric' => 'Harga harus berupa angka',
            'price_per_month.min' => 'Harga tidak boleh negatif',
            'capacity.required' => 'Kapasitas wajib diisi',
            'capacity.integer' => 'Kapasitas harus berupa angka',
            'capacity.min' => 'Kapasitas minimal 1',
            'facilities.required' => 'Fasilitas wajib diisi',
            'facilities.array' => 'Fasilitas harus berupa array',
            'facilities.min' => 'Minimal 1 fasilitas',
            'images.required' => 'Gambar wajib diupload',
            'images.array' => 'Gambar harus berupa array',
            'images.min' => 'Minimal 1 gambar',
            'images.max' => 'Maksimal 10 gambar',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.mimes' => 'Format gambar: jpeg, png, jpg, gif',
            'images.*.max' => 'Ukuran gambar maksimal 5MB',
            'discount_type.in' => 'Tipe diskon harus percentage atau flat',
            'discount_value.numeric' => 'Nilai diskon harus berupa angka',
            'discount_value.min' => 'Nilai diskon tidak boleh negatif'
        ];
    }

    protected function prepareForValidation()
    {
        // Map price_per_month to price column used in DB
        if ($this->has('price_per_month')) {
            $this->merge(['price' => $this->input('price_per_month')]);
        }

        // Normalize discount fields
        if ($this->input('discount_type') && $this->input('discount_value') === null) {
            $this->merge(['discount_value' => 0]);
        }

        if (!$this->input('discount_type')) {
            $this->merge(['discount_value' => null]);
        }
    }
}

