<?php
function send_push_notification($device_token, $message, $conn, $payment_id = null, $customer_id = null) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    $serverKey = 'YOUR_FIREBASE_SERVER_KEY'; // Replace with your Firebase server key

    $notification = [
        'title' => 'DOHIVES Alert',
        'body'  => $message,
        'sound' => 'default'
    ];

    $data = [
        'to' => $device_token,
        'notification' => $notification,
        'priority' => 'high'
    ];

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    curl_close($ch);

    $status = (strpos($result, 'message_id') !== false) ? 'sent' : 'failed';

    // Log into push_notif_logs
    if ($device_token) {
        $stmt = $conn->prepare("INSERT INTO push_notif_logs (payment_id, customer_id, message, sent_at, status, device_token) VALUES (?,?,?,?,?,?)");
        $now = date("Y-m-d H:i:s");
        $stmt->bind_param("iissss", $payment_id, $customer_id, $message, $now, $status, $device_token);
        $stmt->execute();
        $stmt->close();
    }
}
?>