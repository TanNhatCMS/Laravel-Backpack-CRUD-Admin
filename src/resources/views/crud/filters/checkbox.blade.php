<li filter-name="{{ $filter->name }}" filter-type="{{ $filter->type }}" class="mx-2 align-self-center">
    <div class="form-check form-check-inline mr-1">
        <input class="form-check-input {{ $filter->name }}-class" name="{{ $filter->name }}"
               id="inline-checkbox-{{ $filter->name }}" type="checkbox" @if (Request::get($filter->name)) checked @endif>
        <label class="form-check-label" for="inline-checkbox-{{ $filter->name }}">{{ $filter->label }}</label>
    </div>
</li>
@push('crud_list_scripts')
<script>
    jQuery(document).ready(function($) {
        $(".{{ $filter->name }}-class").change(function(e) {
            e.preventDefault();
            var parameter = $(this).attr('name');
            var checked = $(this).prop('checked') ? 1 : 0;
            var ajax_table = $("#crudTable").DataTable();
            var current_url = ajax_table.ajax.url();
            var new_url = addOrUpdateUriParameter(current_url, parameter, checked);
            new_url = normalizeAmpersand(new_url.toString());
            ajax_table.ajax.url(new_url).load();
        });
        $("li[filter-name={{ $filter->name }}]").on('filter:clear', function(e) {
            $(".{{ $filter->name }}-class").prop('checked', false);
        });
    });
</script>
@endpush
