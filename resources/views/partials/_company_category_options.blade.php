{{-- Opsi company category. Form admin kirim ['includeInactive' => true] supaya
     company yang masih di kategori nonaktif (mis. "Supplier/Distributor/Manufacturer")
     tetap tampil terpilih di modal edit dan tidak ikut terhapus saat disimpan.
     Form publik hanya menampilkan kategori aktif. --}}
@php
    $companyCategoryRows = \App\Models\Company\CompanyCategory::orderBy('sort_order')->get();
@endphp
<option value="">--Select--</option>
@foreach($companyCategoryRows->where('is_active', true) as $cat)
    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
@endforeach
@if (!empty($includeInactive) && $companyCategoryRows->where('is_active', false)->isNotEmpty())
    <optgroup label="Kategori lama (nonaktif) — pilih ulang">
        @foreach($companyCategoryRows->where('is_active', false) as $cat)
            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
        @endforeach
    </optgroup>
@endif
