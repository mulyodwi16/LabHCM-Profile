@props(['type' => 'image'])

<p {{ $attributes->merge(['class' => 'text-xs text-slate-500 mt-1']) }}>
    @if ($type === 'pdf')
        Format didukung: <strong>PDF</strong>. Maks. 10 MB.
    @else
        Format didukung: <strong>JPG, PNG</strong>. Maks. 8 MB per file.
    @endif
</p>
