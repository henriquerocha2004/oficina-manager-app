@php
    $brandName = 'Easy Oficina';
    $brandUrl = config('app.url');
    $logoUrl = asset('images/easy-oficina-logo.png');
@endphp

<tr>
<td class="header">
<a href="{{ $brandUrl }}" style="display: inline-block;">
<img src="{{ $logoUrl }}" alt="{{ $brandName }}" style="max-width: 220px; max-height: 72px;">
</a>
</td>
</tr>
