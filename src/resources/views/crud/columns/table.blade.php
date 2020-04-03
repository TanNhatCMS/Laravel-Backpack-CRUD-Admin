@php
	$value = data_get($entry, $column['name']);

    // make sure columns are defined
    if (!isset($column['columns'])) {
        $column['columns'] = ['value' => "Value"];
    }
    
	$columns = $column['columns'];

	// if this attribute isn't using attribute casting, decode it
	if (is_string($value)) {
	    $value = json_decode($value);
	}
    
    // if the json value is marked as only one assoc array, let's adapt it
	// when json attribute is stored as {key: val, key: val} not [{key: val, key: val}]
	if ((!empty($column['only_assoc'])) && ($column['only_assoc'] === true)) {
		$value = [$value];
	}


@endphp

<span>
	@if ($value && count($columns))
	<table class="table table-bordered table-condensed table-striped m-b-0">
		<thead>
			<tr>
				@foreach($columns as $tableColumnKey => $tableColumnLabel)
				<th>{{ $tableColumnLabel }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($value as $tableRow)
			<tr>
				@foreach($columns as $tableColumnKey => $tableColumnLabel)
					<td>
                    
						@if( is_array($tableRow) && isset($tableRow[$tableColumnKey]) )
                            
                            {{ $tableRow[$tableColumnKey] }}
                        
                        @elseif( is_object($tableRow) && property_exists($tableRow, $tableColumnKey) )
                        
                            {{ $tableRow->{$tableColumnKey} }}
                        
                        @endif
                        
					</td>
				@endforeach
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</span>
