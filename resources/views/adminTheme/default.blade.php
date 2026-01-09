<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">



    <title>African Artistic Gymnastics Championships Yaoundé 2026</title>



    <link rel="icon" type="image/type" href="{{asset('adminTheme/img/competition.jpeg')}}">

    <!-- Custom fonts for this teqplate-->
    <link href="{{asset("adminTheme/vendor/fontawesome-free/css/all.min.css")}}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{asset("adminTheme/css/sb-admin-2.min.css")}}" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
    @include('adminTheme.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
    @include("adminTheme.navbar")
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                                @yield("content")


                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; bellofidele@2026</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    
                    <form method="POST" action="{{ route('logout') }}"> 
                        
                    @csrf 
                    
                    <input type="submit" class="btn btn-primary"  value="Logout"></button>
                </form>
                   
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{asset("adminTheme/vendor/jquery/jquery.min.js")}}"></script>
    <script src="{{asset("adminTheme/vendor/bootstrap/js/bootstrap.bundle.min.js")}}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{asset("adminTheme/vendor/jquery-easing/jquery.easing.min.js")}}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{asset("adminTheme/js/sb-admin-2.min.js")}}"></script>

    <!-- Page level plugins -->
    <script src="{{asset("adminTheme/vendor/chart.js/Chart.min.js")}}"></script>

    <!-- Page level custom scripts -->
    <script src="{{asset("adminTheme/s/demo/chart-area-demo.js")}}"></script>
    <script src="{{asset("adminTheme/js/demo/chart-pie-demo.js")}}"></script>


    <script src="{{ asset('https://cdn.jsdelivr.net/npm/chart.js')}}"></script>
@stack('charts')




<script>
$('#previewModal').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let url = button.data('url');

    $('#previewFrame').attr('src', url);
});

$('#previewModal').on('hidden.bs.modal', function () {
    $('#previewFrame').attr('src','');
});
</script>



<script>
$('#function').on('change', function () {
    const role = $(this).val();

    if (['gymnast','coach','judge','doctor'].includes(role)) {
        $('#figIdField').removeClass('d-none');
    } else {
        $('#figIdField').addClass('d-none');
    }

    if (role === 'gymnast') {
        $('#gymnastFields').removeClass('d-none');
    } else {
        $('#gymnastFields').addClass('d-none');
        $('#musicField').addClass('d-none');
    }
});

$('#discipline').on('change', function () {
    if ($(this).val() === 'GAF') {
        $('#musicField').removeClass('d-none');
    } else {
        $('#musicField').addClass('d-none');
    }
});
</script>




@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.preview-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            const url  = this.dataset.url;
            const type = this.dataset.type;
            const container = document.getElementById('previewContainer');

            container.innerHTML = '';

            if (type === 'pdf') {
                container.innerHTML = `
                    <iframe src="${url}"
                            style="width:100%;height:100%;border:none;"
                            allowfullscreen>
                    </iframe>`;
            }

            else if (type === 'image') {
                container.innerHTML = `
                    <img src="${url}"
                         class="img-fluid"
                         style="max-height:100%;max-width:100%;">
                `;
            }

            else if (type === 'audio') {
                container.innerHTML = `
                    <audio controls style="width:80%;">
                        <source src="${url}">
                        Votre navigateur ne supporte pas l’audio.
                    </audio>
                `;
            }

            $('#previewModal').modal({
                backdrop: 'static',
                keyboard: false
            });

        });
    });

    // Nettoyage à la fermeture
    $('#previewModal').on('hidden.bs.modal', function () {
        document.getElementById('previewContainer').innerHTML = '';
    });
});
</script>
@endpush


</body>

</html>