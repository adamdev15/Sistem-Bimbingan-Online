@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-slate-50 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500']) }}>
