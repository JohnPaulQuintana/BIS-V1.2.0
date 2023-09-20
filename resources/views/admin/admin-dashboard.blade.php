<!doctype html>
<html lang="en">

<head>
    <!-- Sweet Alert-->
    <link href="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    @yield('header-links')

</head>

<body data-topbar="dark">

    @include('admin.loaderPage.preloader')

    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

    <!-- Begin page -->
    <div id="layout-wrapper">


        @include('admin.body.header')

        <!-- ========== Left Sidebar Start ========== -->
        @include('admin.body.sidebar')
        <!-- Left Sidebar End -->



        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            @yield('admin-dashboard')
            <!-- End Page-content -->


            @include('admin.attendance.attendance')
            @include('admin.body.footer')

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    {{-- <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title d-flex align-items-center px-3 py-4">

                <h5 class="m-0 me-2">Settings</h5>

                <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                    <i class="mdi mdi-close noti-icon"></i>
                </a>
            </div>

            <!-- Settings -->
            <hr class="mt-0" />
            <h6 class="text-center mb-0">Choose Layouts</h6>

            <div class="p-4">
                <div class="mb-2">
                    <img src=" {{ asset('backend/assets/images/layouts/layout-1.jpg') }}"
                        class="img-fluid img-thumbnail" alt="layout-1">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>

                <div class="mb-2">
                    <img src=" {{ asset('backend/assets/images/layouts/layout-2.jpg') }}"
                        class="img-fluid img-thumbnail" alt="layout-2">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch"
                        data-bsStyle=" {{ asset('backend/assets/css/bootstrap-dark.min.css') }}"
                        data-appStyle=" {{ asset('backend/assets/css/app-dark.min.css') }}">
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>

                <div class="mb-2">
                    <img src=" {{ asset('backend/assets/images/layouts/layout-3.jpg') }}"
                        class="img-fluid img-thumbnail" alt="layout-3">
                </div>
                <div class="form-check form-switch mb-5">
                    <input class="form-check-input theme-choice" type="checkbox" id="rtl-mode-switch"
                        data-appStyle=" {{ asset('backend/assets/css/app-rtl.min.css') }}">
                    <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                </div>


            </div>

        </div> <!-- end slimscroll-menu-->
    </div> --}}
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    @yield('footer-script')
    {{-- available to all --}}
    <script src="{{ asset('html5-qrcodes/html5-qrcode.min.js') }}"></script>
    <script src="{{ asset('html5-qrcodes/scan.js') }}"></script>

    <!-- Sweet Alerts js -->
    <script src="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Sweet alert init js-->
    {{-- <script src="{{ asset('backend/assets/js/pages/sweet-alerts.init.js') }}"></script> --}}
    <script>
        // Get the CSRF token from a meta tag in your HTML
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const synth = window.speechSynthesis;
        const print = false;
        $(document).ready(function () {
                // Check if DataTable is already initialized
                if (!$.fn.DataTable.isDataTable('#state-saving-datatable')) {
                    $('#state-saving-datatable').DataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        responsive: true,
                        deferRender: true, // Defer rendering until interaction
                        // Add other DataTables configuration options here
                    });
                    
                }

               
            
            
        $(document).on('click', '#assistant', function() {
            // Make an API request to Laravel with CSRF token in headers
            // Make an API request to Laravel with CSRF token using jQuery's AJAX
            Swal.fire({
                title: "Im your BIS Assistant.",
                html: `<div>
                            <img src="/images/assistant.png" alt="Header Image" style="width: 40%; max-height: 100px;">
                        </div>
                        <div>
                            <label for="custom-input">What can i do for you?</label>
                            <input type="text" id="custom-input" class="swal2-input" placeholder="Enter your queries">
                        </div>
                `,
                showCancelButton: true,
                confirmButtonText: "Submit",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#0f9cf3",
                cancelButtonColor: "#f32f53",
                preConfirm: function() {
                    const ask = document.getElementById("custom-input").value;
                    if (!ask) {
                        Swal.showValidationMessage("Please enter your queries.");
                    }
                    return ask;
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    const question = result.value;
                   // Call the function with the desired text and csrfToken
                    makeNLPRequest(question, csrfToken)
                        .done(function(response) {
                            // Handle the NLP response from Laravel
                            console.log(response); // Modify this to handle the response as needed
                            const initialUtterance = new SpeechSynthesisUtterance(response.init);
                            // const answerUtterance = new SpeechSynthesisUtterance(response.answer);
                            synth.speak(initialUtterance);
                            let itemList = ''
                           
                             // Create a list of items using <li> tags
                            itemList = response.answer.split('\n').map(item => `<li>${item}</li>`).join('');

                            Swal.fire({
                                icon: "success",
                                title: "Assistant Speaking...",
                                html: `${itemList}!`,
                                confirmButtonColor: "#0f9cf3",
                                timer: 3000, // Time in milliseconds (e.g., 3000ms = 3 seconds)
                                timerProgressBar: true, // Show a progress bar indicating the remaining time
                                onClose: function () {
                                    switch (response.action) {
                                        case 'attendance':
                                            // Trigger the click event to open the modal automatically
                                            $("#openModalAttendance").trigger("click");
                                            break;
                                        case 'available':
                                            // Redirect to the desired route
                                            window.location.href = "{{ route('inventory.available.stocks') }}";
                                            break;
                                        case 'employee.present':
                                            // Redirect to the desired route
                                            window.location.href = "{{ route('employee.table') }}";
                                            break;
                                        case 'print.products':
                                            // Redirect to the desired route
                                            $(".buttons-print").trigger("click");
                                            break;
                                    
                                        default:
                                            break;
                                    }
                                    
                                    
                                    
                                }
                                
                            })

                        })
                        .fail(function(error) {
                            // Handle errors
                            console.error(error);
                        });
                    
                }
            });

            function makeNLPRequest(text, csrfToken) {
                return $.ajax({
                    method: 'POST',
                    url: '/nlp',
                    data: JSON.stringify({
                        text: text
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
            }

        })
    });
        
    </script>
</body>

</html>
