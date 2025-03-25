<li filter-name="{{ $filter->name }}" filter-type="{{ $filter->type }}" class="mx-2 py-1 align-self-center">
    <a href="#" class="nav-link dropdown-toggle show" data-toggle="dropdown" data-bs-toggle="dropdown" role="button"
       aria-haspopup="true" aria-expanded="true">Role <span class="caret"></span></a>
    <ul class="dropdown-menu show" data-bs-popper="static">
        <a class="dropdown-item" parameter="{{ $filter->name }}" dropdownkey="" href="">-</a>
        <div role="separator" class="dropdown-divider"></div>
        @foreach ($filter->values as $option)
        <a class="dropdown-item" parameter="{{ $filter->name }}" href="" dropdownkey="{{ $loop->iteration }}">{{ $option }}</a>
        @endforeach
    </ul>
</li>
@push('crud_list_scripts')
<script>
    jQuery(document).ready(function($) {
        $("li.dropdown[filter-key={{ $filter->name }}] .dropdown-menu a").click(function(e) {
            e.preventDefault();
            var value = $(this).attr('dropdownkey');
            var parameter = $(this).attr('parameter');
            var new_url = updateDatatablesOnFilterChange(parameter, value, true, 0);

            // mark this filter as active in the navbar-filters
            // mark dropdown items active accordingly
            if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
                $("li[filter-key={{ $filter->name }}]").removeClass('active').addClass('active');
                $("li[filter-key=role] .dropdown-menu a").removeClass('active');
                $(this).addClass('active');
            }
            else
            {
                $("li[filter-key={{ $filter->name }}]").trigger("filter:clear");
            }
        });
        // clear filter event (used here and by the Remove all filters button)
        $("li[filter-key={{ $filter->name }}]").on('filter:clear', function(e) {
            $("li[filter-key={{ $filter->name }}]").removeClass('active');
            $("li[filter-key={{ $filter->name }}] .dropdown-menu a").removeClass('active');
        });
    });
</script>
@endpush
