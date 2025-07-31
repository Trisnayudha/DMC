<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Kartu Nama dengan Kamera</title>
    <!-- CSRF token untuk Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        #progress {
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <h1>Scan Kartu Nama dengan Kamera</h1>
    <p>Arahkan kartu nama ke kamera dan ambil foto untuk mengambil data secara otomatis.</p>

    <div id="camera">
        <video id="video" playsinline autoplay></video>
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

        <button id="submit">Input ke Laravel</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.1/dist/tesseract.min.js"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const captureButton = document.getElementById('capture');
        const formDiv = document.getElementById('form');
        const progressValue = document.getElementById('progressValue');
        const resultDiv = document.getElementById('result');

        // Mulai streaming kamera: coba rear camera dulu, jika gagal fallback ke default
        function startStream() {
            const envConstraints = {
                video: {
                    facingMode: {
                        ideal: "environment"
                    }
                }
            };
            navigator.mediaDevices.getUserMedia(envConstraints)
                .catch(err => {
                    console.warn("Tidak dapat akses kamera belakang, fallback ke default:", err);
                    return navigator.mediaDevices.getUserMedia({
                        video: true
                    });
                })
                .then(stream => {
                    video.srcObject = stream;
                    video.onloadedmetadata = () => video.play();
                })
                .catch(err => {
                    console.error("Gagal mengakses kamera:", err);
                    alert("Tidak bisa mengakses kamera: " + err.message);
                });
        }

        startStream();

        // Klik tombol ambil foto → proses OCR
        captureButton.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageDataURL = canvas.toDataURL('image/png');

            Tesseract.recognize(imageDataURL, 'eng', {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const p = Math.round(m.progress * 100);
                            progressValue.textContent = p + '%';
                        }
                    }
                })
                .then(({
                    data: {
                        text
                    }
                }) => {
                    const ocrText = text.trim();
                    if (!ocrText) {
                        resultDiv.textContent = "Tidak ada data yang terdeteksi.";
                        formDiv.style.display = 'none';
                    } else {
                        resultDiv.textContent = ocrText;
                        // parsing sederhana
                        document.getElementById('companyInput').value = extractCompany(ocrText);
                        document.getElementById('nameInput').value = extractName(ocrText);
                        document.getElementById('jobTitleInput').value = extractJobTitle(ocrText);
                        document.getElementById('emailInput').value = extractEmail(ocrText);
                        formDiv.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error("OCR error:", err);
                    resultDiv.textContent = "Terjadi kesalahan OCR.";
                });
        });

        function extractCompany(text) {
            // TODO: ganti dengan regex atau NLP sesuaikan format kartu nama sebenarnya
            return "";
        }

        function extractName(text) {
            return "";
        }

        function extractJobTitle(text) {
            return "";
        }

        function extractEmail(text) {
            const re = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/;
            const m = text.match(re);
            return m ? m[0] : "";
        }

        // Kirim data ke Laravel
        document.getElementById('submit').addEventListener('click', () => {
            const payload = {
                company: document.getElementById('companyInput').value,
                name: document.getElementById('nameInput').value,
                job_title: document.getElementById('jobTitleInput').value,
                email: document.getElementById('emailInput').value,
                mobile: ''
            };
            fetch('/business-card/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(json => {
                    alert('Data berhasil disimpan ke database!');
                    formDiv.style.display = 'none';
                })
                .catch(err => {
                    console.error('Gagal mengirim data:', err);
                    alert('Gagal menyimpan data.');
                });
        });
    </script>

</body>

</html>
