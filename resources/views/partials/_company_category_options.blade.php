<option value="">--Select--</option>
@foreach(\App\Models\Company\CompanyCategory::where('is_active', true)->orderBy('sort_order')->get() as $cat)
    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
@endforeach
