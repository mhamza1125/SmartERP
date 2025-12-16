<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>{{ ucfirst(auth()->user()->role->name) }} - SmartERP</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ URL::asset('assets/css/app.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/lightgallery/dist/css/lightgallery.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/summernote/summernote-bs4.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/select2/dist/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/datatables/datatables.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/pretty-checkbox/pretty-checkbox.min.css')}}">
  <link rel="stylesheet" href="{{ URL::asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/css/components.css') }}">
  
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{ URL::asset('assets/css/custom.css') }}">
  <link rel='shortcut icon' type='image/x-icon' href='{{ URL::asset('assets/img/favicon.ico') }}' />
  {{-- Custom JS --}}
  <script src="{{ URL::asset('assets/bundles/jquery-3.6.0.min.js') }}"></script>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a></li>
          </ul>
        </div>
        <ul class="navbar-nav navbar-right">
          <li class="dropdown"><a href="#" data-toggle="dropdown"
              class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image" src="{{ URL::asset('assets/img/user.jpg') }}"
                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title">Hello {{ auth()->user()->name }}</div>
              <a href="{{ route('user') }}" class="dropdown-item has-icon"> <i class="far fa-user"></i> Profile </a>
              <div class="dropdown-divider"></div>
              <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="dropdown-item has-icon text-danger" style="background: none; border: none; cursor: pointer; width: 100%; text-align: left;">
                  <i class="fas fa-sign-out-alt ml-1">&nbsp Logout</i> 
                </button>
              </form>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}"> <img alt="image" src="{{ URL::asset('assets/img/logo.png') }}" class="header-logo" /> <span
                class="logo-name">SmartERP</span>
            </a>
          </div>
          <ul class="sidebar-menu">
            {{-- <li class="menu-header">My Work</li> --}}
            <li class="dropdown active">
              <a href="{{ route('dashboard') }}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
            </li>

            {{-- Stock / Issuance Section --}}
            @can('stocks_access', App\Models\Stock::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Stock / Issuance</span></a>
              <ul class="dropdown-menu">
                @can('stocks_access', App\Models\Stock::class)
                  <li><a class="nav-link" href="{{ route('stock') }}">Stock</a></li>
                @endcan
                @can('access', App\Models\Stock::class)
                  <li><a class="nav-link" href="{{ route('ptc') }}">PTC</a></li>
                  <li><a class="nav-link" href="{{ route('issue') }}">Issuance</a></li>
                  <li><a class="nav-link" href="{{ route('receiveIssue') }}">Receive</a></li>
                  <li><a class="nav-link" href="{{ route('igroup') }}">Group / Lot</a></li>
                @endcan
              </ul>
            </li>
            @endcan

            {{-- Purchase Orders Section --}}
            @can('access', App\Models\Purchase::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Purchase Orders</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('purchase') }}">Purchase</a></li>
                <li><a class="nav-link" href="{{ route('receive') }}">Receive</a></li>
                <li><a class="nav-link" href="{{ route('return') }}">Return</a></li>
              </ul>
            </li>
            @endcan

            {{-- Transactions Section --}}
            @can('access', App\Models\Transaction::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Transactions</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('transaction') }}">All Transaction </a></li>
                <li><a class="nav-link" href="{{ route('ePayment') }}">Employee</a></li>
                <li><a class="nav-link" href="{{ route('vPayment') }}">Vendor</a></li>
                <li><a class="nav-link" href="{{ route('cPayment') }}">Contractor</a></li>
                <li><a class="nav-link" href="{{ route('expense') }}">Expense</a></li>
                <li><a class="nav-link" href="{{ route('oPayment') }}">Customer Order</a></li>
                <li><a class="nav-link" href="{{ route('transaction.addGeneralVoucher') }}">General Voucher</a></li>
              </ul>
            </li>
            @endcan

            @canany(['access'], [App\Models\Bank::class, App\Models\Asset::class])
            <li class="dropdown">
                <a href="#" class="menu-toggle nav-link has-dropdown">
                    <i data-feather="briefcase"></i><span>Bank / Cash</span>
                </a>

                <ul class="dropdown-menu">
                    {{-- Bank / Cash Section --}}
                    @can('access', App\Models\Bank::class)
                        {{-- <li><a class="nav-link" href="{{ route('bank') }}">Bank Account</a></li> --}}
                        <li><a class="nav-link" href="{{ route('bankBalance') }}">Bank Balance</a></li>
                        <li><a class="nav-link" href="{{ route('cashBalance') }}">Cash Balance</a></li>
                    @endcan

                    {{-- Assets Section --}}
                    @can('access', App\Models\Asset::class)
                        <li><a class="nav-link" href="{{ route('asset') }}">Assets</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            {{-- Machines Section --}}
            @can('access', App\Models\Machine::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Machines</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('machine') }}">Machine</a></li>
                <li><a class="nav-link" href="{{ route('missue') }}">Material Issuance</a></li>
              </ul>
            </li>
            @endcan

            {{-- Customer Orders Section --}}
            @can('access', App\Models\Order::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Customer Orders</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('order') }}">Orders</a></li>
                @can('access', App\Models\Delivery::class)
                  <li><a class="nav-link" href="{{ route('delivery') }}">Deliveries</a></li>
                @endcan
              </ul>
            </li>
            @endcan
            {{-- People Management Section --}}
            @if(Gate::allows('access', App\Models\Customer::class) || Gate::allows('access', App\Models\Employee::class) || Gate::allows('access', App\Models\Vendor::class) || Gate::allows('contractors_access', App\Models\Vendor::class))
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>People Management</span></a>
              <ul class="dropdown-menu">
                @can('access', App\Models\Customer::class)
                  <li><a class="nav-link" href="{{ route('customer') }}">Customer</a></li>
                @endcan
                @can('access', App\Models\Employee::class)
                  <li><a class="nav-link" href="{{ route('employee') }}">Employee</a></li>
                @endcan
                @can('access', App\Models\Vendor::class)
                  <li><a class="nav-link" href="{{ route('vendor') }}">Vendor</a></li>
                @endcan
                @can('contractors_access', App\Models\Vendor::class)
                  <li><a class="nav-link" href="{{ route('contractor') }}">Contractor</a></li>
                @endcan
              </ul>
            </li>
            @endif

            {{-- Products Section --}}
            @can('access', App\Models\Product::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Products</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('product') }}">Product</a></li>
                <li><a class="nav-link" href="{{ route('productMaterial') }}">Product Material</a></li>
                <li><a class="nav-link" href="{{ route('productCost') }}">Product Costing / Wages</a></li>
              </ul>
            </li>
            @endcan

            {{-- Materials Section --}}
            @can('access', App\Models\Material::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                data-feather="briefcase"></i><span>Materials</span></a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link" href="{{ route('material') }}">Materials</a></li>
                  <li><a class="nav-link" href="{{ route('mprocess') }}">Material Processing</a></li>
              </ul>
            </li>
            @endcan

            {{-- Attendance / Payroll Section --}}
            @if (
                auth()->user()->can('access', App\Models\Attendance::class) ||
                auth()->user()->can('access', App\Models\Transaction::class)
            )
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Attendance / Payroll</span></a>
              <ul class="dropdown-menu">
                @can('access', App\Models\Transaction::class)
                  <li><a class="nav-link" href="{{ route('payroll.index') }}">Monthly Payroll</a></li>
                  <li><a class="nav-link" href="{{ route('wages') }}">Work Wages</a></li>
                @endcan
                @can('access', App\Models\Attendance::class)
                  <li><a class="nav-link" href="{{ route('attendance') }}">Attendance</a></li>
                  <li><a class="nav-link" href="{{ route('attendance.summary') }}">Salary Calculation</a></li>
                @endcan
              </ul>
            </li>
            @endif

            {{-- Reports Section --}}
            @can('reports_access', App\Models\User::class)
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Reports</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('stock.daily') }}">Daily Issuance</a></li>
                <li><a class="nav-link" href="{{ route('rstock.daily') }}">Daily Receiving</a></li>
                <li><a class="nav-link" href="{{ route('material.detail') }}">Material Ledger</a></li>
              </ul>
            </li>
            @endcan

            {{-- Settings Section --}}
            @can('access', App\Models\User::class)
            <li class="dropdown mb-4">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Settings</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('user') }}">Users</a></li>
                <li><a class="nav-link" href="{{ route('role') }}">Roles & Permissions</a></li>
                <li><a class="nav-link" href="{{ route('company') }}">Company</a></li>
                <li><a class="nav-link" href="{{ route('head') }}">Heads</a></li>
                <li><a class="nav-link" href="{{ route('category') }}">Category</a></li>
                <li><a class="nav-link" href="{{ route('workTime') }}">Work Hours</a></li>
                <li><a class="nav-link" href="{{ route('workHoliday') }}">Non Working Days</a></li>
              </ul>
            </li>
            @endcan
            
            @if(auth()->user()->role == 'admin')
              {{-- Data to Display --}}
            @endif
          </ul>
        </aside>
      </div>
      
      <!-- Main Content -->
      <div class="main-content">
        @if (session('success'))
          <input type="hidden" id="successMessage" value="{{ session('success') }}">
        @elseif (session('fails'))
          <input type="hidden" id="errorMessage" value="{{ session('fails') }}">
        @elseif ($errors->any())
          <input type="hidden" id="errorMessage" value="{{ implode(', ', $errors->all()) }}">
        @endif

        @yield('content')
        {{-- <div class="settingSidebar">
          <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i>
          </a>
          <div class="settingSidebar-body ps-container ps-theme-default">
            <div class=" fade show active">
              <div class="setting-panel-header">Setting Panel
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Select Layout</h6>
                <div class="selectgroup layout-color w-50">
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                    <span class="selectgroup-button">Light</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                    <span class="selectgroup-button">Dark</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar" checked>
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Color Theme</h6>
                <div class="theme-setting-options">
                  <ul class="choose-theme list-unstyled mb-0">
                    <li title="white" class="active">
                      <div class="white"></div>
                    </li>
                    <li title="cyan">
                      <div class="cyan"></div>
                    </li>
                    <li title="black">
                      <div class="black"></div>
                    </li>
                    <li title="purple">
                      <div class="purple"></div>
                    </li>
                    <li title="orange">
                      <div class="orange"></div>
                    </li>
                    <li title="green">
                      <div class="green"></div>
                    </li>
                    <li title="red">
                      <div class="red"></div>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="mini_sidebar_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Mini Sidebar</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="sticky_header_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Sticky Header</span>
                  </label>
                </div>
              </div>
              <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                  <i class="fas fa-undo"></i> Restore Default
                </a>
              </div>
            </div>
          </div>
        </div> --}}
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          <a href="#">Company XYZ</a>
        </div>
        <div class="footer-right">
          Copyright &copy; Designed & Developed by <a href="#">Core Web Solutions</a> {{date("Y")}}
        </div>
      </footer>
    </div>
  </div>
  <!-- General JS Scripts -->
  <script src="{{ URL::asset('assets/js/app.min.js') }}"></script>
  <!-- JS Libraies -->
  <script src="{{ URL::asset('assets/bundles/izitoast/js/iziToast.min.js') }}"></script>
  {{-- <script src="{{ URL::asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script> --}}
  <script src="{{ URL::asset('assets/bundles/sweetalert/sweetalert.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/select2/dist/js/select2.full.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/summernote/summernote-bs4.js') }}"></script>
  <script src="{{ URL::asset('assets/js/page/toastr.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/datatables.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/jszip.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
  <script src="{{ URL::asset('assets/js/page/datatables.js') }}"></script>
  <script src="{{ URL::asset('assets/bundles/lightgallery/dist/js/lightgallery-all.js') }}"></script>
  <script src="{{ URL::asset('assets/js/page/light-gallery.js') }}"></script>
  <script src="{{ URL::asset('assets/js/page/index.js') }}"></script>
  <!-- Template JS File -->
  <script src="{{ URL::asset('assets/js/scripts.js') }}"></script>
  <!-- Print JS File -->
  <script src="{{ URL::asset('assets/js/print.js') }}"></script>
  <!-- Custom JS File -->
  <script src="{{ URL::asset('assets/js/custom.js') }}"></script>

  @stack('scripts')
</body>
</html>