<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interview Confirmation</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #1f2937;">
    <p>Dear Mr./Ms. <strong>{{ $schedule->pic_name }}</strong>,</p>

    <p>Thank you for your submission. We are pleased to confirm your interview session at <strong>Indonesia Miner 2026</strong>.</p>

    <p>Please find the details below:</p>

    <p><strong>Company Name:</strong><br>{{ $schedule->company_name }}</p>
    <p><strong>Preferred Interview Time Slot:</strong><br>{{ $schedule->preferred_time_slot }}</p>
    <p><strong>Number of Interviewees:</strong><br>{{ $schedule->number_of_interviewees }} participants</p>

    <p><strong>Interviewee Details:</strong></p>
    <ol>
        @foreach ((array) $schedule->interviewees as $item)
            <li>{{ $item['name'] ?? '-' }} - {{ $item['job_title'] ?? '-' }}</li>
        @endforeach
    </ol>

    <p><strong>Selected Questions:</strong></p>
    <ol>
        @foreach ($questionDetails as $question)
            <li>{{ $question['text'] }}</li>
        @endforeach
    </ol>

    <p>If there are any updates or changes, please feel free to inform us.</p>

    <p>We look forward to your participation and see you at the event.</p>

    <p>
        Best regards,<br>
        <strong>Djakarta Mining Club Team</strong>
    </p>
</body>

</html>
