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
                <h1>Stock Warning</h1>
            </caption>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity / Weight</th>
                    <th>Stock Warning</th>
                    <th>Stock Warning Type</th>
                    <th>Stock</th>
                    <th>Stock/PC</th>
                    <th>Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p>{{ @$params['product_name'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['quantity'] }} x
                            {{ is_int(@$params['weight'] / @$params['quantity'])
                                ? @$params['weight'] / @$params['quantity']
                                : number_format(@$params['weight'] / @$params['quantity'], 2) }}
                            {{ @$params['variation'] }}
                        </p>
                    </td>
                    <td>
                        <p>{{ @$params['stock_warning'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['stock_warning_type'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['stock_v2'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['stock_pc_v2'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['price'] }}</p>
                    </td>
                    <td>
                        <p>{{ @$params['email_date'] }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <br></br><br></br>
    </center>
</body>

</html>
