<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Residence;
use App\Models\Activity;

class StoreBookingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->hasRole('user');
    }

    public function rules()
    {
        $rules = [
            'bookable_type' => 'required|in:residence,activity,App\\Models\\Residence,App\\Models\\Activity',
            'bookable_id' => 'required|integer',
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string|max:1000'
        ];

        // Add conditional rules based on bookable type
        if ($this->bookable_type === 'residence') {
            $rules['check_in_date'] = 'required|date|after_or_equal:today';
            $rules['check_out_date'] = 'nullable|date|after:check_in_date';
        } else {
            // For activity, check_in_date is required but validation is handled in validateBookableItem
            $rules['check_in_date'] = 'required|date';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'bookable_type.required' => 'Tipe booking wajib dipilih',
            'bookable_type.in' => 'Tipe booking harus residence atau activity',
            'bookable_id.required' => 'Item booking wajib dipilih',
            'bookable_id.integer' => 'ID item harus berupa angka',
            'check_in_date.required' => 'Tanggal check-in wajib diisi',
            'check_in_date.date' => 'Tanggal check-in harus berupa tanggal yang valid',
            'check_in_date.after_or_equal' => 'Tanggal check-in minimal hari ini',
            'check_out_date.date' => 'Tanggal check-out harus berupa tanggal yang valid',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in',
            'documents.required' => 'Dokumen wajib diupload',
            'documents.array' => 'Dokumen harus berupa array',
            'documents.min' => 'Minimal 1 dokumen',
            'documents.*.file' => 'File harus berupa file yang valid',
            'documents.*.mimes' => 'Format file: pdf, jpg, jpeg, png',
            'documents.*.max' => 'Ukuran file maksimal 2MB',
            'notes.max' => 'Catatan maksimal 1000 karakter'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBookableItem($validator);
            $this->validateAvailability($validator);
        });
    }

    protected function prepareForValidation()
    {
        $type = $this->input('bookable_type');
        if ($type === 'App\\Models\\Residence') {
            $this->merge(['bookable_type' => 'residence']);
        } elseif ($type === 'App\\Models\\Activity') {
            $this->merge(['bookable_type' => 'activity']);
        }
    }

    protected function validateBookableItem($validator)
    {
        $type = $this->bookable_type;
        $id = $this->bookable_id;

        if ($type === 'residence') {
            $item = Residence::find($id);
        } else {
            $item = Activity::find($id);
        }

        if (!$item) {
            $validator->errors()->add('bookable_id', 'Item tidak ditemukan');
            return;
        }

        if (!$item->is_active) {
            $validator->errors()->add('bookable_id', 'Item tidak aktif');
        }

        // Additional validation for activity
        if ($type === 'activity') {
            if ($item->registration_deadline <= now()) {
                $validator->errors()->add('bookable_id', 'Registrasi sudah ditutup');
            }

            // Validate that check_in_date matches event_date
            $checkInDate = $this->input('check_in_date');
            if ($checkInDate && $item->event_date->format('Y-m-d') !== $checkInDate) {
                $validator->errors()->add('check_in_date', 'Tanggal booking harus sesuai dengan tanggal kegiatan');
            }
        }
    }

    protected function validateAvailability($validator)
    {
        $type = $this->bookable_type;
        $id = $this->bookable_id;

        if ($type === 'residence') {
            $item = Residence::find($id);
        } else {
            $item = Activity::find($id);
        }

        if ($item && $item->available_slots <= 0) {
            $validator->errors()->add('bookable_id', 'Tidak ada slot tersedia');
        }
    }
}

