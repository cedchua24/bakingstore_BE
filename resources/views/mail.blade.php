<html>

<head>
    <style>
        body {
            background-image: url('img/mapi_dog.png');
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            background-color: #cccccc;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        caption {
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>

<body>
    <center>
        <!-- <img src="{{ URL('img/mapi_dog.png') }}" alt=""> -->
        <table>
            <caption>
                <h1>Reports</h1>
            </caption>
            <thead>
                <tr>
                    <th>Total Transaction</th>
                    <th>Total Cash Payment</th>
                    <th>Total Online Payment:</th>
                    <th>Total Sales</th>
                    <th> Total Profit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p>{{ @$params['total_count'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['total_cash'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['total_online'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['total_paid'] + @$params['total_paid_prev'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['total_profit'] }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <br></br><br></br>
        <table>
            <caption>
                <h1>Payment Type</h1>
            </caption>
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($params['payment'] as $key)
                    <tr>
                        <td>{{ $key['payment_type'] }}</td>
                        <td>{{ $key['total_amount'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </center>
</body>

</html>
