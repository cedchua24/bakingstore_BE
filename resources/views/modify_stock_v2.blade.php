<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock adjustment</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f6f8; color: #222b38; font-family: Arial, Helvetica, sans-serif;">
    @php
        $adjustment = $params['newStocks'] ?? 0;
        $isAddition = $adjustment > 0;
        $formattedQuantity = rtrim(rtrim(number_format($adjustment, 4, '.', ','), '0'), '.');
    @endphp
    <div style="padding: 32px 16px;">
        <div style="max-width: 560px; margin: 0 auto;">
            <p style="margin: 0 0 12px; color: #647084; font-size: 11px; font-weight: bold; letter-spacing: 1.5px;">INVENTORY NOTIFICATION</p>
            <div style="background-color: #ffffff; border: 1px solid #e3e7ed; border-top: 4px solid #3563b5; border-radius: 12px;">
                <div style="padding: 24px;">
                    <p style="margin: 0 0 12px; color: #3563b5; font-size: 12px; font-weight: bold;">MANUAL STOCK ADJUSTMENT</p>
                    <h1 style="margin: 0 0 20px; font-size: 24px; line-height: 32px; overflow-wrap: anywhere; word-wrap: break-word;">{{ $params['product_name'] ?? 'Product' }}</h1>

                    <div style="padding: 18px 20px; border-radius: 8px; background-color: {{ $isAddition ? '#edf7f1' : '#fff1ee' }}; color: {{ $isAddition ? '#216743' : '#a33d2d' }};">
                        <p style="margin: 0 0 6px; font-size: 12px; font-weight: bold;">{{ $isAddition ? 'STOCK ADDED' : 'STOCK REDUCED' }}</p>
                        <p style="margin: 0; font-size: 30px; line-height: 38px; font-weight: bold;">{{ $isAddition ? '+' : '' }}{{ $formattedQuantity }} <span style="font-size: 18px;">{{ $params['pack'] ?? '' }}</span></p>
                    </div>

                    <div style="padding: 22px 0; border-bottom: 1px solid #e9ecf1;">
                        <p style="margin: 0 0 6px; color: #647084; font-size: 12px;">Adjustment type</p>
                        <p style="margin: 0 0 18px; font-size: 16px; line-height: 24px; font-weight: bold;">{{ ($params['type'] ?? null) ?: 'Not provided' }}</p>
                        <p style="margin: 0 0 6px; color: #647084; font-size: 12px;">Modified by</p>
                        <p style="margin: 0; font-size: 16px; line-height: 24px; font-weight: bold;">{{ $modifiedBy ?? 'Not provided' }}</p>
                    </div>

                    <div style="padding: 22px 0; border-bottom: 1px solid #e9ecf1;">
                        <p style="margin: 0 0 8px; color: #647084; font-size: 12px;">Reason for adjustment</p>
                        <p style="margin: 0; font-size: 15px; line-height: 24px; white-space: pre-wrap; overflow-wrap: anywhere; word-wrap: break-word;">{{ ($params['stock_reason'] ?? null) ?: 'Not provided' }}</p>
                    </div>

                    <div style="padding-top: 20px;">
                        <p style="margin: 0 0 8px; color: #647084; font-size: 13px; line-height: 22px;">Unit cost: <strong style="color: #222b38;">{{ number_format($params['email_price'] ?? 0, 2) }}</strong></p>
                        <p style="margin: 0; color: #647084; font-size: 13px; line-height: 22px;">Total cost adjustment: <strong style="color: #222b38;">{{ number_format($params['email_total_cost'] ?? 0, 2) }}</strong></p>
                    </div>
                </div>
                <div style="padding: 16px 24px; border-top: 1px solid #e9ecf1;">
                    <p style="margin: 0 0 4px; color: #647084; font-size: 12px;">Recorded on (GMT+8)</p>
                    <p style="margin: 0; color: #424e60; font-size: 13px; line-height: 20px;">{{ $params['email_date'] ?? 'Not available' }}</p>
                </div>
            </div>
            <p style="margin: 16px 0 0; color: #647084; font-size: 12px; text-align: center;">Automated inventory alert</p>
        </div>
    </div>
</body>
</html>
