<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- BRAND -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="">
        <div class="sidebar-brand-icon">
            <i class="fas fa-medal"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            Yaoundé <sup>2026</sup>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

  @php
    $user = auth('championat')->user();
@endphp

<!-- DASHBOARD (TOUS) -->
@php
    $dashboardRoute = match(auth('championat')->user()->role) {
        'super-admin'      => 'dashboard.super_admin',
        'admin-local'      => 'dashboard.admin_local',
        'admin-federation' => 'dashboard.admin_federation',
        default            => 'home',
    };
@endphp

<li class="nav-item">
    <a class="nav-link" href="{{ route($dashboardRoute) }}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</li>


<hr class="sidebar-divider">

<!-- ================= GESTION COMPÉTITION ================= -->
<div class="sidebar-heading">
    Gestion Compétition
</div>

<!-- DÉLÉGATIONS (SUPER ADMIN + ADMIN LOCAL) -->
<!-- ajouter une view pour que les admin de fed voient quel sont les delegations deja present -->
@if(in_array($user->role, ['super-admin', 'admin-local']))
<li class="nav-item">
    <a class="nav-link" href="/delegations">
        <i class="fas fa-fw fa-flag"></i>
        <span>Délégations</span>
    </a>
</li>
@endif

<!-- INSCRIPTIONS (ADMIN FÉDÉRATION SEULEMENT) -->




<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRegistrations">
        <i class="fas fa-fw fa-clipboard-check"></i>
        <span>Inscriptions</span>
    </a>
    <div id="collapseRegistrations" class="collapse">
        <div class="bg-white py-2 collapse-inner rounded">
          
            @if(in_array($user->role, ['super-admin', 'admin-local'])) 
                <a class="collapse-item" href="/admin/registrations">Gestion des Inscriptions</a>
            @endif
            @if($user->role === 'admin-federation')
                <a class="collapse-item" href="/provisional-registration">Provisional Registration</a>
                <a class="collapse-item" href="/definitive-registration">Definitive Registration</a>
                <a class="collapse-item" href="/nominative-registration">Nominative Registration</a>
            @endif
        </div>
    </div>
</li>


<!-- ACCOMMODATION (ADMIN LOCAL + ADMIN FÉDÉRATION) -->
@if(in_array($user->role, ['super-admin','admin-local', 'admin-federation']))
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccommodation">
        <i class="fas fa-fw fa-hotel"></i>
        <span>Accommodation</span>
    </a>
    <div id="collapseAccommodation" class="collapse">
        <div class="bg-white py-2 collapse-inner rounded">
            @if(in_array($user->role, ['super-admin','admin-local']))
                <a class="collapse-item" href="/accommodation">Accommodation</a>
                <a class="collapse-item" href="/hotels">Gestion Hôtels</a>
                <a class="collapse-item" href="/reservations">Gestion Réservations</a>
            @endif
            @if($user->role === 'admin-federation')
                <a class="collapse-item" href="/federation/hotels">Hotels</a>
                <a class="collapse-item" href="/reservations">Reservations</a>
            @endif
        </div>
    </div>
</li>
@endif

<!-- PAIEMENTS (ADMIN LOCAL + ADMIN FÉDÉRATION) -->
@if($user->role === 'super-admin')
<li class="nav-item">
    <a class="nav-link" href="/payments">
        <i class="fas fa-fw fa-credit-card"></i>
        <span>Paiements</span>
    </a>
</li>
@endif

<!-- ACCRÉDITATIONS (ADMIN LOCAL SEULEMENT) -->
@if(in_array($user->role, ['super-admin', 'admin-local']))
<li class="nav-item">
    <a class="nav-link" href="/admin/accreditations">
        <i class="fas fa-fw fa-id-badge"></i>
        <span>Accréditations</span>
    </a>
</li>
@endif



<!-- ================= ADMINISTRATION ================= -->
 @if(in_array($user->role, ['super-admin', 'admin-local']))
 <hr class="sidebar-divider">
<div class="sidebar-heading">
    Administration
</div>

<!-- UTILISATEURS & RÔLES (SUPER ADMIN + ADMIN LOCAL) -->

<li class="nav-item">
    <a class="nav-link" href="/role_inscription">
        <i class="fas fa-fw fa-users-cog"></i>
        <span>Utilisateurs & Rôles</span>
    </a>
</li>


<!-- STATISTIQUES (SUPER ADMIN + ADMIN LOCAL) -->

<li class="nav-item">
    <a class="nav-link" href="/statistics">
        <i class="fas fa-fw fa-chart-line"></i>
        <span>Statistiques</span>
    </a>
</li>
@endif

<!-- PARAMÈTRES (SUPER ADMIN SEULEMENT) -->
@if($user->role === 'super-admin')
<li class="nav-item">
    <a class="nav-link" href="/settings">
        <i class="fas fa-fw fa-cogs"></i>
        <span>Paramètres</span>
    </a>
</li>
@endif


    <hr class="sidebar-divider d-none d-md-block">

    <!-- SIDEBAR TOGGLER -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- FOOTER MESSAGE -->
    <div class="sidebar-card d-none d-lg-flex">
        <p class="text-center mb-2">
            <strong>CAMERGYM 2026</strong><br>
            African Artistic Gymnastics Championships
        </p>
    </div>

</ul>

