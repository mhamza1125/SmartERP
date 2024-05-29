<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>{{ ucfirst(auth()->user()->role) }} - SmartERP</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ URL::asset('assets/css/app.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/lightgallery/dist/css/lightgallery.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/izitoast/css/iziToast.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/summernote/summernote-bs4.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/bootstrap-daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/select2/dist/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/datatables/datatables.min.css') }}">
  <link rel="stylesheet" href="{{ URL::asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
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
          <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
              class="nav-link nav-link-lg message-toggle"><i data-feather="mail"></i>
              <span class="badge headerBadge1">
                6 </span> </a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
              <div class="dropdown-header">
                Messages
                <div class="float-right">
                  <a href="#">Mark All As Read</a>
                </div>
              </div>
              <div class="dropdown-list-content dropdown-list-message">
                <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white"> <img alt="image" src="{{ URL::asset('assets/img/users/user-1.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">John
                      Deo</span>
                    <span class="time messege-text">Please check your mail !!</span>
                    <span class="time">2 Min Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white">
                    <img alt="image" src="{{ URL::asset('assets/img/users/user-2.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">Sarah
                      Smith</span> <span class="time messege-text">Request for leave
                      application</span>
                    <span class="time">5 Min Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white">
                    <img alt="image" src="{{ URL::asset('assets/img/users/user-5.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">Jacob
                      Ryan</span> <span class="time messege-text">Your payment invoice is
                      generated.</span> <span class="time">12 Min Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white">
                    <img alt="image" src="{{ URL::asset('assets/img/users/user-4.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">Lina
                      Smith</span> <span class="time messege-text">hii John, I have upload
                      doc
                      related to task.</span> <span class="time">30
                      Min Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white">
                    <img alt="image" src="{{ URL::asset('assets/img/users/user-3.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">Jalpa
                      Joshi</span> <span class="time messege-text">Please do as specify.
                      Let me
                      know if you have any query.</span> <span class="time">1
                      Days Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-avatar text-white">
                    <img alt="image" src="{{ URL::asset('assets/img/users/user-2.png') }}" class="rounded-circle">
                  </span> <span class="dropdown-item-desc"> <span class="message-user">Sarah
                      Smith</span> <span class="time messege-text">Client Requirements</span>
                    <span class="time">2 Days Ago</span>
                  </span>
                </a>
              </div>
              <div class="dropdown-footer text-center">
                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
              </div>
            </div>
          </li>
          <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
              class="nav-link notification-toggle nav-link-lg"><i data-feather="bell" class="bell"></i>
            </a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
              <div class="dropdown-header">
                Notifications
                <div class="float-right">
                  <a href="#">Mark All As Read</a>
                </div>
              </div>
              <div class="dropdown-list-content dropdown-list-icons">
                <a href="#" class="dropdown-item dropdown-item-unread"> <span
                    class="dropdown-item-icon bg-primary text-white"> <i class="fas fa-code"></i>
                  </span> <span class="dropdown-item-desc"> Template update is
                    available now! <span class="time">2 Min
                      Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-icon bg-info text-white"> <i class="far fa-user"></i>
                  </span> <span class="dropdown-item-desc"> <b>You</b> and <b>Dedik
                      Sugiharto</b> are now friends <span class="time">10 Hours
                      Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-icon bg-success text-white"> <i class="fas fa-check"></i>
                  </span> <span class="dropdown-item-desc"> <b>Kusnaedi</b> has
                    moved task <b>Fix bug header</b> to <b>Done</b> <span class="time">12 Hours Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-icon bg-danger text-white"> <i class="fas fa-exclamation-triangle"></i>
                  </span> <span class="dropdown-item-desc"> Low disk space. Let's
                    clean it! <span class="time">17 Hours Ago</span>
                  </span>
                </a> <a href="#" class="dropdown-item"> <span class="dropdown-item-icon bg-info text-white"> <i class="fas fa-bell"></i>
                  </span> <span class="dropdown-item-desc"> Welcome to SmartERP
                    template! <span class="time">Yesterday</span>
                  </span>
                </a>
              </div>
              <div class="dropdown-footer text-center">
                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
              </div>
            </div>
          </li>
          <li class="dropdown"><a href="#" data-toggle="dropdown"
              class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image" src="{{ URL::asset('assets/img/user.png') }}"
                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title">Hello Sarah Smith</div>
              <a href="profile.html" class="dropdown-item has-icon"> <i class="far fa-user"></i> Profile
              </a> <a href="timeline.html" class="dropdown-item has-icon"> <i class="fas fa-bolt"></i>
                Activities
              </a> <a href="#" class="dropdown-item has-icon"> <i class="fas fa-cog"></i>
                Settings
              </a>
              <div class="dropdown-divider"></div>
              <a href="auth-login.html" class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                Logout
              </a>
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
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Stock / Issuance</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('stock') }}">Available</a></li>
                <li><a class="nav-link" href="{{ route('issue') }}">Issue</a></li>
                <li><a class="nav-link" href="{{ route('receiveIssue') }}">Receive</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Purchase Orders</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('purchase') }}">Purchase</a></li>
                <li><a class="nav-link" href="{{ route('receive') }}">Receive</a></li>
                <li><a class="nav-link" href="{{ route('return') }}">Return</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Transactions</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('transaction') }}">All Transaction </a></li>
                <li><a class="nav-link" href="{{ route('ePayment') }}">Employee</a></li>
                <li><a class="nav-link" href="{{ route('vPayment') }}">Vendor / Contractor</a></li>
                <li><a class="nav-link" href="{{ route('expense') }}">Expense</a></li>
                <li><a class="nav-link" href="{{ route('oPayment') }}">Customer Order</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Bank / Cash</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('bank') }}">Bank Account</a></li>
                <li><a class="nav-link" href="{{ route('bankBalance') }}">Bank Balance</a></li>
                <li><a class="nav-link" href="{{ route('cashBalance') }}">Cash Balance</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Machines</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('machine') }}">Machine</a></li>
                <li><a class="nav-link" href="{{ route('missue') }}">Material Issuance</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Customer Orders</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('order') }}">Orders</a></li>
                <li><a class="nav-link" href="{{ route('delivery') }}">Deliveries</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>People Management</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('customer') }}">Customer</a></li>
                <li><a class="nav-link" href="{{ route('employee') }}">Employee</a></li>
                <li><a class="nav-link" href="{{ route('vendor') }}">Vendor</a></li>
                <li><a class="nav-link" href="{{ route('contractor') }}">Contractor</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Products</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('product') }}">Product</a></li>
                <li><a class="nav-link" href="{{ route('productMaterial') }}">Product Material</a></li>
                <li><a class="nav-link" href="{{ route('productCost') }}">Product Costing</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                data-feather="briefcase"></i><span>Materials</span></a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link" href="{{ route('material') }}">Materials</a></li>
                  <li><a class="nav-link" href="{{ route('mprocess') }}">Material Processing</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Reports</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('wages') }}">Work Wages</a></li>
                <li><a class="nav-link" href="{{ route('stock.daily') }}">Daily Issuance</a></li>
                <li><a class="nav-link" href="{{ route('rstock.daily') }}">Daily Receiving</a></li>
                <li><a class="nav-link" href="{{ route('material.detail') }}">Material Ledger</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="menu-toggle nav-link has-dropdown"><i
                  data-feather="briefcase"></i><span>Settings</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ route('head') }}">Heads</a></li>
                <li><a class="nav-link" href="{{ route('category') }}">Category</a></li>
              </ul>
            </li>
            
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
        @endif
        @yield('content')
        <div class="settingSidebar">
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
        </div>
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          <a href="#">Palls Enterprises</a>
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
  <!-- Custom JS File -->
  <script src="{{ URL::asset('assets/js/custom.js') }}"></script>
</body>
</html>