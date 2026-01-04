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
                <h1>Modified Stock Alert</h1>
            </caption>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Total Cost</th>
                    <th>Reason</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p>{{ @$params['product_name'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['email_price'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['newStocks'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['pack'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['email_total_cost'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['stock_reason'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['created_at'] }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <br></br><br></br>
    </center>
</body>

</html>
