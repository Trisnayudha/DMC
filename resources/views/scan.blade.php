<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- include the library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

</head>

<body>
    <div id="qr-reader" style="width: 1000px; display: ; text-align: center;"></div>

    <script>
        var scanComplete = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (!scanComplete) {
                console.log(`Code scanned = ${decodedText}`, decodedResult);
                scanComplete = true;
                console.log(scanComplete);

                // Tambahkan kode ini untuk menampilkan sweet alert success scan
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'center',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: 'Scan berhasil'
                })

                setTimeout(() => {
                    scanComplete = false;
                }, 5000); // delay for 5 seconds
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
            fps: 10,
            qrbox: 500
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>

</body>

</html>
