<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    @media screen and (min-width: 1000px) {
        .custom-card {
            margin-top: 50px;
        }
    }

    @media screen and (max-width: 1000px) {
        .bebas {
            flex: 0 0 auto !important;
            width: 100% !important;
        }
    }

    /* Additional CSS for the centered card with margins */
    .centered-card {
        margin: 20px auto;
        /* Adjust margin as needed */
        /* max-width: 600px; */
        padding: 0;
        /* Remove default padding */
    }

    .custom-card-body {
        height: 600px;
        /* Set the desired height for card-body */
        overflow: auto;
        /* Add scroll if content overflows */
    }

    /* Style for bottom buttons */
    .bottom-buttons {
        text-align: center;
        margin-top: 20px;
    }

    .hide {
        display: none;
    }

    .centered-card {
        font-size: 12px;
        /* Atur ukuran font yang diinginkan di sini */
    }

    .myDiv {
        display: none;
    }
</style>
