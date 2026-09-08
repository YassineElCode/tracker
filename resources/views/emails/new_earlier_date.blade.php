<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Earlier Date Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        .date-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }

        strong {
            display: inline-block;
            width: 70px;
        }

        .times {
            margin-top: 5px;
            font-size: 14px;
            color: #444;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>New Earlier Dates Found in {{ $city }}!</h1>

        <p>Hello,</p>
        <p>The system has found the following new earlier appointment dates that may suit your schedule:</p>

        @foreach ($earlierDatums as $datum)
        <div class="date-info">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($datum['date'])->format('d/m/Y') }}</p>
            @if(!empty($datum['city']))
            <p><strong>City:</strong> {{ $datum['city'] }}</p>
            @endif
            <p><strong>Info:</strong> {{ $datum['text'] }}</p>
            @if (!empty($datum['times']))
            <p class="times"><strong>Times:</strong> {{ implode(', ', $datum['times']) }}</p>
            @else
            <p class="times"><em>No times available for your specified range.</em></p>
            @endif
        </div>
        @endforeach

        <p>Please check the booking site to secure one of these slots if they're still available.</p>

        <div class="footer">
            <p>Thank you,</p>
            <p>Your Friendly Bot</p>
        </div>
    </div>
</body>

</html>