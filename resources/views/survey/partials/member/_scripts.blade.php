<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script>
    (function() {
        var form = document.getElementById('member-survey-form');
        if (!form) return;

        var panes = form.querySelectorAll('.step-pane');
        var stepperItems = document.querySelectorAll('.stepper-item');
        var btnBack = document.getElementById('btn-step-back');
        var btnNext = document.getElementById('btn-step-next');
        var btnSubmit = document.getElementById('btn-step-submit');
        var totalSteps = panes.length;
        var currentStep = 1;

        /* ---------- Phone ---------- */
        var phoneInput = document.getElementById('phone');
        // Phone widget is optional: if the CDN fails, the form still works as a plain input
        var iti = window.intlTelInput ? window.intlTelInput(phoneInput, {
            initialCountry: 'id',
            separateDialCode: true,
            autoPlaceholder: 'polite',
            placeholderNumberType: 'MOBILE',
            preferredCountries: ['id', 'au', 'sg', 'my'],
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.min.js'
        }) : null;

        /* ---------- Choice cards ---------- */
        function syncChoiceGroup(group) {
            group.querySelectorAll('.choice-card').forEach(function(card) {
                card.classList.toggle('active', card.querySelector('input').checked);
            });
        }

        form.querySelectorAll('[data-choice-group]').forEach(function(group) {
            group.addEventListener('change', function() {
                syncChoiceGroup(group);
                group.classList.remove('is-invalid');
                toggleConditionalFields();
            });
        });

        function checkedValue(name) {
            var checked = form.querySelector('input[name="' + name + '[]"]:checked');
            return checked ? checked.value : null;
        }

        function toggleConditionalFields() {
            var usageGroup = document.getElementById('usage-frequency-group');
            var notAware = checkedValue('app_awareness') === 'No';
            usageGroup.style.display = notAware ? 'none' : '';
            if (notAware) {
                usageGroup.querySelectorAll('input').forEach(function(input) {
                    input.checked = false;
                });
                syncChoiceGroup(usageGroup.querySelector('[data-choice-group]'));
            }

            ['event_types', 'event_improvement_aspects'].forEach(function(name) {
                var input = document.getElementById(name === 'event_types' ? 'event_types_other' : 'event_improvement_other');
                var selected = form.querySelector('input[name="' + name + '[]"][value="Other"]:checked');
                input.style.display = selected ? '' : 'none';
                if (!selected) input.value = '';
            });

            var otherInput = document.getElementById('email_primary_goal_other');
            var isOther = checkedValue('email_primary_goal') === 'Other';
            otherInput.style.display = isOther ? '' : 'none';
            if (!isOther) otherInput.value = '';
        }

        /* ---------- Validation ---------- */
        function markInvalid(el) {
            el.classList.add('is-invalid', 'shake-element');
            setTimeout(function() {
                el.classList.remove('shake-element');
            }, 400);
        }

        function validateStep(step) {
            var pane = panes[step - 1];
            var firstInvalid = null;

            pane.querySelectorAll('.form-control-dark[required]').forEach(function(input) {
                var value = input.value.trim();
                var valid = value !== '' && (input.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value));
                var target = input === phoneInput ? (input.closest('.iti') || input) : input;

                target.classList.remove('is-invalid');
                if (!valid) {
                    markInvalid(target);
                    firstInvalid = firstInvalid || input;
                }
            });

            pane.querySelectorAll('[data-choice-group][data-required]').forEach(function(group) {
                group.classList.remove('is-invalid');
                if (!group.querySelector('input:checked')) {
                    markInvalid(group);
                    firstInvalid = firstInvalid || group;
                }
            });

            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (firstInvalid.focus) firstInvalid.focus({ preventScroll: true });
            }
            return !firstInvalid;
        }

        form.querySelectorAll('.form-control-dark').forEach(function(input) {
            input.addEventListener('input', function() {
                input.classList.remove('is-invalid');
                var phoneWrapper = input.closest('.iti');
                if (phoneWrapper) phoneWrapper.classList.remove('is-invalid');
            });
        });

        /* ---------- Step navigation ---------- */
        function showStep(step) {
            currentStep = step;
            panes.forEach(function(pane, i) {
                pane.classList.toggle('active', i === step - 1);
            });
            stepperItems.forEach(function(item, i) {
                item.classList.toggle('active', i === step - 1);
                item.classList.toggle('done', i < step - 1);
            });
            btnBack.style.display = step > 1 ? '' : 'none';
            btnNext.style.display = step < totalSteps ? '' : 'none';
            btnSubmit.style.display = step === totalSteps ? '' : 'none';
            document.getElementById('step-counter').textContent = step;
            document.getElementById('survey-steps').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        btnNext.addEventListener('click', function() {
            if (validateStep(currentStep)) showStep(currentStep + 1);
        });

        btnBack.addEventListener('click', function() {
            showStep(currentStep - 1);
        });

        // Enter on a text input moves to the next step instead of submitting early
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && currentStep < totalSteps) {
                e.preventDefault();
                btnNext.click();
            }
        });

        // Warn before refresh / close once the user is past step 1, since answers are not saved until submit
        var isSubmitting = false;
        window.addEventListener('beforeunload', function(e) {
            if (isSubmitting || currentStep < 2) return;
            e.preventDefault();
            e.returnValue = '';
        });

        form.addEventListener('submit', function(e) {
            for (var step = 1; step <= totalSteps; step++) {
                if (!validateStep(step)) {
                    e.preventDefault();
                    showStep(step);
                    validateStep(step);
                    return;
                }
            }

            if (iti && iti.getNumber()) phoneInput.value = iti.getNumber();
            isSubmitting = true;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        });

        /* ---------- Init ---------- */
        // After a server-side validation error, open the step that holds the first failing field
        @php($errorFields = collect($errors->keys())->map(function ($key) { return explode('.', $key)[0]; })->unique()->values())
        var errorFields = @json($errorFields);

        var startStep = 1;
        for (var i = 0; i < panes.length && errorFields.length; i++) {
            var hasError = errorFields.some(function(field) {
                return panes[i].querySelector('[name="' + field + '"], [name="' + field + '[]"]');
            });
            if (hasError) {
                startStep = i + 1;
                break;
            }
        }

        toggleConditionalFields();
        showStep(startStep);
        window.scrollTo(0, 0);
    })();
</script>
