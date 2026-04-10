                @foreach ($menuItems as $item)
                    <a
                        href="{{ $item['href'] ?? '#' }}"
                        @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors',
                            'bg-blue-800/80 text-white shadow-inner' => $item['active'],
                            'text-blue-100 hover:bg-blue-900/80' => ! $item['active'],
                        ])
                    >
                        <x-sidebar-icon :name="$item['icon'] ?? $item['label']" />
                        <span x-show="sidebarOpen" x-transition.opacity class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
