<li filter-name="{{ $filter->name }}" filter-type="{{ $filter->type }}" class="mx-2 py-1 align-self-center">
    <select class="form-control {{ $filter->name }}-class" name="{{ $filter->name }}">
        <option value="">{{ $filter->label }}</option>
        @foreach ($filter->values as $option)
        <option value="{{ $option }}" class="text-uppercase">{{ $option }}</option>
        @endforeach
    </select>
</li>
@push('crud_list_scripts')
<script>
    jQuery(document).ready(function($) {
        $(".{{ $filter->name }}-class").change(function(e) {
            e.preventDefault();
            var parameter = $(this).attr('name');
            var value = $(this).val();
            var ajax_table = $("#crudTable").DataTable();
            var current_url = ajax_table.ajax.url();
            var new_url = addOrUpdateUriParameter(current_url, parameter, value);
            new_url = normalizeAmpersand(new_url.toString());
            ajax_table.ajax.url(new_url).load();
        });
        $("li[filter-name={{ $filter->name }}]").on('filter:clear', function(e) {
            $(".{{ $filter->name }}-class").value('');
        });
    });
</script>
@endpush
