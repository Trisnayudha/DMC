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
            group.addEventListener('change', function(event) {
                if (group.dataset.choiceGroup === 'platforms' && event.target.checked) {
                    var none = "I don't follow any";
                    group.querySelectorAll('input').forEach(function(input) {
                        if ((event.target.value === none && input !== event.target) ||
                            (event.target.value !== none && input.value === none)) input.checked = false;
                    });
                }
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
            ['event_types', 'event_improvement_aspects'].forEach(function(name) {
                var input = document.getElementById(name === 'event_types' ? 'event_types_other' : 'event_improvement_other');
                var selected = form.querySelector('input[name="' + name + '[]"][value="Other"]:checked');
                input.style.display = selected ? '' : 'none';
                input.required = !!selected;
                if (!selected) { input.value = ''; input.classList.remove('is-invalid'); }
            });

            var otherInput = document.getElementById('email_primary_goal_other');
            var isOther = checkedValue('email_primary_goal') === 'Other';
            otherInput.style.display = isOther ? '' : 'none';
            otherInput.required = isOther;
            if (!isOther) { otherInput.value = ''; otherInput.classList.remove('is-invalid'); }
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
            furthestStep = Math.max(furthestStep, step);
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
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
                saveToServer(false);
            }
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

        /* ---------- Autosave ----------
           Every change is kept in localStorage right away and backed up to the
           database (status in_progress) shortly after, so an unfinished survey
           can be resumed later and the team still has the partial answers. */
        var DRAFT_URL = @json(route('survey.member.draft'));
        var LOCAL_KEY = 'dmc_member_survey_draft';
        var serverDraft = @json($draftState ?? null);
        var isSubmitting = false;
        var isDirty = false;
        var saveTimer = null;
        var furthestStep = 1;

        function collectState() {
            var fields = {};
            Array.prototype.forEach.call(form.elements, function(el) {
                if (!el.name || el.name === '_token' || el.type === 'submit' || el.type === 'button') return;
                if (el.name.slice(-2) === '[]') {
                    var key = el.name.slice(0, -2);
                    fields[key] = fields[key] || [];
                    if (el.checked) fields[key].push(el.value);
                } else {
                    fields[el.name] = el === phoneInput ? phoneValue() : el.value;
                }
            });
            return { fields: fields, last_step: furthestStep };
        }

        function phoneValue() {
            return (iti && iti.getNumber()) || phoneInput.value;
        }

        function readLocal() {
            try { return JSON.parse(localStorage.getItem(LOCAL_KEY)); } catch (e) { return null; }
        }

        function writeLocal() {
            try { localStorage.setItem(LOCAL_KEY, JSON.stringify(collectState())); } catch (e) {}
        }

        function saveToServer(useBeacon) {
            clearTimeout(saveTimer);
            if (isSubmitting) return;

            var payload = new FormData(form);
            payload.set('phone', phoneValue());
            payload.set('last_step', furthestStep);
            isDirty = false;

            if (useBeacon && navigator.sendBeacon) {
                navigator.sendBeacon(DRAFT_URL, payload);
                return;
            }
            fetch(DRAFT_URL, {
                method: 'POST',
                body: payload,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function() { isDirty = true; });
        }

        function queueSave() {
            if (isSubmitting) return;
            isDirty = true;
            writeLocal();
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function() { saveToServer(false); }, 1500);
        }

        form.addEventListener('input', queueSave);
        form.addEventListener('change', queueSave);

        // Leaving / switching tab: flush whatever has not reached the server yet
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && isDirty) saveToServer(true);
        });
        window.addEventListener('pagehide', function() {
            if (isDirty) saveToServer(true);
        });

        // Put saved answers back into the form; returns the step to reopen
        function applyState(state) {
            if (!state || !state.fields) return 1;
            Object.keys(state.fields).forEach(function(name) {
                var value = state.fields[name];
                if (Array.isArray(value)) {
                    form.querySelectorAll('input[name="' + name + '[]"]').forEach(function(input) {
                        input.checked = value.indexOf(input.value) !== -1;
                    });
                    return;
                }
                var input = form.querySelector('[name="' + name + '"]');
                if (!input || input.type === 'hidden' || value === null) return;
                if (input === phoneInput && iti && String(value).charAt(0) === '+') {
                    iti.setNumber(value);
                } else {
                    input.value = value;
                }
            });
            form.querySelectorAll('[data-choice-group]').forEach(syncChoiceGroup);
            return Math.min(Math.max(parseInt(state.last_step, 10) || 1, 1), totalSteps);
        }

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

        // No validation errors: resume an unfinished survey (this browser first, then the database backup)
        if (!errorFields.length) {
            startStep = applyState(readLocal() || serverDraft);
        }

        toggleConditionalFields();
        showStep(startStep);
        window.scrollTo(0, 0);
    })();
</script>
