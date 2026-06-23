@php
    $user = auth()->user();
    $userPerms = [];
    $isFacultyOrHigher = false;
    if ($user && ($user->isFaculty() || $user->isHigherFaculty())) {
        $isFacultyOrHigher = true;
        $userPerms = $user->permissions->pluck('permission')->toArray();
        if ($user->students()->exists() && !in_array('guide', $userPerms)) {
            $userPerms[] = 'guide';
        }
    }
    
    $isMultiAuthority = $isFacultyOrHigher && count($userPerms) > 1;

    $activeDashboard = session('selected_dashboard');
    if (!$activeDashboard) {
        if (request()->routeIs('faculty.guide-dashboard') || request()->routeIs('faculty.guide.*')) {
            $activeDashboard = 'guide';
        } elseif (request()->routeIs('faculty.approval-dashboard') || request()->routeIs('faculty.applications.*')) {
            $activeDashboard = 'approval_faculty';
        } elseif (request()->routeIs('higher-faculty.noc-dashboard') || request()->routeIs('higher-faculty.applications.*')) {
            $activeDashboard = 'noc_authority';
        }
    }

    $dashboardLabels = [
        'guide' => 'Guide Dashboard',
        'approval_faculty' => 'Approval Dashboard',
        'noc_authority' => 'NOC Dashboard',
    ];
    $activeDashboardLabel = $dashboardLabels[$activeDashboard] ?? 'Dashboard';
@endphp
<nav x-data="{ open: false }" class="bg-white border-b border-gray-150 shadow-xs">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Left: Logo & Smart User Info -->
            <div class="shrink-0 flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                    @if($user)
                        <div class="ml-4 border-l border-gray-200 pl-4 flex flex-col justify-center text-left">
                            <span class="text-sm font-bold text-gray-900 leading-tight">{{ $user->name }}</span>
                            @if($user->isAdmin())
                                <span class="text-xs text-gray-500 leading-tight mt-0.5">Administrator</span>
                            @elseif($user->isStudent())
                                <span class="text-xs text-gray-500 leading-tight mt-0.5">Student</span>
                            @else
                                <span class="text-[11px] text-indigo-600 font-bold leading-none mt-1">
                                    @php
                                        $roles = [];
                                        if (in_array('guide', $userPerms)) $roles[] = 'Guide Faculty';
                                        if (in_array('approval_faculty', $userPerms)) $roles[] = 'Approval Faculty';
                                        if (in_array('noc_authority', $userPerms)) $roles[] = 'NOC Authority';
                                        if (empty($roles)) $roles[] = 'Faculty';
                                    @endphp
                                    {{ implode(' | ', $roles) }}
                                </span>
                                @if($isMultiAuthority)
                                    <span class="text-[10px] text-emerald-600 font-extrabold leading-none mt-1">
                                        Current Dashboard: {{ $activeDashboardLabel }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    @endif
                </a>
            </div>

            <!-- Center: Dashboard Navigation (Balanced and Centered) -->
            <div class="hidden sm:flex flex-1 justify-center px-6">
                @if($isMultiAuthority)
                    <div class="flex justify-center items-center gap-12 lg:gap-16">
                        @if(in_array('guide', $userPerms))
                            <a href="{{ route('switch-dashboard', 'guide') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeDashboard === 'guide' ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('Guide Dashboard') }}
                            </a>
                        @endif
                        @if(in_array('approval_faculty', $userPerms))
                            <a href="{{ route('switch-dashboard', 'approval_faculty') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeDashboard === 'approval_faculty' ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('Approval Dashboard') }}
                            </a>
                        @endif
                        @if(in_array('noc_authority', $userPerms))
                            <a href="{{ route('switch-dashboard', 'noc_authority') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeDashboard === 'noc_authority' ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('NOC Dashboard') }}
                            </a>
                        @endif
                    </div>
                @else
                    <!-- Center Links for single authority roles that require headers (Admin / Student) -->
                    @if(auth()->user()->isAdmin())
                        <div class="flex justify-center items-center gap-12 lg:gap-16">
                            <a href="{{ route('admin.dashboard') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.dashboard') && !request()->has('tab') ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('admin.dashboard') }}#users" class="relative py-2.5 text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300">
                                {{ __('Users') }}
                            </a>
                            <a href="{{ route('admin.dashboard') }}#applications" class="relative py-2.5 text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300">
                                {{ __('Applications') }}
                            </a>
                            <a href="{{ route('admin.dashboard') }}#nocs" class="relative py-2.5 text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300">
                                {{ __('NOCs') }}
                            </a>
                        </div>
                    @elseif(auth()->user()->isStudent())
                        <div class="flex justify-center items-center gap-12 lg:gap-16">
                            <a href="{{ route('dashboard') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('student.applications.create') }}" class="relative py-2.5 text-sm font-semibold transition-all duration-200 {{ request()->routeIs('student.applications.create') ? 'text-indigo-600 font-extrabold border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900 hover:border-b-2 hover:border-gray-300' }}">
                                {{ __('New Application') }}
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Right: Profile Menu / Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center p-0.5 border-2 border-transparent rounded-full hover:border-indigo-500 focus:outline-none transition ease-in-out duration-150 cursor-pointer">
                            @if(Auth::user()->profile_photo_path)
                                <img class="h-10 w-10 md:h-12 md:w-12 rounded-full object-cover border border-gray-200 shadow-sm" src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}">
                            @else
                                <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-sm md:text-base shadow-sm">
                                    {{ Auth::user()->initials_avatar }}
                                </div>
                            @endif
                        </button>
                    </x-slot>
 
                    <x-slot name="content">
                        <!-- User Info Header -->
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <div class="font-bold text-sm text-gray-900 leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 truncate mt-0.5">{{ Auth::user()->email }}</div>

                        </div>
 
                        <!-- Switch Dashboard Section (Only for Multi-Authority Users) -->
                        @if($isMultiAuthority)
                            <div class="px-4 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                                {{ __('Switch Dashboard') }}
                            </div>
 
                            @if(in_array('guide', $userPerms))
                                <x-dropdown-link :href="route('switch-dashboard', 'guide')" class="flex items-center justify-between text-xs py-2 {{ $activeDashboard === 'guide' ? 'bg-indigo-50 text-indigo-700 font-bold' : '' }}">
                                    <span class="flex items-center font-medium">
                                        @if($activeDashboard === 'guide')
                                            <span class="text-indigo-600 font-bold mr-2">✓</span>
                                        @else
                                            <span class="w-3.5 inline-block"></span>
                                        @endif
                                        {{ __('Guide Dashboard') }}
                                    </span>
                                </x-dropdown-link>
                            @endif
 
                            @if(in_array('approval_faculty', $userPerms))
                                <x-dropdown-link :href="route('switch-dashboard', 'approval_faculty')" class="flex items-center justify-between text-xs py-2 {{ $activeDashboard === 'approval_faculty' ? 'bg-indigo-50 text-indigo-700 font-bold' : '' }}">
                                    <span class="flex items-center font-medium">
                                        @if($activeDashboard === 'approval_faculty')
                                            <span class="text-indigo-600 font-bold mr-2">✓</span>
                                        @else
                                            <span class="w-3.5 inline-block"></span>
                                        @endif
                                        {{ __('Approval Dashboard') }}
                                    </span>
                                </x-dropdown-link>
                            @endif
 
                            @if(in_array('noc_authority', $userPerms))
                                <x-dropdown-link :href="route('switch-dashboard', 'noc_authority')" class="flex items-center justify-between text-xs py-2 {{ $activeDashboard === 'noc_authority' ? 'bg-indigo-50 text-indigo-700 font-bold' : '' }}">
                                    <span class="flex items-center font-medium">
                                        @if($activeDashboard === 'noc_authority')
                                            <span class="text-indigo-600 font-bold mr-2">✓</span>
                                        @else
                                            <span class="w-3.5 inline-block"></span>
                                        @endif
                                        {{ __('NOC Dashboard') }}
                                    </span>
                                </x-dropdown-link>
                            @endif
                            
                            <div class="border-t border-gray-100"></div>
                        @endif

                        <!-- Settings & Password Links -->
                        <x-dropdown-link :href="route('profile.settings')" class="text-xs font-semibold py-2">
                            <i class="fas fa-cog text-gray-400 mr-2"></i> {{ __('Profile Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.change-password')" class="text-xs font-semibold py-2">
                            <i class="fas fa-key text-gray-400 mr-2"></i> {{ __('Change Password') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-xs font-semibold py-2 text-rose-600 hover:text-rose-700">
                                <i class="fas fa-sign-out-alt text-rose-400 mr-2"></i> {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Only) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2.5 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition duration-150 ease-in-out cursor-pointer">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile Menu) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gray-50 border-t border-gray-150">
        <div class="pt-2 pb-3 space-y-1">
            @if($isMultiAuthority)
                <!-- Centers Navigation Tabs for Mobile -->
                <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ __('Dashboard Navigation') }}
                </div>
                @if(in_array('guide', $userPerms))
                    <x-responsive-nav-link :href="route('switch-dashboard', 'guide')" :active="$activeDashboard === 'guide'">
                        {{ __('Guide Dashboard') }}
                    </x-responsive-nav-link>
                @endif
                @if(in_array('approval_faculty', $userPerms))
                    <x-responsive-nav-link :href="route('switch-dashboard', 'approval_faculty')" :active="$activeDashboard === 'approval_faculty'">
                        {{ __('Approval Dashboard') }}
                    </x-responsive-nav-link>
                @endif
                @if(in_array('noc_authority', $userPerms))
                    <x-responsive-nav-link :href="route('switch-dashboard', 'noc_authority')" :active="$activeDashboard === 'noc_authority'">
                        {{ __('NOC Dashboard') }}
                    </x-responsive-nav-link>
                @endif
            @else
                <!-- Navigation links for single roles (Admin / Student / Faculty) -->
                @if(auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard') && !request()->has('tab')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.dashboard') . '#users'" :active="false">
                        {{ __('Users') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.dashboard') . '#applications'" :active="false">
                        {{ __('Applications') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.dashboard') . '#nocs'" :active="false">
                        {{ __('NOCs') }}
                    </x-responsive-nav-link>
                @elseif(auth()->user()->isStudent())
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('student.applications.create')" :active="request()->routeIs('student.applications.create')">
                        {{ __('New Application') }}
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-2 border-t border-gray-200">
            <div class="px-4 flex items-center mb-3">
                @if(Auth::user()->profile_photo_path)
                    <img class="h-10 w-10 rounded-full object-cover mr-3 border border-gray-200" src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}">
                @else
                    <div class="h-10 w-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-base mr-3 shadow-xs">
                        {{ Auth::user()->initials_avatar }}
                    </div>
                @endif
                <div>
                    <div class="font-bold text-sm text-gray-800 leading-none">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-gray-500 mt-1 leading-none">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="space-y-1">
                @if($isMultiAuthority)
                    <!-- Switch Dashboard in Mobile Options -->
                    <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-gray-100">
                        {{ __('Switch Dashboard Workspace') }}
                    </div>
                    @if(in_array('guide', $userPerms))
                        <x-responsive-nav-link :href="route('switch-dashboard', 'guide')" :active="$activeDashboard === 'guide'">
                            <span class="flex items-center">
                                @if($activeDashboard === 'guide')
                                    <span class="text-emerald-600 font-bold mr-1.5">✓</span>
                                @endif
                                {{ __('Guide Dashboard') }}
                            </span>
                        </x-responsive-nav-link>
                    @endif
                    @if(in_array('approval_faculty', $userPerms))
                        <x-responsive-nav-link :href="route('switch-dashboard', 'approval_faculty')" :active="$activeDashboard === 'approval_faculty'">
                            <span class="flex items-center">
                                @if($activeDashboard === 'approval_faculty')
                                    <span class="text-emerald-600 font-bold mr-1.5">✓</span>
                                @endif
                                {{ __('Approval Dashboard') }}
                            </span>
                        </x-responsive-nav-link>
                    @endif
                    @if(in_array('noc_authority', $userPerms))
                        <x-responsive-nav-link :href="route('switch-dashboard', 'noc_authority')" :active="$activeDashboard === 'noc_authority'">
                            <span class="flex items-center">
                                @if($activeDashboard === 'noc_authority')
                                    <span class="text-emerald-600 font-bold mr-1.5">✓</span>
                                @endif
                                {{ __('NOC Dashboard') }}
                            </span>
                        </x-responsive-nav-link>
                    @endif
                    <div class="border-t border-gray-200 my-1"></div>
                @endif

                <x-responsive-nav-link :href="route('profile.settings')" :active="request()->routeIs('profile.settings')">
                    <i class="fas fa-cog mr-2"></i> {{ __('Profile Settings') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.change-password')" :active="request()->routeIs('profile.change-password')">
                    <i class="fas fa-key mr-2"></i> {{ __('Change Password') }}
                </x-responsive-nav-link>

                <div class="border-t border-gray-250 my-1"></div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();"
                            class="text-rose-600 font-semibold hover:bg-rose-50">
                        <i class="fas fa-sign-out-alt mr-2 text-rose-500"></i> {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
