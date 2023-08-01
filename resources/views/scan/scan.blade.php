<html>

<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        body {
            background: linear-gradient(to right, #f7797d, #FBD786, #c2e59c);
            background-size: 200% auto;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        input[type="text"] {
            padding: 1em;
            font-size: 1.5em;
            border-radius: 5px;
            border: none;
            text-align: center;
        }

        img {
            height: 200px;
        }

        .bebas {
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="bebas">
        <img src="https://api.djakarta-miningclub.com/image/dmc.png" alt="Image" class="image">
    </div>
    <div class="container">
        <input type="text" id="text-input" placeholder="Enter your text here">
    </div>
</body>
<script>
    $('#text-input').focus();
    $(document).on('click', function(event) {
        var input = $('#text-input');
        var targetElement = event.target;

        // Cek apakah targetElemen yang diklik bukanlah input
        if (!$(targetElement).is(input)) {
            input.focus();
        }
    });
    $('#text-input').focus();
    $("#text-input").on("input", function() {
        if ($(this).val().length >= 7) {
            Swal.fire({
                title: "Loading",
                text: "Please wait...",
                allowOutsideClick: false,
                showConfirmButton: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                }
            });
            setTimeout(function() {
                $.ajax({
                    url: "{{ url('scan/request') }}",
                    type: "POST",
                    data: {
                        input_text: $("#text-input").val()
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log(response)
                        Swal.close();
                        if (response.status == 1) {
                            Swal.fire({
                                title: response.data.package,
                                text: response.data.name + " " + response.data
                                    .company_name,
                                icon: 'success',
                                showCancelButton: true,
                                cancelButtonText: 'Print',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Checkin'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'success',
                                        title: 'Success Checkin',
                                        showConfirmButton: false,
                                        timer: 1000
                                    })
                                } else {
                                    window.open("{{ url('scan/print?name=') }}" +
                                        response
                                        .data.name + "&company=" + response.data
                                        .company_name +
                                        "&package=" + response.data.package,
                                        "_blank");
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "Error Scan",
                                text: response.message,
                                type: "error",
                                confirmButtonText: "OK"
                            });
                        }
                        $("#text-input").val("");
                        // window.location.href = "http://127.0.0.1:8000/scan/success";
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            title: "Error Scan",
                            text: "Error scanning input text!",
                            type: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });

            }, 1000);
        }
    });
</script>

</html>
