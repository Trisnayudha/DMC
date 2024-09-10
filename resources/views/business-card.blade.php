<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Kartu Nama dengan Kamera</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        #camera,
        #result {
            margin-top: 20px;
        }

        video,
        canvas {
            width: 100%;
            max-width: 400px;
        }

        #form {
            display: none;
            margin-top: 20px;
        }

        #result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            white-space: pre-wrap;
        }

        label {
            display: block;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <h1>Scan Kartu Nama dengan Kamera</h1>
    <p>Arahkan kartu nama ke kamera dan ambil foto untuk mengambil data secara otomatis.</p>

    <div>
        <video id="video" autoplay></video>
        <button id="capture">Ambil Foto</button>
        <canvas id="canvas" style="display: none;"></canvas>
    </div>

    <div id="progress">Progress: <span id="progressValue">0%</span></div>
    <div id="result">Hasil OCR akan muncul di sini...</div>

    <!-- Form untuk preview dan edit data -->
    <div id="form">
        <label>Company:</label>
        <input type="text" id="companyInput" placeholder="Company Name">

        <label>Name:</label>
        <input type="text" id="nameInput" placeholder="Name">

        <label>Job Title:</label>
        <input type="text" id="jobTitleInput" placeholder="Job Title">

        <label>Email:</label>
        <input type="text" id="emailInput" placeholder="Email">

        <button id="submit">Input ke Spreadsheet</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.1/dist/tesseract.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const captureButton = document.getElementById('capture');
        const formDiv = document.getElementById('form');

        // Akses kamera perangkat dengan kamera belakang (facingMode: environment)
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: {
                        exact: "environment" // Menentukan kamera belakang
                    }
                }
            })
            .then(function(stream) {
                video.srcObject = stream;
            })
            .catch(function(error) {
                console.error("Gagal mengakses kamera belakang:", error);
            });

        captureButton.addEventListener('click', function() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageDataURL = canvas.toDataURL('image/png');

            // Proses OCR dengan Tesseract.js
            Tesseract.recognize(
                imageDataURL,
                'eng', {
                    logger: function(m) {
                        if (m.status === 'recognizing text') {
                            const progress = Math.round(m.progress * 100);
                            document.getElementById('progressValue').textContent = progress + '%';
                        }
                    }
                }
            ).then(function(result) {
                const ocrText = result.data.text;
                document.getElementById('result').textContent = ocrText;

                // Ekstrak informasi dari hasil OCR (ini bisa disesuaikan tergantung format kartu nama)
                const company = extractCompany(ocrText);
                const name = extractName(ocrText);
                const jobTitle = extractJobTitle(ocrText);
                const email = extractEmail(ocrText);

                // Masukkan hasil ke input form
                document.getElementById('companyInput').value = company;
                document.getElementById('nameInput').value = name;
                document.getElementById('jobTitleInput').value = jobTitle;
                document.getElementById('emailInput').value = email;

                // Tampilkan form untuk mengedit jika diperlukan
                formDiv.style.display = 'block';
            });
        });

        // Fungsi untuk parsing data hasil OCR (sederhana)
        function extractCompany(text) {
            return "Contoh Company"; // Lakukan parsing lebih baik di sini
        }

        function extractName(text) {
            return "Nama Contoh"; // Lakukan parsing lebih baik di sini
        }

        function extractJobTitle(text) {
            return "Job Title Contoh"; // Lakukan parsing lebih baik di sini
        }

        function extractEmail(text) {
            const emailRegex = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/;
            const match = text.match(emailRegex);
            return match ? match[0] : 'Tidak ditemukan';
        }

        // Kirim data ke Google Sheets via Apps Script ketika user klik submit
        document.getElementById('submit').addEventListener('click', function() {
            const company = document.getElementById('companyInput').value;
            const name = document.getElementById('nameInput').value;
            const jobTitle = document.getElementById('jobTitleInput').value;
            const email = document.getElementById('emailInput').value;

            sendToGoogleSheets(company, name, jobTitle, email);
        });

        function sendToGoogleSheets(company, name, jobTitle, email) {
            const scriptURL = 'URL_APPS_SCRIPT_ANDA'; // Masukkan URL Apps Script Anda di sini
            fetch(scriptURL, {
                    method: 'POST',
                    body: JSON.stringify({
                        company,
                        name,
                        jobTitle,
                        email
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.text())
                .then(data => {
                    alert('Data berhasil dikirim ke Google Sheets!');
                    formDiv.style.display = 'none'; // Sembunyikan form setelah berhasil dikirim
                })
                .catch(error => {
                    console.error('Gagal mengirim data:', error);
                });
        }
    </script>

</body>

</html>
