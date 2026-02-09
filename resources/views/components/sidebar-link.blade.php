@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'flex items-center px-4 py-2.5 mb-1 text-sm font-medium rounded-md transition-colors ' . 
    ($active 
        ? 'bg-[#F2F2F2] text-[#034C8C]' 
        : 'text-gray-700 hover:bg-[#F2F2F2]')
]) }}>
    {{ $slot }}
</a>