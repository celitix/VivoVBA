<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Query Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            background: #ffffff;
            max-width: 650px;
            margin: auto;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #e5e5e5;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .header h2 {
            margin: 0;
            color: #1a1a1a;
        }

        .content {
            padding: 20px 0;
        }

        .content p {
            font-size: 15px;
            color: #333;
            margin: 8px 0;
        }

        .label {
            font-weight: bold;
            color: #000;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="email-container">

        <div class="header">
            <h2>New Query Submitted</h2>
        </div>

        <div class="content">
            <p><span class="label">VBA:</span> {{ $name }}</p>
            <p><span class="label">Consumer Name:</span> {{ $data["consumer_name"] }}</p>
            <p><span class="label">Contact Number:</span> {{ $data["contact_number"] }}</p>
            <p><span class="label">Email:</span> {{ $data["email"] }}</p>
            <!-- <p><span class="label">Model:</span> {{ $model }}</p> -->
            <p><span class="label">Source:</span> {{ $data["query"] }}</p>
            <p><span class="label">Type:</span> {{ $type }}</p>
            @if ($data["message"])
                <p><span class="label">Query:</span> {{ $data["message"] }}</p>
            @endif
            @if ($data["model"])
                <p><span class="label">Model:</span> {{ $model }}</p>
            @endif
        </div>

        <div class="footer">
            <p>This message was generated automatically by your website.</p>
        </div>

    </div>

</body>

</html>