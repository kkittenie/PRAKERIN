@props([
    'title',
    'description',
    'icon' => 'icon-clock',
    'iconBg' => 'bg-gray-100',
    'iconColor' => 'text-gray-300',
])

{{-- Reusable empty-state, shared by Riwayat, Voucher, and Stamp — each screen passes
     its own icon/color so they read as related but not identical. flex-1 lets it fill
     whatever vertical space its flex-col parent has left, so it sits centered instead
     of hugging the top. --}}
<div class="flex flex-1 flex-col items-center justify-center py-10 text-center">
    <span class="relative flex h-20 w-20 items-center justify-center rounded-full {{ $iconBg }} {{ $iconColor }}">
        @include('member.components.'.$icon, ['class' => 'h-9 w-9'])
        <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-gray-100"></span>
        <span class="absolute -left-2 bottom-1 h-2 w-2 rounded-full bg-gray-100"></span>
    </span>
    <p class="mt-5 text-sm font-bold text-gray-400">{{ $title }}</p>
    <p class="mt-1 max-w-[220px] text-xs leading-relaxed text-gray-300">{{ $description }}</p>
</div>
