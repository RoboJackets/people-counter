<dropdown-trigger class="h-9 flex items-center">
    <span class="text-90">
        {{ auth()->user()->full_name }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="100" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ route('logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</dropdown-menu>
