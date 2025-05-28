<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="">INVENTORY</a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="">INV</a>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-header">Dashboard</li>
        <li class="{{ request()->is('/') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fire"></i> <span>Home</span></a>
        </li>
        <li class="menu-header">Master Data</li>
        <li class="{{ request()->is('categories*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('categories.index') }}"><i class="fas fa-list"></i> <span>Data Kategori</span></a>
        </li>
        <li class="{{ request()->is('bahan_baku*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('bahan_baku.index') }}"><i class="fas fa-box"></i> <span>Data Bahan Baku</span></a>
        </li>
        <li class="menu-header">Inventory</li>
        <li class="{{ request()->is('inventory') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-boxes"></i> <span>Simple Inventory</span></a>
        </li>
        <li class="{{ request()->is('per-produk*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('inventory.perProduk') }}"><i class="fas fa-box-open"></i> <span>Per bahan Baku</span></a>
        </li>
        <li class="dropdown {{ request()->is('detail_inventory*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-warehouse"></i> <span>Detail Inventory</span></a>
            <ul class="dropdown-menu">
                @foreach ($kategori as $item)
                    <li class="{{ request()->is('detail_inventory/'.$item->id) ? 'active' : '' }}"><a class="nav-link category-link" href="{{ route('detail_inventory.index', $item->id)  }}">{{ $item->nama }}</a></li>
                @endforeach
            </ul>
          </li>
          <li class="{{ request()->is('inventory-history*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('inventory.history') }}"><i class="fas fa-history"></i> <span>Inventory History</span></a>
        </li>
        <li class="menu-header">Kelola Stok</li>
        <li class="{{ request()->is('stok_masuk*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('stok_masuk.index') }}"><i class="fas fa-plus"></i> <span>Stok Masuk</span></a>
        </li>
        <li class="{{ request()->is('stok_keluar*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('stok_keluar.index') }}"><i class="fas fa-minus"></i> <span>Stok Keluar</span></a>
        </li>
        <li class="{{ request()->is('stock-opname*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('stockOpname.index') }}"><i class="fas fa-sticky-note"></i> <span>Stok Opname</span></a>
        </li>
        <li class="menu-header">Laporan</li>
        <li class="{{ request()->is('laporan-stok-masuk*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('laporan.stokMasuk') }}"><i class="fas fa-plus"></i> <span>Stok Masuk</span></a>
        </li>
        <li class="{{ request()->is('laporan-stok-keluar*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('laporan.stokKeluar') }}"><i class="fas fa-minus"></i> <span>Stok Keluar</span></a>
        </li>
    </ul>
</aside>
