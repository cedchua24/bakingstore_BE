<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Out of Stock Alert</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f6f8; color: #222b38; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%;">
    @php
        $quantity = $params['quantity'] ?? null;
        $weight = $params['weight'] ?? null;
        $unitWeight = is_numeric($quantity) && $quantity > 0 && is_numeric($weight)
            ? $weight / $quantity
            : null;
        $price = $params['price'] ?? null;
        $pendingOrders = collect($pendingOrders ?? []);
        $hasSentOrder = $pendingOrders->contains('status', 'SEND_TO_SUPPLIER');
    @endphp

    <div style="padding: 32px 16px;">
        <div style="max-width: 560px; margin: 0 auto;">
            <p style="margin: 0 0 12px; color: #647084; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase;">Inventory notification</p>

            <div style="overflow: hidden; background-color: #ffffff; border: 1px solid #e3e7ed; border-top: 4px solid #c54638; border-radius: 12px;">
                <div style="padding: 28px 24px 24px;">
                    <span style="display: inline-block; padding: 6px 10px; border-radius: 6px; background-color: #fcecea; color: #a52e24; font-size: 11px; line-height: 16px; font-weight: bold; letter-spacing: 0.6px; text-transform: uppercase;">Out of stock</span>
                    <h1 style="margin: 18px 0 8px; font-size: 24px; line-height: 32px; font-weight: bold; overflow-wrap: anywhere; word-wrap: break-word;">{{ $params['product_name'] ?? 'Product unavailable' }}</h1>
                    <p style="margin: 0; color: #647084; font-size: 14px; line-height: 22px;">
                        @if ($hasSentOrder)
                            This product is out of stock, but replenishment has already been ordered. Please follow up on delivery.
                        @elseif ($pendingOrders->isNotEmpty())
                            This product is out of stock. A purchase order exists but has not yet been sent to the supplier.
                        @else
                            This product has run out of stock and has no open purchase order. Please arrange replenishment.
                        @endif
                    </p>

                    @if ($pendingOrders->isNotEmpty())
                        <div style="margin-top: 24px; padding: 18px 20px; border: 1px solid #dbe5fa; border-radius: 8px; background-color: #f5f8ff;">
                            <h2 style="margin: 0 0 14px; color: #2455a4; font-size: 16px; line-height: 24px;">{{ $hasSentOrder ? 'Replenishment ordered' : 'Purchase order pending' }}</h2>
                            @foreach ($pendingOrders as $order)
                                <div style="{{ !$loop->first ? 'margin-top: 16px; padding-top: 16px; border-top: 1px solid #dbe5fa;' : '' }}">
                                    <p style="margin: 0 0 6px; font-size: 14px; line-height: 22px; font-weight: bold;">{{ $order['supplier'] }}</p>
                                    <p style="margin: 0 0 8px; color: #647084; font-size: 12px; line-height: 20px;">PO #{{ $order['order_supplier_transaction_id'] }} &middot; {{ $order['date'] }}</p>
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; background-color: #e7eefc; color: #2455a4; font-size: 11px; line-height: 16px; font-weight: bold;">{{ $order['status'] === 'SEND_TO_SUPPLIER' ? 'SENT TO SUPPLIER' : 'PENDING — NOT YET SENT' }}</span>
                                    <p style="margin: 10px 0 0; font-size: 14px; line-height: 22px;">{{ $order['status'] === 'SEND_TO_SUPPLIER' ? 'Incoming' : 'Ordered quantity' }}: <strong>{{ $order['quantity'] }}</strong></p>
                                    @if ($order['status'] === 'SEND_TO_SUPPLIER')
                                        <p style="margin: 4px 0 0; color: #647084; font-size: 12px; line-height: 20px;">
                                            @if ($order['send_date'])
                                                Sent {{ \Carbon\Carbon::parse($order['send_date'])->format('M j, Y') }} &middot; {{ \Carbon\Carbon::parse($order['send_date'])->diffForHumans() }}
                                            @else
                                                Sent date not recorded
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="margin-top: 24px; padding: 18px 20px; border: 1px solid #f0d5ac; border-radius: 8px; background-color: #fff8ed;">
                            <h2 style="margin: 0 0 6px; color: #8a541b; font-size: 16px; line-height: 24px;">No purchase order yet</h2>
                            <p style="margin: 0; color: #805d36; font-size: 13px; line-height: 21px;">No pending or sent-to-supplier PO was found for this product. Please create a purchase order.</p>
                        </div>
                    @endif

                    <div style="margin-top: 24px; padding: 18px 20px; border: 1px solid #e9ecf1; border-radius: 8px; background-color: #f8f9fb;">
                        <p style="margin: 0 0 6px; color: #647084; font-size: 12px; line-height: 18px;">Quantity / Weight</p>
                        <p style="margin: 0; font-size: 16px; line-height: 24px; font-weight: bold; overflow-wrap: anywhere;">
                            @if ($unitWeight !== null)
                                {{ $quantity }} &times; {{ number_format($unitWeight, 2) }} {{ $params['variation'] ?? '' }}
                            @else
                                Not available
                            @endif
                        </p>
                        <div style="margin: 16px 0; border-top: 1px solid #e3e7ed;"></div>
                        <p style="margin: 0 0 6px; color: #647084; font-size: 12px; line-height: 18px;">Price</p>
                        <p style="margin: 0; font-size: 20px; line-height: 28px; font-weight: bold;">{{ is_numeric($price) ? number_format($price, 2) : ($price ?? 'Not available') }}</p>
                    </div>
                </div>

                <div style="padding: 16px 24px; border-top: 1px solid #e9ecf1;">
                    <p style="margin: 0 0 4px; color: #647084; font-size: 12px; line-height: 18px;">Reported on</p>
                    <p style="margin: 0; color: #424e60; font-size: 13px; line-height: 20px;">{{ $params['email_date'] ?? 'Not available' }}</p>
                </div>
            </div>

            <p style="margin: 16px 0 0; color: #647084; font-size: 12px; line-height: 18px; text-align: center;">Automated inventory alert</p>
        </div>
    </div>
</body>
</html>
