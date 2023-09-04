<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Register</title>
    @include('register_event.style')
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('image/dmc.png') }}" alt="Bootstrap" width="100" height="40">
            </a>
            <ul class="nav nav-pills justify-content-end">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">REGISTER EVENT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">CONTACT US</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="container centered-card mt-4">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <!-- Your card content goes here -->
                        <form action="#" method="post">
                            <fieldset id="step1">
                                @include('register_event.step1')
                                <div class="bottom-buttons">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="prevStep(currentStep)">Previous</button>
                                    <button type="button" class="btn btn-primary nextButton"
                                        onclick="nextStep(currentStep)" disabled>Next</button>
                                </div>
                            </fieldset>

                            <fieldset style="display: none;" id="step2">
                                @include('register_event.step2')
                                <div class="bottom-buttons">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="prevStep(currentStep)">Previous</button>
                                    <button type="button" class="btn btn-primary nextButton"
                                        onclick="nextStep(currentStep)" id="nextButton" disabled>Next</button>
                                </div>
                            </fieldset>

                            <fieldset style="display: none;" id="step3">
                                @include('register_event.step3')
                                <div class="bottom-buttons">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="prevStep(currentStep)">Previous</button>
                                    <button type="button" class="btn btn-primary nextButton"
                                        onclick="nextStep(currentStep)" disabled>Next</button>
                                </div>
                            </fieldset>

                            <fieldset style="display: none;" id="step4">
                                @include('register_event.step4')
                                <div class="bottom-buttons">
                                    <button type="button" class="btn btn-secondary"
                                        onclick="prevStep(currentStep)">Previous</button>
                                    <button type="button" class="btn btn-primary nextButton"
                                        onclick="nextStep(currentStep)" disabled>Next</button>
                                </div>

                            </fieldset>

                            <fieldset style="display: none;" id="step5">
                                <legend>Step 5: Web View from Xendit Link</legend>
                                <div style="display: flex; justify-content: center;">
                                    <iframe src="https://checkout-staging.xendit.co/web/64f143a7ec885983f72fd26e"
                                        width="600" height="1000" frameborder="0"></iframe>
                                </div>
                            </fieldset>

                            <fieldset style="display: none;" id="step6">
                                <legend>Step 6: Success Payment</legend>
                                <!-- Display success message -->
                            </fieldset>
                        </form>
                        <!-- Add more content as needed -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('register_event.hide_form')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @include('register_event.script_step3')
    @include('register_event.script_multiple_payment')

</body>

</html>
