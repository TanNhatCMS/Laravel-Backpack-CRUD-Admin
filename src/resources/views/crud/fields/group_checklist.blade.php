@php
$value = isset($field) ? old_empty_or_null($field['name'], '') ?? ($field['value'] ?? null) : null;
@endphp
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
@foreach ($field['options'] ?? [] as $groupLabel => $options)
<div class="row col-12 mb-3">
    <div class="col-12 px-0">
        <input class="group-checkall" data-target="{{ \Illuminate\Support\Str::slug($groupLabel) }}-group-checkbox"
               id="{{ \Illuminate\Support\Str::slug($groupLabel) }}-check-all" type="checkbox">
        <label for="{{ \Illuminate\Support\Str::slug($groupLabel) }}-check-all">{{ $groupLabel }}</label>
    </div>
    @foreach ($options as $key => $option)
    <div class="col-12 col-md-6 form-check checkbox">
        <input class="form-check-input {{ \Illuminate\Support\Str::slug($groupLabel) }}-group-checkbox"
               id="checkbox-{{ \Illuminate\Support\Str::slug($key) }}-{{ $loop->index }}" type="checkbox"
               name="fields[]" value="{{ $key }}" @if (is_null($value) || in_array($key, $value)) checked @endif>
        <label class="d-inline" for="checkbox-{{ \Illuminate\Support\Str::slug($key) }}-{{ $loop->index }}">
            {{ $option }}
        </label>
    </div>
    @endforeach
</div>
@endforeach
@include('crud::fields.inc.wrapper_end')
@if ($crud->fieldTypeNotLoaded($field))
@php
$crud->markFieldTypeAsLoaded($field);
@endphp
{{-- FIELD EXTRA CSS --}}
{{-- push things in the after_styles section --}}
@push('crud_fields_styles')
<!-- no styles -->
@endpush
{{-- FIELD EXTRA JS --}}
{{-- push things in the after_scripts section --}}
@push('crud_fields_scripts')
<script>
    $('.group-checkall').change(function() {
        $(`.${$(this).data('target')}`).prop('checked', this.checked);
    })
</script>
@endpush
@endif
