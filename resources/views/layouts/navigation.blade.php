<nav x-data="{ open: false, profileMenu: false }" class="bg-gradient-to-b from-blue-900 to-indigo-900 w-64 min-h-screen flex flex-col">
    <div class="p-4 border-b border-indigo-800/50">
        <a href="#" class="flex items-center">
            <i class="fas fa-wrench text-blue-300 text-2xl mr-2"></i>
            <span class="text-blue-100 text-lg font-bold">Bengkel App</span>
        </a>
    </div>
 
    <div class="flex-1 py-4">
        <a href="{{ route('dashboard') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-800/50 text-white' : '' }}">
            <i class="fas fa-home mr-3"></i>Dashboard
         </a>
       
        <div class="px-4 py-2 text-blue-400 text-xs font-bold uppercase tracking-wider">Master Data</div>
        <a href="{{ route('satuan.index') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200 {{ request()->routeIs('satuan.index') ? 'bg-blue-800/50 text-white' : '' }}">
            <i class="fas fa-ruler mr-3"></i>Satuan
         </a>
         <a href="{{ route('kategori.index') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200 {{ request()->routeIs('kategori.index') ? 'bg-blue-800/50 text-white' : '' }}">
            <i class="fas fa-tags mr-3"></i>Kategori 
         </a>
         <a href="{{ route('barang.index') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200 {{ request()->routeIs('barang.index') ? 'bg-blue-800/50 text-white' : '' }}">
            <i class="fas fa-box mr-3"></i>Barang
         </a>
 
        <div class="px-4 py-2 text-blue-400 text-xs font-bold uppercase tracking-wider mt-4">Transaksi</div>
        <a href="{{ route('barang-masuk.index') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200 {{ request()->routeIs('barang-masuk.index') ? 'bg-blue-800/50 text-white' : '' }}">
            <i class="fas fa-arrow-right mr-3"></i>Barang Masuk
        </a>
        <a href="{{ route('barang-keluar.index') }}" class="block px-4 py-3 text-blue-300 hover:bg-blue-800/50 hover:text-white rounded-lg mx-2 transition-all duration-200">
            <i class="fas fa-arrow-left mr-3"></i>Barang Keluar
        </a>
    </div>
 
    <div class="border-t border-blue-800/50 w-full">
        <div class="p-4" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full text-blue-300 hover:text-white p-2 rounded-lg transition-all duration-200 bg-blue-800/30">
                <i class="fas fa-user-circle text-2xl mr-3"></i>
                <div class="text-lg">{{ Auth::user()->name }}</div>
                <i class="fas fa-chevron-down ml-auto transition-transform duration-200" :class="{'rotate-180': open}"></i>
            </button>
            
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
                 class="mt-2 py-2 bg-blue-800/30 rounded-lg">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-blue-300 hover:text-white hover:bg-blue-700/50">
                    <i class="fas fa-user-edit mr-2"></i>Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-blue-300 hover:text-white hover:bg-blue-700/50">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
 </nav>